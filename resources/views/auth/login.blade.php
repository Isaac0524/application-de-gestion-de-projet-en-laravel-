<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-form {
            background: white;
            padding: 40px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #1e3a8a;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #374151;
        }
        .password-container {
            position: relative;
        }
        input {
            width: 90%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding-right: 30px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
        }
        button[type="submit"] {
            background-color: #1e3a8a;
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #1e40af;
        }
        .link {
            margin-top: 15px;
            text-align: center;
        }
        .link a {
            color: #06b6d4;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <form method="POST" action="{{ route('login') }}" class="login-form">
        @csrf

        <h2>Se connecter</h2>

        @if (session('status'))
            <p style="color: green; text-align: center; margin-bottom: 15px;">
                {{ session('status') }}
            </p>
        @endif

        @if ($errors->any())
            <div style="color: red; font-size: 0.9rem; margin-bottom: 15px;">
                <ul style="padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-group">
            <label for="email">Adresse email</label>
            <input type="email" name="email" id="email" required value="{{ old('email') }}" autofocus>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <div class="password-container"> <input type="password" name="password" id="password" required>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i> </div>
        </div>

        <button type="submit">Connexion</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');

            togglePassword.addEventListener('click', function () {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);

                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>
