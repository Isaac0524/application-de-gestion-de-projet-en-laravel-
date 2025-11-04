<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 3rem;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .logo {
            font-size: clamp(1.5rem, 4vw, 1.8rem);
            font-weight: bold;
            color: #1e3a8a;
        }
        .nav-buttons {
            display: flex;
            gap: 1rem;
        }
        .nav-btn {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
        }
        .home-btn {
            background: #1e3a8a;
            color: white;
        }
        .signin-btn {
            background: transparent;
            color: #1e3a8a;
            border: 2px solid #1e3a8a;
        }
        .signup-btn {
            background: #ef4444;
            color: white;
        }
        .home-container {
            padding: clamp(6rem, 15vw, 8rem) 1rem 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            min-height: 100vh;
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        .home-left {
            flex: 1;
            min-width: 280px;
            max-width: 600px;
        }
        .home-title {
            font-size: clamp(2rem, 6vw, 4rem);
            color: #1e3a8a;
            font-weight: bold;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        .home-subtitle {
            color: #06b6d4;
        }
        .home-description {
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            color: #374151;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .cta-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .cta-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 25px;
            font-size: clamp(0.9rem, 2.5vw, 1.1rem);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
        }
        .try-now-btn {
            background: #ef4444;
            color: white;
        }
        .get-started-btn {
            background: #06b6d4;
            color: white;
        }
        .home-right {
            flex: 1.5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 280px;
        }
        .illustration {
            width: 100%;
            max-width: 700px;
            height: auto;
        }
        .illustration img {
            width: 100%;
            height: auto;
            object-fit: contain;
            max-height: 80vh;
        }
        /* Responsive Design */
        @media (max-width: 1024px) {
            .home-container {
                flex-direction: column;
                padding: 6rem 1.5rem 2rem;
                text-align: center;
            }
            .home-left, .home-right {
                max-width: 100%;
            }
            .cta-buttons {
                justify-content: center;
            }
        }
        @media (max-width: 768px) {
            .header {
                padding: 1rem 1.5rem;
                flex-wrap: wrap;
                gap: 1rem;
            }
            .nav-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
            .nav-btn {
                padding: 0.5rem 1rem;
            }
            .home-title {
                font-size: clamp(1.8rem, 5vw, 2.5rem);
            }
            .home-subtitle {
                font-size: clamp(1.5rem, 4vw, 2rem);
            }
            .cta-btn {
                width: 100%;
                max-width: 300px;
            }
        }
        @media (max-width: 480px) {
            .header {
                padding: 0.75rem 1rem;
            }
            .logo {
                font-size: clamp(1.2rem, 4vw, 1.5rem);
            }
            .nav-btn {
                font-size: clamp(0.8rem, 2.5vw, 0.9rem);
                padding: 0.5rem 0.75rem;
            }
            .home-container {
                padding: 5rem 1rem 2rem;
            }
            .home-description {
                font-size: clamp(0.9rem, 2.5vw, 1rem);
            }
            .illustration {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">PM-APP</div>
        <nav class="nav-buttons">
            <a href="<?php echo e(route('login')); ?>" class="nav-btn signin-btn">Sign In</a>
        </nav>
    </header>

    <main class="home-container">
        <section class="home-left">
            <h1 class="home-title">Project <span class="home-subtitle">Management</span></h1>
            <p class="home-description">
                Organisez • Planifiez • Réussissez<br><br>
                Transformez votre façon de gérer les projets avec notre plateforme intuitive.
                Collaborez efficacement, suivez les progrès en temps réel et atteignez vos objectifs
                plus rapidement que jamais.
            </p>
            <div class="cta-buttons">
                <a href="<?php echo e(route('login')); ?>" class="cta-btn get-started-btn">Get Started</a>
            </div>
        </section>

        <section class="home-right">
            <div class="illustration">
                <img src="<?php echo e(asset('assets/img/Bg_acceuil.jpeg')); ?>" alt="Project Management Illustration">
            </div>
        </section>
    </main>
</body>
</html>
