
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد تسجيل الحساب</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            color: #3A63ED;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>تأكيد تسجيل الحساب</h2>
        <p>مرحباً بك،</p>
        <p>شكراً لتسجيلك في تطبيقنا. لإكمال عملية التسجيل، يرجى استخدام رمز التحقق التالي:</p>

        <div class="code">{{ $otp }}</div>

        <p>إذا لم تقم بإنشاء هذا الحساب، يمكنك تجاهل هذا البريد الإلكتروني.</p>

        <div class="footer">
            <p>سعداء بانضمامك إلينا! 💙</p>
        </div>
    </div>
</body>
</html>
