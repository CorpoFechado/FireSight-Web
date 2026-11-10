<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Verification - FireSight</title>
  <link rel="icon" type="image/png" href="/favicon.png">
  <style>
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #0b1528 0%, #1e293b 100%);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      color: #1e293b;
      box-sizing: border-box;
      padding: 24px;
    }
    .card {
      width: 100%;
      max-width: 480px;
      background-color: #ffffff;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      text-align: center;
    }
    .card-header {
      background-color: #0b1528;
      padding: 32px 24px;
    }
    .brand-title {
      color: #ffffff;
      font-size: 26px;
      font-weight: 800;
      letter-spacing: 2px;
      margin: 0;
    }
    .brand-accent {
      color: #ea580c;
    }
    .card-body {
      padding: 40px 32px;
    }
    .icon-container {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 24px;
    }
    .icon-success {
      background-color: #ecfdf5;
      color: #10b981;
    }
    .icon-error {
      background-color: #fef2f2;
      color: #ef4444;
    }
    .title {
      font-size: 22px;
      font-weight: 700;
      color: #0f172a;
      margin: 0 0 12px;
    }
    .message {
      font-size: 15px;
      line-height: 24px;
      color: #64748b;
      margin: 0 0 32px;
    }
    .btn-action {
      display: inline-block;
      width: 100%;
      box-sizing: border-box;
      background-color: #ea580c;
      color: #ffffff !important;
      font-size: 16px;
      font-weight: 700;
      text-decoration: none;
      padding: 14px 24px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
      transition: background-color 0.2s, transform 0.1s;
    }
    .btn-action:hover {
      background-color: #c2410c;
    }
    .btn-action:active {
      transform: scale(0.98);
    }
    .card-footer {
      background-color: #f8fafc;
      padding: 16px 24px;
      border-top: 1px solid #f1f5f9;
      font-size: 12px;
      color: #94a3b8;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="card-header">
      <h1 class="brand-title">FIRE<span class="brand-accent">SIGHT</span></h1>
    </div>
    <div class="card-body">
      @if ($status === 'success' || $status === 'already_verified')
        <div class="icon-container icon-success">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        </div>
        <h2 class="title">{{ $status === 'already_verified' ? 'Already Verified!' : 'Email Verified Successfully!' }}</h2>
        <p class="message">{{ $message }}</p>

        @if (!empty($deepLink))
          <a href="{{ $deepLink }}" class="btn-action">Return to FireSight App</a>
        @endif
      @else
        <div class="icon-container icon-error">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
        </div>
        <h2 class="title">Verification Failed</h2>
        <p class="message">{{ $message }}</p>
      @endif
    </div>
    <div class="card-footer">
      Bureau of Fire Protection &bull; Lian Municipal Fire Station
    </div>
  </div>
</body>
</html>
