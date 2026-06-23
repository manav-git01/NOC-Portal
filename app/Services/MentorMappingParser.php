<?php

namespace App\Services;

use App\Models\User;
use App\Models\Batch;
use Illuminate\Support\Facades\Log;

/**
 * Intelligent Excel parser for Mentor Mapping imports.
 *
 * Handles:
 * - Multi-sheet workbooks (processes ALL sheets)
 * - Fuzzy header detection (scans first 10 rows for the real header)
 * - Flexible column name matching (Student ID, Roll No, Counsellor Name, etc.)
 * - Batch detection from sheet names
 * - Mentor name extraction from combined columns (e.g. "Faculty name & Batch")
 */
class MentorMappingParser
{
    /**
     * Column aliases for enrollment/student ID.
     * Order matters — first match wins.
     */
    private const ENROLLMENT_ALIASES = [
        'enrollment number',
        'enrollment_number',
        'enrollment',
        'student id',
        'student_id',
        'studentid',
        'roll no',
        'roll no.',
        'rollno',
        'roll number',
        'roll_number',
        'id',
        'reg no',
        'reg. no',
        'reg. no.',
        'registration number',
        'registration no',
        'admission no',
        'admission number',
        'enroll no',
        'enroll no.',
        'student enrollment',
    ];

    /**
     * Column aliases for student name.
     */
    private const STUDENT_NAME_ALIASES = [
        'student name',
        'student_name',
        'name',
        'student',
        'full name',
        'full_name',
        'learner name',
        'name of student',
    ];

    /**
     * Column aliases for mentor/guide/counsellor name.
     */
    private const MENTOR_ALIASES = [
        'mentor faculty name',
        'mentor_faculty_name',
        'mentor name',
        'mentor_name',
        'mentor',
        'counsellor name',
        'counselor name',
        'counsellor',
        'counselor',
        'guide',
        'guide name',
        'guide_name',
        'faculty name',
        'faculty_name',
        'faculty',
        'faculty name & batch',
        'teacher name',
        'assigned faculty',
        'assigned guide',
        'advisor',
        'advisor name',
    ];

    /**
     * Column aliases for batch.
     */
    private const BATCH_ALIASES = [
        'batch',
        'batch name',
        'batch_name',
        'class',
        'section',
        'division',
        'group',
    ];

    /**
     * Parse ALL sheets from a spreadsheet and return structured preview data.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @return array{preview_rows: array, warnings: array, stats: array, sheet_mappings: array}
     */
    public function parseWorkbook(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): array
    {
        $allPreviewRows = [];
        $allWarnings = [];
        $sheetMappings = [];

        $statsStudentsFound = 0;
        $statsNewStudents = 0;
        $statsNewFaculty = 0;
        $statsExistingFaculty = 0;
        $statsNewBatches = 0;
        $statsValidationErrors = 0;
        $statsTotalSheets = $spreadsheet->getSheetCount();
        $statsProcessedSheets = 0;
        $statsSkippedSheets = 0;

        $seenMentorEmails = [];
        $seenBatchNames = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = $sheet->getTitle();
            $rows = $sheet->toArray();

            // Skip empty sheets
            if (empty($rows) || count($rows) < 2) {
                $allWarnings[] = "Sheet \"{$sheetName}\": Skipped (empty or only 1 row).";
                $statsSkippedSheets++;
                continue;
            }

            // Build merged cells map for this sheet
            $mergedCellsMap = [];
            try {
                foreach ($sheet->getMergeCells() as $mergeRange) {
                    $boundaries = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::rangeBoundaries($mergeRange);
                    $startCol = $boundaries[0][0];
                    $startRow = $boundaries[0][1];
                    $endCol = $boundaries[1][0];
                    $endRow = $boundaries[1][1];
                    
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol);
                    $topLeftValue = $sheet->getCell($colLetter . $startRow)->getValue();
                    
                    // Propagate to all coordinates inside boundaries
                    for ($col = $startCol; $col <= $endCol; $col++) {
                        for ($r = $startRow; $r <= $endRow; $r++) {
                            // Map 0-indexed coords
                            $mergedCellsMap[($r - 1) . '-' . ($col - 1)] = $topLeftValue;
                        }
                    }
                }
            } catch (\Exception $e) {
                $allWarnings[] = "Warning: Merged range could not be fully resolved in Sheet '{$sheetName}'. Continuing with remaining cells.";
            }

            // Detect header row (scan first 10 rows)
            $headerResult = $this->detectHeaderRow($rows);

            if ($headerResult === null) {
                $allWarnings[] = "Sheet \"{$sheetName}\": Skipped — could not detect valid headers (need at least an ID column and a mentor column).";
                $statsSkippedSheets++;
                continue;
            }

            $headerRowIndex = $headerResult['row_index'];
            $header = $headerResult['header'];
            $enrollIndex = $headerResult['enroll_index'];
            $nameIndex = $headerResult['name_index'];
            $mentorIndex = $headerResult['mentor_index'];
            $batchIndex = $headerResult['batch_index'];

            // Record the sheet mapping for display
            $mapping = [
                'sheet_name' => $sheetName,
                'header_row' => $headerRowIndex + 1, // 1-indexed for display
                'detected_headers' => array_filter($header, fn($h) => !empty($h)),
                'mapped_columns' => [],
            ];
            if ($enrollIndex !== false) {
                $mapping['mapped_columns'][] = ['original' => $header[$enrollIndex], 'mapped_to' => 'Enrollment Number'];
            }
            if ($nameIndex !== false) {
                $mapping['mapped_columns'][] = ['original' => $header[$nameIndex], 'mapped_to' => 'Student Name'];
            }
            if ($mentorIndex !== false) {
                $mapping['mapped_columns'][] = ['original' => $header[$mentorIndex], 'mapped_to' => 'Mentor Faculty'];
            }
            if ($batchIndex !== false) {
                $mapping['mapped_columns'][] = ['original' => $header[$batchIndex], 'mapped_to' => 'Batch'];
            }
            $sheetMappings[] = $mapping;

            // Detect batch from sheet name
            $sheetBatchName = $this->extractBatchFromSheetName($sheetName);

            $statsProcessedSheets++;

            $lastSeenMentorName = '';
            $lastSeenMentorEmail = '';

            // Process data rows (everything after the header row)
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Apply merged cell values to this row in-place
                foreach ($row as $colIdx => $val) {
                    $key = "{$i}-{$colIdx}";
                    if (isset($mergedCellsMap[$key]) && ($val === null || trim((string)$val) === '')) {
                        $row[$colIdx] = $mergedCellsMap[$key];
                    }
                }

                // Ignore formatting/empty/header rows
                if ($this->isFormattingOrEmptyRow($row, $enrollIndex, $nameIndex)) {
                    continue;
                }

                // Get enrollment and name
                $enrollment = $enrollIndex !== false ? trim((string)($row[$enrollIndex] ?? '')) : '';
                $studentName = $nameIndex !== false ? trim((string)($row[$nameIndex] ?? '')) : '';

                // Skip if both enrollment and student name are completely empty
                if (empty($enrollment) && empty($studentName)) {
                    continue;
                }

                // Skip and warn if student ID or name is missing
                if (empty($enrollment)) {
                    $allWarnings[] = "Sheet \"{$sheetName}\", Row " . ($i + 1) . ": Student ID is missing. Skipped.";
                    $statsValidationErrors++;
                    continue;
                }

                if (empty($studentName)) {
                    $allWarnings[] = "Sheet \"{$sheetName}\", Row " . ($i + 1) . ": Student Name is missing. Skipped.";
                    $statsValidationErrors++;
                    continue;
                }

                $mentorRaw = $mentorIndex !== false ? trim((string)($row[$mentorIndex] ?? '')) : '';
                $batchRaw = $batchIndex !== false ? trim((string)($row[$batchIndex] ?? '')) : '';

                // Extract mentor name (handle combined "Faculty name & Batch" columns)
                $mentorName = $this->extractMentorName($mentorRaw);

                if (!empty($mentorName)) {
                    // Generate mentor email
                    $cleanName = strtolower(str_replace([' ', '.', ','], '', $mentorName));
                    // Remove common titles
                    $cleanName = preg_replace('/^(dr|prof|mr|mrs|ms|shri)/', '', $cleanName);
                    $cleanName = ltrim($cleanName, '.');
                    if (empty($cleanName)) {
                        $cleanName = strtolower(str_replace(' ', '', $mentorName));
                    }
                    $mentorEmail = $cleanName . '.it@charusat.ac.in';

                    // Update last seen mentor
                    $lastSeenMentorName = $mentorName;
                    $lastSeenMentorEmail = $mentorEmail;
                } else {
                    // Carry forward / batch-wise grouping logic
                    $mentorName = $lastSeenMentorName;
                    $mentorEmail = $lastSeenMentorEmail;
                }

                // Warn if mentor not detected after propagation, but do NOT fail!
                if (empty($mentorName)) {
                    $allWarnings[] = "Sheet \"{$sheetName}\", Row " . ($i + 1) . " (Student '{$enrollment}'): Mentor not detected. Student imported without guide assignment.";
                    $mentorEmail = '';
                }

                // Determine batch name: prefer column value, then sheet name detection
                $batchName = '';
                if (!empty($batchRaw) && strtolower($batchRaw) !== 'n/a') {
                    $batchName = $batchRaw;
                } elseif (!empty($sheetBatchName)) {
                    $batchName = $sheetBatchName;
                }

                // Check if student exists
                $student = User::where('enrollment_number', $enrollment)->first();

                // Check if mentor exists
                $mentor = !empty($mentorEmail) ? User::where('email', $mentorEmail)->first() : null;

                $status = '';
                $statusColor = '';

                if (!$student) {
                    $status = 'Will Create Student';
                    $statusColor = 'text-indigo-600 bg-indigo-50 border border-indigo-100';
                    $statsNewStudents++;
                } else {
                    $status = 'Update Assignment';
                    $statusColor = 'text-green-600 bg-green-50';
                    $statsStudentsFound++;
                }

                $mentorStatus = 'N/A';
                if (!empty($mentorName)) {
                    if ($mentor) {
                        $mentorStatus = 'Existing Faculty';
                        if (!in_array($mentorEmail, $seenMentorEmails)) {
                            $statsExistingFaculty++;
                        }
                    } else {
                        $mentorStatus = 'Will Create Faculty';
                        if (!in_array($mentorEmail, $seenMentorEmails)) {
                            $statsNewFaculty++;
                        }
                    }
                    $seenMentorEmails[] = $mentorEmail;
                }

                // Track batch stats
                if (!empty($batchName) && $batchName !== 'N/A' && !in_array($batchName, $seenBatchNames)) {
                    $existingBatch = Batch::where('name', $batchName)->exists();
                    if (!$existingBatch) {
                        $statsNewBatches++;
                    }
                    $seenBatchNames[] = $batchName;
                }

                $allPreviewRows[] = [
                    'enrollment' => $enrollment,
                    'student_name' => $studentName ?: ($student ? $student->name : 'N/A'),
                    'batch' => $batchName ?: ($student && $student->batch ? $student->batch->name : 'N/A'),
                    'mentor_name' => $mentorName ?: 'N/A',
                    'mentor_email' => $mentorEmail ?: 'N/A',
                    'status' => $status,
                    'status_color' => $statusColor,
                    'mentor_status' => $mentorStatus,
                    'student_exists' => $student ? true : false,
                    'source_sheet' => $sheetName,
                ];
            }
        }

        $stats = [
            'total_sheets' => $statsTotalSheets,
            'processed_sheets' => $statsProcessedSheets,
            'skipped_sheets' => $statsSkippedSheets,
            'total_rows' => count($allPreviewRows),
            'students_found' => $statsStudentsFound,
            'new_students' => $statsNewStudents,
            'new_faculty' => $statsNewFaculty,
            'existing_faculty' => $statsExistingFaculty,
            'new_batches' => $statsNewBatches,
            'detected_batches' => count($seenBatchNames),
            'detected_mentors' => count(array_filter(array_unique($seenMentorEmails))),
            'validation_errors' => $statsValidationErrors,
        ];

        return [
            'preview_rows' => $allPreviewRows,
            'warnings' => $allWarnings,
            'stats' => $stats,
            'sheet_mappings' => $sheetMappings,
        ];
    }

    /**
     * Scan the first 10 rows to find the actual header row.
     * Returns null if no valid header row is found.
     *
     * A valid header row must contain at least an enrollment-like column
     * and a mentor-like column.
     */
    private function detectHeaderRow(array $rows): ?array
    {
        $maxScan = min(10, count($rows));

        $bestMatch = null;
        $bestScore = 0;

        for ($rowIdx = 0; $rowIdx < $maxScan; $rowIdx++) {
            $row = $rows[$rowIdx];
            if (empty($row)) continue;

            // Normalize the row values
            $normalized = array_map(fn($cell) => strtolower(trim((string)($cell ?? ''))), $row);

            // Try to match columns
            $enrollIdx = $this->fuzzyFindColumn($normalized, self::ENROLLMENT_ALIASES);
            $nameIdx = $this->fuzzyFindColumn($normalized, self::STUDENT_NAME_ALIASES);
            $mentorIdx = $this->fuzzyFindColumn($normalized, self::MENTOR_ALIASES);
            $batchIdx = $this->fuzzyFindColumn($normalized, self::BATCH_ALIASES);

            // Must have at least enrollment + mentor to be valid
            if ($enrollIdx === false && $mentorIdx === false) {
                continue;
            }

            // Score this row: enrollment + mentor = minimum viable
            $score = 0;
            if ($enrollIdx !== false) $score += 10;
            if ($mentorIdx !== false) $score += 10;
            if ($nameIdx !== false) $score += 5;
            if ($batchIdx !== false) $score += 3;

            // Prefer rows with more non-empty cells that look like text headers
            $textCells = 0;
            foreach ($row as $cell) {
                $val = trim((string)($cell ?? ''));
                if (!empty($val) && !is_numeric($val) && strlen($val) > 1) {
                    $textCells++;
                }
            }
            $score += $textCells;

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = [
                    'row_index' => $rowIdx,
                    'header' => $normalized,
                    'enroll_index' => $enrollIdx,
                    'name_index' => $nameIdx,
                    'mentor_index' => $mentorIdx,
                    'batch_index' => $batchIdx,
                ];
            }
        }

        // If we found a header with at least enrollment OR mentor
        if ($bestMatch !== null && ($bestMatch['enroll_index'] !== false || $bestMatch['mentor_index'] !== false)) {
            return $bestMatch;
        }

        return null;
    }

    /**
     * Fuzzy column matching: find the best matching column index.
     * Uses exact match first, then substring/contains matching.
     */
    private function fuzzyFindColumn(array $normalizedRow, array $aliases): int|false
    {
        // Pass 1: Exact match
        foreach ($aliases as $alias) {
            foreach ($normalizedRow as $idx => $cell) {
                if ($cell === $alias) {
                    return $idx;
                }
            }
        }

        // Pass 2: Cell contains the alias (for multi-word headers)
        foreach ($aliases as $alias) {
            if (strlen($alias) < 3) continue; // Skip very short aliases for contains matching
            foreach ($normalizedRow as $idx => $cell) {
                if (!empty($cell) && str_contains($cell, $alias)) {
                    return $idx;
                }
            }
        }

        // Pass 3: Alias contains the cell value (e.g., cell is "id" and alias is "student id")
        foreach ($normalizedRow as $idx => $cell) {
            if (empty($cell) || strlen($cell) < 2) continue;
            foreach ($aliases as $alias) {
                // Don't match cell "name" to batch/class/group/section aliases
                if ($cell === 'name' && (str_contains($alias, 'batch') || str_contains($alias, 'class') || str_contains($alias, 'group') || str_contains($alias, 'section') || str_contains($alias, 'division'))) {
                    continue;
                }

                // Don't match cell "id" to faculty/mentor/guide aliases
                if ($cell === 'id' && (str_contains($alias, 'faculty') || str_contains($alias, 'mentor') || str_contains($alias, 'guide') || str_contains($alias, 'teacher') || str_contains($alias, 'counsellor') || str_contains($alias, 'counselor') || str_contains($alias, 'advisor'))) {
                    continue;
                }

                if (str_contains($alias, $cell) && strlen($cell) >= 2) {
                    // Extra check: "id" alone is too broad — require it to be a standalone cell
                    if ($cell === 'id' || $cell === 'name' || $cell === 'faculty' || $cell === 'guide' || $cell === 'mentor' || $cell === 'counsellor' || $cell === 'counselor') {
                        return $idx;
                    }
                    // For longer values, match if the cell is a significant part of the alias
                    if (strlen($cell) >= 4) {
                        return $idx;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Extract a batch name from a sheet name.
     * Handles patterns like: "2023-Batch-Sem6", "2024 Batch Sem 4", "6_ELEC_RM", "Batch A", etc.
     */
    private function extractBatchFromSheetName(string $sheetName): string
    {
        $name = trim($sheetName);

        // Skip generic sheet names
        $skipNames = ['sheet1', 'sheet2', 'sheet3', 'sheet 1', 'sheet 2', 'sheet 3', 'data', 'main', 'import'];
        if (in_array(strtolower($name), $skipNames)) {
            return '';
        }

        // If the name looks meaningful, use it directly as the batch name
        if (strlen($name) >= 2) {
            return $name;
        }

        return '';
    }

    /**
     * Extract the mentor name from a raw cell value.
     * Handles combined columns like "Faculty name & Batch" where the value
     * might just be a name, or might contain extra info.
     */
    private function extractMentorName(string $raw): string
    {
        $raw = trim($raw);
        if (empty($raw)) return '';

        // If the value contains a pipe or semicolon separator, take the first part
        if (str_contains($raw, '|')) {
            $raw = trim(explode('|', $raw)[0]);
        }
        if (str_contains($raw, ';')) {
            $raw = trim(explode(';', $raw)[0]);
        }

        // If it contains "batch" or "sem" after the name, strip that suffix
        // e.g., "Dr. Patel - Batch A" → "Dr. Patel"
        $raw = preg_replace('/\s*[-–]\s*(batch|sem|section|div|group)\b.*$/i', '', $raw);

        // If it contains parenthetical batch info, strip it
        // e.g., "Dr. Patel (Batch 1)" → "Dr. Patel"
        $raw = preg_replace('/\s*\(.*?(batch|sem|section|div|group).*?\)\s*$/i', '', $raw);

        return trim($raw);
    }

    /**
     * Check if a row is likely a formatting, heading, empty, or section row.
     */
    private function isFormattingOrEmptyRow(array $row, $enrollIndex, $nameIndex): bool
    {
        // 1. Check if the row is entirely empty or has only empty cells
        $nonEmptyCells = array_filter($row, fn($cell) => trim((string)($cell ?? '')) !== '');
        if (empty($nonEmptyCells)) {
            return true;
        }

        // 2. If it has very few non-empty cells and no student ID, it is likely a title or decorative row
        $enrollment = $enrollIndex !== false ? trim((string)($row[$enrollIndex] ?? '')) : '';
        if (empty($enrollment) && count($nonEmptyCells) <= 2) {
            return true;
        }

        // 3. Check if enrollment contains formatting or section headers
        if (!empty($enrollment)) {
            if ($this->isLikelySectionLabel($enrollment)) {
                return true;
            }
            if (preg_match('/^[_\-\*=+\.\s]+$/', $enrollment)) {
                return true;
            }
            // Check if enrollment matches a header alias
            $lowerEnroll = strtolower($enrollment);
            foreach (self::ENROLLMENT_ALIASES as $alias) {
                if ($lowerEnroll === $alias) {
                    return true;
                }
            }
        }

        // 4. Check if student name matches a header alias
        $studentName = $nameIndex !== false ? trim((string)($row[$nameIndex] ?? '')) : '';
        if (!empty($studentName)) {
            $lowerName = strtolower($studentName);
            foreach (self::STUDENT_NAME_ALIASES as $alias) {
                if ($lowerName === $alias) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a cell value looks like a section label/sub-header rather than data.
     */
    private function isLikelySectionLabel(string $value): bool
    {
        $lower = strtolower($value);

        // Common non-data patterns in departmental sheets
        $patterns = [
            'faculty of',
            'department of',
            'institute of',
            'college of',
            'university',
            'total',
            'grand total',
            'sr no',
            'sr. no',
            'sr.no',
            'serial',
            'semester',
            'sem ',
            'academic year',
            'counsellor wise list',
            'mentor wise list',
            'student list',
            'it department',
            'btech',
            'mtech',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
