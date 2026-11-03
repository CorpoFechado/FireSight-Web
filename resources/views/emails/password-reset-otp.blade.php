<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Password Reset Verification Code</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #f1f5f9;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      color: #1e293b;
      -webkit-font-smoothing: antialiased;
    }
    .wrapper {
      width: 100%;
      background-color: #f1f5f9;
      padding: 40px 16px;
      box-sizing: border-box;
    }
    .container {
      max-width: 520px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
      border: 1px solid #e2e8f0;
    }
    .header {
      background-color: #0b1528;
      padding: 32px 24px;
      text-align: center;
    }
    .brand-title {
      color: #ffffff;
      font-size: 24px;
      font-weight: 800;
      letter-spacing: 2px;
      margin: 0;
    }
    .brand-accent {
      color: #ea580c;
    }
    .content {
      padding: 36px 32px;
    }
    .greeting {
      font-size: 18px;
      font-weight: 700;
      color: #0f172a;
      margin: 0 0 16px;
    }
    .body-text {
      font-size: 15px;
      line-height: 24px;
      color: #475569;
      margin: 0 0 24px;
    }
    .otp-card {
      background-color: #f8fafc;
      border: 2px dashed #cbd5e1;
      border-radius: 12px;
      padding: 24px 16px;
      text-align: center;
      margin: 28px 0;
    }
    .otp-label {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: #64748b;
      margin-bottom: 8px;
    }
    .otp-code {
      font-size: 36px;
      font-weight: 800;
      letter-spacing: 8px;
      color: #ea580c;
      font-family: 'Courier New', Courier, monospace;
      margin: 0;
    }
    .otp-expiry {
      font-size: 13px;
      color: #64748b;
      margin-top: 10px;
    }
    .security-note {
      background-color: #fffbeb;
      border-left: 4px solid #f59e0b;
      padding: 12px 16px;
      border-radius: 6px;
      margin-top: 24px;
    }
    .security-note p {
      margin: 0;
      font-size: 13px;
      line-height: 20px;
      color: #92400e;
    }
    .footer {
      background-color: #f8fafc;
      padding: 24px 32px;
      text-align: center;
      border-top: 1px solid #e2e8f0;
    }
    .footer-text {
      font-size: 12px;
      color: #94a3b8;
      line-height: 18px;
      margin: 0;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
      <!-- Header -->
      <div class="header">
        <h1 class="brand-title">FIRE<span class="brand-accent">SIGHT</span></h1>
      </div>

      <!-- Main Body -->
      <div class="content">
        <h2 class="greeting">Hello {{ $firstName ? $firstName : 'Resident' }},</h2>
        <p class="body-text">
          We received a request to reset your password for your FireSight Resident mobile account. Please use the verification code below to proceed:
        </p>

        <!-- 6-Digit OTP Block -->
        <div class="otp-card">
          <div class="otp-label">Verification Code</div>
          <div class="otp-code">{{ $otp }}</div>
          <div class="otp-expiry">Valid for <strong>15 minutes</strong></div>
        </div>

        <p class="body-text">
          Enter this 6-digit code in the FireSight mobile app to verify your identity and create your new password.
        </p>

        <!-- Security Disclaimer -->
        <div class="security-note">
          <p>
            <strong>Security Notice:</strong> If you did not request a password reset, you can safely disregard this email. Your password will remain unchanged. Never share this code with anyone.
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="footer">
        <p class="footer-text">
          &copy; {{ date('Y') }} FireSight &bull; Bureau of Fire Protection<br>
          This is an automated security email. Please do not reply directly to this message.
        </p>
      </div>
    </div>
  </div>
</body>
</html>
