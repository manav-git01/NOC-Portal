<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4F46E5;
        }
        .info-row {
            margin: 8px 0;
        }
        .label {
            font-weight: bold;
            color: #4B5563;
        }
        .button {
            display: inline-block;
            background-color: #4F46E5;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6B7280;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Internship NOC Application</h1>
        </div>
        
        <div class="content">
            <p>Dear Faculty,</p>
            
            <p>A new internship NOC application has been submitted and requires your review.</p>
            
            <div class="info-box">
                <h3 style="margin-top: 0;">Student Information</h3>
                <div class="info-row">
                    <span class="label">Name:</span> {{ $application->user->name }}
                </div>
                <div class="info-row">
                    <span class="label">Enrollment Number:</span> {{ $application->user->enrollment_number }}
                </div>
                <div class="info-row">
                    <span class="label">Department:</span> {{ $application->user->department }}
                </div>
                <div class="info-row">
                    <span class="label">Semester:</span> {{ $application->user->semester }}
                </div>
            </div>
            
            <div class="info-box">
                <h3 style="margin-top: 0;">Internship Details</h3>
                <div class="info-row">
                    <span class="label">Company:</span> {{ $application->company_name }}
                </div>
                <div class="info-row">
                    <span class="label">Position:</span> {{ $application->internship_position }}
                </div>
                <div class="info-row">
                    <span class="label">Duration:</span> {{ \Carbon\Carbon::parse($application->start_date)->format('d M Y') }} to {{ \Carbon\Carbon::parse($application->end_date)->format('d M Y') }}
                </div>
            </div>
            
            <center>
                <a href="{{ route('faculty.applications.show', $application) }}" class="button">
                    Review Application
                </a>
            </center>
            
            <p style="margin-top: 30px; color: #6B7280; font-size: 14px;">
                Please review this application at your earliest convenience.
            </p>
        </div>
        
        <div class="footer">
            <p>This is an automated email from the Internship NOC Portal.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>