<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - {{ config('app.name') }}</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 15px 40px rgba(16, 185, 129, 0.6);
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

        .transaction-details {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 32px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #d1fae5;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #065f46;
            font-size: 14px;
        }

        .detail-value {
            color: #047857;
            font-size: 14px;
            font-weight: 500;
        }

        .amount {
            font-size: 18px;
            font-weight: 700;
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
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>

        <h1>Payment Successful!</h1>
        <p class="subtitle">
            Your payment has been processed successfully. Thank you for your purchase!
        </p>

        @if(isset($transaction))
            <div class="transaction-details">
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">{{ $transaction->transaction_id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value amount">{{ strtoupper($transaction->currency) }}
                        {{ number_format($transaction->amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">{{ ucfirst($transaction->status) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Gateway:</span>
                    <span class="detail-value">{{ ucfirst($transaction->payment_gateway) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ $transaction->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
        @endif

        <div class="button-group">
            <a href="{{ url('/') }}" class="btn btn-primary">
                Return to Home
            </a>
        </div>

        <p class="support-text">
            Need help? <a href="mailto:{{ config('mail.from.address', 'support@example.com') }}">Contact Support</a>
        </p>
    </div>
</body>

</html>