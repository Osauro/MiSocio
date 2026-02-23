<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LicoPOS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <style>
            body {
                font-family: 'Figtree', sans-serif;
                margin: 0;
                padding: 0;
                overflow-x: hidden;
            }

            .auth-container {
                min-height: 100vh;
                display: flex;
            }

            .auth-logo-section {
                flex: 2.8;
                background: #1a202c;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }

            .auth-logo-section img {
                max-width: 550px;
                width: 100%;
                height: auto;
                filter: drop-shadow(0 10px 30px rgba(0,0,0,0.3));
            }

            .auth-form-section {
                flex: 1;
                background: white;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }

            .auth-form-container {
                width: 100%;
                max-width: 450px;
            }

            .auth-title {
                font-size: 3rem;
                font-weight: 600;
                color: #2d3748;
                margin-bottom: 3rem;
                text-align: left;
            }

            .form-label {
                font-weight: 500;
                color: #4a5568;
                margin-bottom: 0.75rem;
                font-size: 1rem;
            }

            .form-control {
                border: 1px solid #e2e8f0;
                border-radius: 0.5rem;
                padding: 1rem 1.25rem;
                font-size: 1.05rem;
                transition: all 0.3s ease;
                height: 52px;
            }

            .form-control:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }

            .btn-primary-custom {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                border-radius: 0.5rem;
                padding: 1rem 2rem;
                font-size: 1.05rem;
                font-weight: 600;
                color: white;
                width: 100%;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
                cursor: pointer;
                height: 52px;
            }

            .btn-primary-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            }

            .form-check-input:checked {
                background-color: #667eea;
                border-color: #667eea;
            }

            .auth-link {
                color: #667eea;
                text-decoration: none;
                font-weight: 500;
            }

            .auth-link:hover {
                color: #764ba2;
                text-decoration: underline;
            }

            .alert {
                border-radius: 0.5rem;
                padding: 1rem;
                margin-bottom: 1.5rem;
            }

            @media (max-width: 768px) {
                .auth-container {
                    flex-direction: column;
                }

                .auth-logo-section {
                    display: none;
                }

                .auth-form-section {
                    padding: 2rem 1.5rem;
                }

                .auth-title {
                    font-size: 2rem;
                    text-align: center;
                    margin-bottom: 2rem;
                }

                .auth-form-container {
                    max-width: 100%;
                }
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="auth-container">
            <div class="auth-logo-section">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" />
            </div>

            <div class="auth-form-section">
                <div class="auth-form-container">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
