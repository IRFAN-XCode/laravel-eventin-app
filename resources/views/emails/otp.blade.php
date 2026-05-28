<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333333;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eeeeee;
            border-radius: 5px;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #4e73df;
            text-align: center;
            margin: 30px 0;
            padding: 10px;
            background-color: #f8f9fc;
            border-radius: 5px;
        }
        .footer {
            font-size: 12px;
            color: #777777;
            margin-top: 30px;
            border-top: 1px solid #eeeeee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Halo,</h2>
        <p>Kami menerima permintaan untuk mereset password akun Anda di aplikasi.</p>
        <p>Berikut adalah kode OTP Anda untuk memverifikasi perubahan ini:</p>
        
        <div class="otp-code">{{ $otp }}</div>
        
        <p>Kode ini hanya berlaku selama 10 menit. Demi keamanan akun Anda, jangan bagikan kode OTP ini kepada siapa pun.</p>
        <p>Jika Anda tidak merasa melakukan permintaan ini, abaikan saja email ini.</p>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>