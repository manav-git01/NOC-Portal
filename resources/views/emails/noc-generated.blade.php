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
            background-color: #10B981;
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
        .success-icon {
            font-size: 48px;
            text-align: center;
            margin: 20px 0;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #10B981;
        }
        .info-row {
            margin: 8px 0;
        }
        .label {
            font-weight: bold;
            color: #4B5563;
        }
        .noc-number {
            background-color: #D1FAE5;
            color: #065F46;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #10B981;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6B7280;
            font-size: 12px;
        }
        .important-note {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Your NOC is Ready!</h1>
        </div>
        
        <div class="content">
            <div class="success-icon">✅</div>
            
            <p>Dear {{ $application->user->name }},</p>
            
            <p><strong>Congratulations!</strong> Your internship No Objection Certificate (NOC) has been successfully generated and approved.</p>
            
            <div class="noc-number">
                NOC Number: {{ $application->noc->noc_number }}
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
                <div class="info-row">
                    <span class="label">Location:</span> {{ $application->company_address }}
                </div>
            </div>
            
            <div class="important-note">
                <strong>📎 NOC Document Attached</strong><br>
                Your official NOC document is attached to this email as a PDF file. You can also download it from the portal at any time.
            </div>
            
            <center>
                <a href="{{ route('student.applications.show', $application) }}" class="button">
                    View Application & Download NOC
                </a>
            </center>
            
            <div style="margin-top: 30px; padding: 15px; background-color: #EFF6FF; border-radius: 5px;">
                <p style="margin: 0; color: #1E40AF; font-size: 14px;">
                    <strong>Next Steps:</strong>
                </p>
                <ul style="color: #1E40AF; font-size: 14px;">
                    <li>Download and print the NOC document</li>
                    <li>Submit it to your internship company</li>
                    <li>Keep a copy for your records</li>
                    <li>Enjoy your internship experience!</li>
                </ul>
            </div>
            
            <p style="margin-top: 30px; color: #6B7280; font-size: 14px;">
                If you have any questions or need assistance, please contact the administration office.
            </p>
        </div>
        
        <div class="footer">
            <p style="font-weight: bold; color: #10B981; margin-bottom: 15px;">Your NOC has been generated. Download from the website.</p>
            <p>This is an automated email from the Internship NOC Portal.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>