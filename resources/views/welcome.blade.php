<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Vehicle Management API</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background-color: #f8fafc;
                color: #1a202c;
                margin: 0;
                padding: 0;
                line-height: 1.6;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
            }

            .header {
                text-align: center;
                margin-bottom: 3rem;
                padding-top: 2rem;
            }

            .logo {
                margin-bottom: 1rem;
                display: flex;
                justify-content: center;
            }

            .logo svg {
                height: 50px;
                width: auto;
            }

            h1 {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
                color: #2d3748;
            }

            .subtitle {
                font-size: 1.25rem;
                color: #4a5568;
                margin-bottom: 2rem;
            }

            .features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 2rem;
                margin-bottom: 3rem;
            }

            .feature-card {
                background-color: white;
                border-radius: 0.5rem;
                padding: 1.5rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .feature-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            }

            .feature-icon {
                background-color: #f0eff1;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 1rem;
            }

            .feature-title {
                font-size: 1.25rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
                color: #2d3748;
            }

            .feature-description {
                color: #4a5568;
            }

            .cta {
                text-align: center;
                margin-top: 2rem;
                margin-bottom: 3rem;
            }

            .button {
                display: inline-block;
                background-color: #18181a;
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 0.375rem;
                font-weight: 500;
                text-decoration: none;
                transition: background-color 0.3s ease;
            }

            .button:hover {
                background-color: #4338ca;
            }

            .api-section {
                background-color: white;
                border-radius: 0.5rem;
                padding: 2rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                margin-bottom: 3rem;
            }

            .api-title {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 1rem;
                color: #2d3748;
            }

            .endpoint {
                background-color: #fbfcfe;
                border-radius: 0.375rem;
                padding: 1rem;
                margin-bottom: 1rem;
                border-left: 4px solid #18181a;
            }

            .endpoint-method {
                display: inline-block;
                padding: 0.25rem 0.5rem;
                background-color: #18181a;
                color: white;
                border-radius: 0.25rem;
                font-size: 0.875rem;
                font-weight: 500;
                margin-right: 0.5rem;
            }

            .endpoint-url {
                font-family: monospace;
                font-size: 0.875rem;
            }

            .footer {
                text-align: center;
                padding: 2rem 0;
                color: #4a5568;
                font-size: 0.875rem;
            }

            @media (max-width: 768px) {
                .container {
                    padding: 1rem;
                }

                h1 {
                    font-size: 2rem;
                }

                .subtitle {
                    font-size: 1rem;
                }
            }

            .dark-mode-toggle {
                position: absolute;
                top: 1rem;
                right: 1rem;
                background: none;
                border: none;
                cursor: pointer;
                padding: 0.5rem;
            }

            /* Dark mode styles */
            .dark {
                background-color: #1a202c;
                color: #e2e8f0;
            }

            .dark h1, .dark .feature-title, .dark .api-title {
                color: #fbfcfe;
            }

            .dark .subtitle, .dark .feature-description {
                color: #cbd5e0;
            }

            .dark .feature-card, .dark .api-section {
                background-color: #2d3748;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            }

            .dark .feature-icon {
                background-color: #2a4365;
            }

            .dark .endpoint {
                background-color: #2d3748;
                border-left: 4px solid #5a67d8;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <button class="dark-mode-toggle" onclick="toggleDarkMode()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sun-icon"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="moon-icon" style="display: none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>

            <header class="header">
                <div class="logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-car-front"><path d="m21 8-2 2-1.5-3.7A2 2 0 0 0 15.646 5H8.4a2 2 0 0 0-1.903 1.257L5 10 3 8"/><path d="M7 14h.01"/><path d="M17 14h.01"/><rect width="18" height="8" x="3" y="10" rx="2"/><path d="M5 18v2"/><path d="M19 18v2"/></svg>
                </div>
                <h1>Vehicle Management API</h1>
                <p class="subtitle">A powerful API for managing your vehicle fleet</p>
            </header>

            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#18181a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    </div>
                    <h3 class="feature-title">User Management</h3>
                    <p class="feature-description">Create and manage user accounts with secure authentication using Laravel Sanctum.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#18181a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                    </div>
                    <h3 class="feature-title">Vehicle Registration</h3>
                    <p class="feature-description">Register and track vehicles with details like make, model, year, and license plate information.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#18181a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <h3 class="feature-title">Admin Approval</h3>
                    <p class="feature-description">Secure approval workflow for vehicle registration and updates by administrators.</p>
                </div>
            </div>

            <div class="api-section">
                <h2 class="api-title">API Endpoints</h2>

                <div class="endpoint">
                    <span class="endpoint-method">POST</span>
                    <span class="endpoint-url">/api/v1/login</span>
                </div>

                <div class="endpoint">
                    <span class="endpoint-method">GET</span>
                    <span class="endpoint-url">/api/v1/vehicles</span>
                </div>

                <div class="endpoint">
                    <span class="endpoint-method">POST</span>
                    <span class="endpoint-url">/api/v1/vehicles</span>
                </div>

                <div class="endpoint">
                    <span class="endpoint-method">PUT</span>
                    <span class="endpoint-url">/api/v1/vehicles/{id}</span>
                </div>

                <div class="endpoint">
                    <span class="endpoint-method">POST</span>
                    <span class="endpoint-url">/api/v1/vehicles/{id}/approve</span>
                </div>
            </div>

            <footer class="footer">
                <p>&copy; {{ date('Y') }} Vehicle Management API. All rights reserved.</p>
            </footer>
        </div>

        <script>
            function toggleDarkMode() {
                document.body.classList.toggle('dark');
                const sunIcon = document.querySelector('.sun-icon');
                const moonIcon = document.querySelector('.moon-icon');

                if (document.body.classList.contains('dark')) {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                } else {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                }

                // Save preference
                localStorage.setItem('darkMode', document.body.classList.contains('dark'));
            }

            // Check for saved preference
            document.addEventListener('DOMContentLoaded', function() {
                const darkMode = localStorage.getItem('darkMode') === 'true';
                if (darkMode) {
                    document.body.classList.add('dark');
                    document.querySelector('.sun-icon').style.display = 'none';
                    document.querySelector('.moon-icon').style.display = 'block';
                }
            });
        </script>
    </body>
</html>
