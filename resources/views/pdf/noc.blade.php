<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>No Objection Certificate</title>

<style>
@page {
    margin: 1.2cm;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Times New Roman", Times, serif;
    font-size: 12pt;
    line-height: 1.5;
    color: #000;
    text-rendering: optimizeLegibility;
}


/* ================= HEADER ================= */

.header-container {
    border: 2px solid #000;
    padding: 8px 10px;
    margin-bottom: 15px;
    display: table;
    width: 100%;
    table-layout: fixed;
}

.logo-section {
    display: table-cell;
    width: 80px;
    vertical-align: middle;
}

.logo {
    width: 90px;
    height: 90px;
    max-width: 90px;
    max-height: 90px;
    display: block;
}

.logo-section:first-child {
    text-align: left;
}

.logo-section:last-child {
    text-align: right;
}

.logo-section:first-child .logo {
    margin-left: 0;
}

.logo-section:last-child .logo {
    margin-right: 0;
    margin-left: auto;
}

.header-text {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
    padding: 0 6px;
}

.institution-name {
    font-size: 26pt;
    font-weight: bold;
    letter-spacing: 1px;
    margin-bottom: 0px;
    line-height: 1;
}

.sub-institution {
    font-size: 20pt;
    font-weight: bold;
    letter-spacing: 0.5px;
    margin-top: 0px;
    margin-bottom: 2px;
    line-height: 1;
}

.department-name {
    font-size: 13pt;
    margin-top: 4px;
    line-height: 1.2;
    font-weight: normal;
}

/* ================= REF & DATE ================= */

.reference-and-date {
    display: table;
    width: 100%;
    margin-bottom: 14px;
    font-size: 11pt;
    line-height: 1.3;
    padding-left: 1.2cm;
    padding-right: 1.2cm;
}

.reference-col,
.date-col {
    display: table-cell;
    width: 50%;
    vertical-align: bottom;
}

.date-col {
    text-align: right;
}

.color-red {
    color: #c00000;
    font-weight: bold;
}

/* ================= TO SECTION ================= */

.to-section {
    margin-bottom: 22px;
    font-size: 12pt;
    line-height: 1.6;
    padding-left: 1.2cm;
    padding-right: 1.2cm;
}

.to-block {
    padding-left: 1.2cm;   /* 🔥 applies to all lines */
}



/* ================= SUBJECT ================= */

.subject-section {
    text-align: center;
    margin-bottom: 22px;
    font-size: 12pt;
    padding-left: 1.2cm;
    padding-right: 1.2cm;
}

.subject-label {
    font-size: 13.5pt;    /* 🔥 slightly bigger */
    font-weight: bold;
}

.subject-text {
    font-size: 12.5pt;
    font-weight: bold;
    text-decoration: underline;
}


/* ================= CONTENT ================= */

.content {
    font-size: 12pt;
    line-height: 1.5;

    /* 🔥 ADD THESE LINES */
    padding-left: 1.2cm;
    padding-right: 1.2cm;
}

.content p {
    margin-bottom: 12px;
    text-align: justify;
    text-justify: inter-word;
    hyphens: auto;
}

/* ================= SIGNATURE ================= */

.closing {
    margin-top: 18px;
    margin-bottom: 10px;
}

.signature-section {
    margin-top: 45px;
    text-align: left;
}

.signature-image {
    width: 120px;
    height: auto;
    margin-bottom: 6px;
    display: block;
    margin-left: 50px; /* Centered over 220px line: (220-120)/2 */
}

.signature-line {
    border-top: 1px solid #000;
    width: 220px;
    padding-top: 4px;
    font-weight: bold;
    font-size: 12pt;
    margin: 0;
    text-align: left;
}

.tpo-details {
    margin-top: 6px;
    font-size: 10pt;
    line-height: 1.4;
}

.color-blue {
    color: #0000ee;
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header-container">
    <div class="logo-section">
        @php
            $hasCharusatLogo = isset($charusatLogo) && !empty($charusatLogo) && strlen($charusatLogo) > 100;
        @endphp
        @if($hasCharusatLogo)
            <img src="data:image/jpeg;base64,{!! $charusatLogo !!}" alt="CHARUSAT Logo" class="logo">
        @else
            <!-- DEBUG: charusatLogo is {{ isset($charusatLogo) ? 'set but empty/short' : 'not set' }} -->
            <div style="width: 70px; height: 70px; background: #f0f0f0; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #999;">CHARUSAT</div>
        @endif
    </div>

    <div class="header-text">
        <div class="institution-name">CHARUSAT</div>
        <div class="sub-institution">CSPIT</div>
        <div class="department-name">
            Smt. Kundalben Dinsha Patel Department of<br>
            Information Technology
        </div>
    </div>

    <div class="logo-section">
        @php
            $hasCspitLogo = isset($cspitLogo) && !empty($cspitLogo) && strlen($cspitLogo) > 100;
        @endphp
        @if($hasCspitLogo)
            <img src="data:image/png;base64,{!! $cspitLogo !!}" alt="CSPIT Logo" class="logo">
        @else
            <!-- DEBUG: cspitLogo is {{ isset($cspitLogo) ? 'set but empty/short' : 'not set' }} -->
            <div style="width: 70px; height: 70px; background: #f0f0f0; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #999;">CSPIT</div>
        @endif
    </div>
</div>

<!-- REF & DATE -->
<div class="reference-and-date">
    <div class="reference-col">
        CSPIT/IT/TPO/<span class="color-red">{{ $application->user->semester }}-INT-{{ $application->start_date->format('Y') }}-{{ str_pad($application->start_date->format('y') + 1, 2, '0', STR_PAD_LEFT) }}</span>/<span class="color-red">{{ $application->user->enrollment_number ?? '00000000' }}</span>
    </div>
    <div class="date-col">
        <strong>Date:</strong> {{ $application->start_date->format('d-m-Y') }}
    </div>
</div>

<!-- TO -->
<div class="to-section">
    <div class="to-label">To,</div>
    <div class="to-content color-red">
        <div>{{ $application->contact_person_name ?? $application->company_name }}</div>
        <div>{{ $application->company_name }}</div>
        <div>{{ $application->company_address }}</div>
    </div>
</div>

<!-- SUBJECT -->
<div class="subject-section">
    <span class="subject-label">Subject:</span>
    <span class="subject-text">
        {{ $application->internship_position ? ucfirst($application->internship_position) . ' Internship' : 'Intern Internship' }} at your Organization
    </span>
</div>

<!-- CONTENT -->
<div class="content">

<p>Dear Sir/Madam,</p>

<p>
<strong>Chandubhai.S.Patel Institute of Technology, Changa [CSPIT] </strong> is a graduate degree engineering college affiliated to
<strong>Charotar University of Science and Technology (CHARUSAT)</strong>.
We run full-time Degree & Post Graduate programs in Electronics & Communication Engineering,
Computer Engineering, Information Technology, Electrical Engineering, Mechanical and Civil Engineering.
</p>

<p>
The students of semester <span class="color-red">{{ $application->user->semester }}<sup>{{ 
    match(true) {
        in_array($application->user->semester % 100, [11, 12, 13]) => 'th',
        $application->user->semester % 10 == 1 => 'st',
        $application->user->semester % 10 == 2 => 'nd',
        $application->user->semester % 10 == 3 => 'rd',
        default => 'th'
    } 
}}</sup></span> of the Smt. Kundanben Dinsha Patel Department of Information Technology
of our Institute are required to undergo industrial training as a part of their academic course content of the University.
This is to certify that the institute does not have any objection if our student
<span class="color-red">
{{ $application->user->name }} ({{ $application->user->enrollment_number }})
</span>
of Information Technology Department undergoes project internship at your organization from
<span class="color-red">{{ $application->start_date->format('d/m/Y') }}</span>
to
<span class="color-red">{{ $application->end_date->format('d/m/Y') }}</span>.
</p>

<p>
In case any issue arises, it should be immediately brought to the notice of the Training & Placement Officer in the department. 
We hereby ensure you of the best behavior and discipline of our student coming in your Organization.
</p>

<p class="closing">Thanks and Regards,</p>

<div class="signature-section">
    @if(isset($tpoSignature) && !empty($tpoSignature))
        <img src="data:image/jpeg;base64,{!! $tpoSignature !!}" alt="Training & Placement Officer Signature" class="signature-image">
    @endif
    <div class="signature-line">Training & Placement Officer</div>
    <div class="tpo-details">
        CHARUSAT, Changa<br>
        Tel: 02697 265213<br>
        Email: <u class="color-blue">tpo@charusat.ac.in</u>
    </div>
</div>

</div>
</body>
</html>
