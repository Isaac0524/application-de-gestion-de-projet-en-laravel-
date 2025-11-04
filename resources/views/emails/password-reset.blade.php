<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .password-box {
            background-color: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 16px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h2>Bonjour {{ $user->name }},</h2>

    <p>Votre mot de passe a été réinitialisé par un administrateur.</p>

    <p><strong>Votre nouveau mot de passe est :</strong></p>
    <div class="password-box">{{ $newPassword }}</div>

    <p>Nous vous recommandons de changer ce mot de passe dès votre première connexion.</p>

    <p>Cordialement,<br>
    L'équipe de gestion</p>
</body>
</html>
