<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iRPM - Sahkan Emel Anda</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
            margin: 0;
            padding: 40px 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: #1e293b;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .header {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: white;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            color: rgba(255,255,255,0.8);
            margin: 8px 0 0;
            font-size: 14px;
        }
        .content {
            padding: 40px 30px;
            color: #e2e8f0;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .message {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white !important;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
        }
        .button:hover {
            opacity: 0.9;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .footer {
            text-align: center;
            padding: 20px 30px 30px;
            color: #64748b;
            font-size: 12px;
            border-top: 1px solid #334155;
        }
        .link-fallback {
            color: #64748b;
            font-size: 12px;
            word-break: break-all;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 iRPM</h1>
            <p>Sistem Rekod Perkembangan Murid</p>
        </div>
        <div class="content">
            <p class="greeting">Assalamualaikum {{ $user->name }},</p>
            <p class="message">
                Terima kasih kerana mendaftar di iRPM! Untuk melengkapkan pendaftaran anda, 
                sila klik butang di bawah untuk mengesahkan alamat emel anda.
            </p>
            <div class="button-container">
                <a href="{{ $verifyUrl }}" class="button">✅ Sahkan Emel Saya</a>
            </div>
            <p class="link-fallback">
                Jika butang tidak berfungsi, salin dan tampal pautan ini ke pelayar anda:<br>
                {{ $verifyUrl }}
            </p>
        </div>
        <div class="footer">
            <p>Emel ini dihantar secara automatik. Jangan balas emel ini.</p>
            <p>© {{ date('Y') }} iRPM - Kementerian Pendidikan Malaysia</p>
        </div>
    </div>
</body>
</html>
