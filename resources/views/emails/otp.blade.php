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
        .otp-box {
            background-color: #EEF2FF;
            color: #4F46E5;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            font-size: 32px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            letter-spacing: 8px;
            margin: 25px 0;
            border: 1px dashed #4F46E5;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6B7280;
            font-size: 12px;
        }
        .warning-note {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 20px 0;
            font-size: 13px;
            color: #92400E;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Internship NOC Portal</h1>
        </div>
        
        <div class="content">
            <h2>Reset Your Password</h2>
            <p>Hello,</p>
            <p>You requested to reset your password on the Internship NOC Portal. Use the following One-Time Password (OTP) to complete the verification process. This OTP is valid for 10 minutes:</p>
            
            <div class="otp-box">
                {{ $otp }}
            </div>
            
            <div class="warning-note">
                <strong>🔒 Security Warning:</strong><br>
                Never share this code with anyone. If you did not request a password reset, please ignore this email or contact support.
            </div>
            
            <p>Thank you,<br>The NOC Portal Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply directly to this message.</p>
        </div>
    </div>
</body>
</html>
