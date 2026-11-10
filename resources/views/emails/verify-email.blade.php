<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Your Email Address</title>
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
    .action-container {
      text-align: center;
      margin: 32px 0;
    }
    .btn-verify {
      display: inline-block;
      background-color: #ea580c;
      color: #ffffff !important;
      font-size: 16px;
      font-weight: 700;
      text-decoration: none;
      padding: 14px 32px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
      transition: background-color 0.2s;
    }
    .btn-verify:hover {
      background-color: #c2410c;
    }
    .info-card {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 16px;
      margin-top: 24px;
    }
    .info-card p {
      margin: 0;
      font-size: 13px;
      line-height: 20px;
      color: #64748b;
    }
    .url-fallback {
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid #f1f5f9;
      font-size: 12px;
      line-height: 18px;
      color: #94a3b8;
      word-break: break-all;
    }
    .url-fallback a {
      color: #ea580c;
      text-decoration: none;
    }
    .footer {
      background-color: #f8fafc;
      padding: 20px 24px;
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
      <div class="header">
        <h1 class="brand-title">FIRE<span class="brand-accent">SIGHT</span></h1>
      </div>
      <div class="content">
        <h2 class="greeting">Hello, {{ $firstName }}!</h2>
        <p class="body-text">
          Thank you for registering with <strong>FireSight</strong>, the official municipal fire safety and emergency reporting system for Lian, Batangas.
        </p>
        <p class="body-text">
          Please confirm your email address by clicking the button below to activate your account.
        </p>

        <div class="action-container">
          <a href="{{ $verificationUrl }}" class="btn-verify" target="_blank">Verify Email Address</a>
        </div>

        <div class="info-card">
          <p>
            <strong>Note:</strong> This verification link is valid for <strong>60 minutes</strong>. If you did not create a FireSight account, no further action is required and you can safely ignore this email.
          </p>
        </div>

        <div class="url-fallback">
          If the button above does not work, copy and paste this link into your browser:<br>
          <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
        </div>
      </div>
      <div class="footer">
        <p class="footer-text">
          Bureau of Fire Protection &bull; Lian Municipal Fire Station &bull; Batangas<br>
          This is an automated system email. Please do not reply directly to this message.
        </p>
      </div>
    </div>
  </div>
</body>
</html>
