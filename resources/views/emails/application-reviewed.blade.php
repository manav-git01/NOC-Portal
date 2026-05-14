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
            background-color: {{ $approval->status === 'approved' ? '#10B981' : '#EF4444' }};
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
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            background-color: {{ $approval->status === 'approved' ? '#D1FAE5' : '#FEE2E2' }};
            color: {{ $approval->status === 'approved' ? '#065F46' : '#991B1B' }};
        }
        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid {{ $approval->status === 'approved' ? '#10B981' : '#EF4444' }};
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
            background-color: {{ $approval->status === 'approved' ? '#10B981' : '#EF4444' }};
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Application {{ ucfirst($approval->status) }}</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $application->user->name }},</p>
            
            <p>Your internship NOC application has been reviewed.</p>
            
            <center>
                <span class="status-badge">{{ strtoupper($approval->status) }}</span>
            </center>
            
            <div class="info-box">
                <h3 style="margin-top: 0;">Application Details</h3>
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
            
            <div class="info-box">
                <h3 style="margin-top: 0;">Review Details</h3>
                <div class="info-row">
                    <span class="label">Reviewed By:</span> {{ $approval->approver->name }} ({{ $approval->approver->role->display_name ?? ucfirst(str_replace('_', ' ', $approval->approver->role->name)) }})
                </div>
                <div class="info-row">
                    <span class="label">Date:</span> {{ $approval->created_at->format('d M Y, h:i A') }}
                </div>
                @if($approval->remarks)
                <div class="info-row">
                    <span class="label">Remarks:</span><br>
                    <p style="margin: 5px 0; padding: 10px; background-color: #F3F4F6; border-radius: 5px;">
                        {{ $approval->remarks }}
                    </p>
                </div>
                @endif
            </div>
            
            @if($approval->status === 'approved')
                @if($approval->approver_role === 'higher_faculty')
                    <p style="color: #059669; font-weight: bold;">
                        ✓ Your NOC has been generated. You can now download it from the website.
                    </p>
                @else
                    <p style="color: #059669; font-weight: bold;">
                        ✓ Your application has been approved! If you want NOC then request it from website.
                    </p>
                @endif
            @else
                <p style="color: #DC2626; font-weight: bold;">
                    Unfortunately, your application has been rejected. Please review the remarks above for more information.
                </p>
            @endif
            
            <center>
                <a href="{{ route('student.applications.show', $application) }}" class="button">
                    {{ $approval->approver_role === 'higher_faculty' ? 'View & Download NOC' : 'View Application' }}
                </a>
            </center>
        </div>
        
        <div class="footer">
            @if($approval->approver_role === 'higher_faculty' && $approval->status === 'approved')
                <p style="font-weight: bold; color: #10B981; margin-bottom: 15px;">Your NOC has been generated. Download from the website.</p>
            @endif
            <p>This is an automated email from the Internship NOC Portal.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>