<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode Verifikasi Email</title>
</head>

<body style="font-family: Arial, sans-serif; color: #1e293b; line-height: 1.6;">
    <h2>Verifikasi Email Registrasi</h2>

    <p>Gunakan kode OTP berikut untuk memverifikasi email Anda:</p>

    <div style="font-size: 32px; font-weight: bold; letter-spacing: 8px; padding: 16px 0;">
        {{ $otp }}
    </div>

    <p>Kode berlaku selama <strong>5 menit</strong>.</p>

    <p>Jangan bagikan kode ini kepada siapa pun.</p>

    <p>Jika Anda tidak merasa melakukan registrasi, abaikan email ini.</p>
</body>

</html>
