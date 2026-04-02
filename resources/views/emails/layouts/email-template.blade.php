<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .email-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333333;
        }

        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .content {
            font-size: 16px;
            line-height: 1.6;
            color: #555555;
        }

        .content p {
            margin: 0 0 12px;
        }

        .button-container {
            margin: 30px 0;
            text-align: center;
        }

        .button {
            display: inline-block;
            background-color: #5C5CDB !important;
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .button:hover {
            background-color: #7656D1 !important;
            color: #ffffff !important;
        }

        .footer {
            font-size: 14px;
            color: #777777;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="email-container">
    @yield('header')
    @yield('greeting')
    @yield('content')
    <div class="footer">
        Best regards,<br>
        {{config('app.name')}}<br>
        &copy; {{ date('Y') }} {{config('app.name')}}. All rights reserved.
    </div>
</div>
</body>
</html>
