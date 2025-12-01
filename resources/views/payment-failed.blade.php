<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(238, 90, 111, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(238, 90, 111, 0.4);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 15px 40px rgba(238, 90, 111, 0.6);
            }
        }

        .icon-container svg {
            width: 50px;
            height: 50px;
            stroke: white;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 16px;
            color: #718096;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .error-details {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 32px;
            text-align: left;
        }

        .error-details p {
            color: #c53030;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
        }

        .error-label {
            font-weight: 600;
            margin-bottom: 4px;
            display: block;
        }

        .button-group {
            display: flex;
            gap: 12px;
            flex-direction: column;
        }

        .btn {
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #f7fafc;
            transform: translateY(-2px);
        }

        .support-text {
            margin-top: 24px;
            font-size: 14px;
            color: #a0aec0;
        }

        .support-text a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .support-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .container {
                padding: 32px 24px;
            }

            h1 {
                font-size: 26px;
            }

            .icon-container {
                width: 80px;
                height: 80px;
            }

            .icon-container svg {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon-container">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>

        <h1>Payment Failed</h1>
        <p class="subtitle">
            We couldn't process your payment. Don't worry, no charges were made to your account.
        </p>

        @if(request()->has('error') || request()->has('message'))
            <div class="error-details">
                <span class="error-label">Error Details:</span>
                <p>{{ request()->get('error') ?? request()->get('message') ?? 'Payment was cancelled or declined.' }}</p>
            </div>
        @endif

        <div class="button-group">
            <a href="{{ url('/') }}" class="btn btn-secondary">
                Return to Home
            </a>
        </div>

        <p class="support-text">
            Need help? <a href="mailto:{{ config('mail.from.address', 'support@example.com') }}">Contact Support</a>
        </p>
    </div>
</body>

</html>