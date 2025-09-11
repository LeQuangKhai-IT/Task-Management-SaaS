<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 20px;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .message {
            font-size: 16px;
            color: #555555;
            margin-bottom: 30px;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .note {
            font-size: 14px;
            color: #777777;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .footer {
            font-size: 14px;
            color: #999999;
            border-top: 1px solid #eeeeee;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            You're Nearly There!
        </div>
        <p class="greeting">Hi {{ $email ?? 'there' }},</p>
        <p class="message">
            To finish setting up your account and start using {{ config('app.name') }},
            please confirm we have the correct email for you.
        </p>
        <a href="{{ $url }}" class="button">
            Verify Your Email
        </a>
        <p class="note">If you did not request this, please ignore this email.</p>
        <div class="footer">
            Thanks,<br>
            {{ config('app.name') }}
        </div>
    </div>
</body>

</html>