<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Infoma - Informasi Kebutuhan Mahasiswa')</title>

    <!-- Preload Font Awesome untuk performance yang lebih baik -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

    <!-- Font Awesome - Multiple CDN with fallback -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>

    <!-- Primary Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#1E40AF',
                    }
                }
            }
        }
    </script>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/Infoma_Branding.png') }}">

    <!-- Critical CSS untuk Font Awesome stability -->
    <style>
        /* Ensure Font Awesome icons are always stable and visible */
        .fas, .fa, .fab, .fal, .far {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 5 Free", "Font Awesome 5 Pro", FontAwesome !important;
            font-weight: 900 !important;
            font-style: normal !important;
            display: inline-block !important;
            text-rendering: auto !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            visibility: visible !important;
            opacity: 1 !important;
            line-height: 1 !important;
        }

        .fab {
            font-weight: 400 !important;
        }

        .far {
            font-weight: 400 !important;
        }

        /* Prevent icon flickering and ensure stability */
        .icon-container {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            position: relative;
        }

        .icon-container i {
            position: relative;
            z-index: 1;
        }

        /* Removed body loading visibility toggles to prevent icons from disappearing */

        /* Fallback styling for emoji */
        .fallback-icon {
            font-family: inherit !important;
            font-size: 1em !important;
            line-height: 1 !important;
        }
    </style>

    <!-- Font Awesome Loading Management -->
    <script>
        // Font Awesome Management System
        window.FontAwesomeManager = {
            isLoaded: false,
            retryCount: 0,
            maxRetries: 3,
            fallbackCDNs: [
                'https://use.fontawesome.com/releases/v6.4.0/css/all.css',
                'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css',
                'https://pro.fontawesome.com/releases/v6.4.0/css/all.css'
            ],

            // Check if Font Awesome is properly loaded
            checkLoaded: function() {
                const testElement = document.createElement('i');
                testElement.className = 'fas fa-home';
                testElement.style.position = 'absolute';
                testElement.style.left = '-9999px';
                testElement.style.visibility = 'hidden';
                document.body.appendChild(testElement);

                const computed = window.getComputedStyle(testElement);
                const fontFamily = computed.getPropertyValue('font-family');

                document.body.removeChild(testElement);

                this.isLoaded = fontFamily.toLowerCase().includes('font awesome');
                return this.isLoaded;
            },

            // Load fallback CDN
            loadFallback: function() {
                if (this.retryCount >= this.maxRetries) {
                    console.warn('Font Awesome loading failed after all retries, using fallback icons');
                    this.showFallbacks();
                    return;
                }

                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = this.fallbackCDNs[this.retryCount];
                link.onload = () => {
                    setTimeout(() => {
                        if (this.checkLoaded()) {
                            console.log('Font Awesome loaded from fallback CDN');
                            this.init();
                        } else {
                            this.retryCount++;
                            this.loadFallback();
                        }
                    }, 100);
                };
                link.onerror = () => {
                    this.retryCount++;
                    this.loadFallback();
                };

                document.head.appendChild(link);
            },

            // Show emoji fallbacks
            showFallbacks: function() {
                const icons = document.querySelectorAll('i[data-fallback]');
                icons.forEach(icon => {
                    const fallback = icon.getAttribute('data-fallback');
                    if (fallback) {
                        icon.textContent = fallback;
                        icon.className = 'fallback-icon';
                    }
                });
            },

            // Initialize icon system
            init: function() {
                document.body.classList.remove('loading');
                document.body.classList.add('loaded');

                if (!this.checkLoaded()) {
                    console.warn('Font Awesome not detected, trying fallback CDNs');
                    this.loadFallback();
                } else {
                    console.log('Font Awesome loaded successfully');

                    // Ensure all icons are visible
                    const icons = document.querySelectorAll('.fas, .fa, .fab, .fal, .far');
                    icons.forEach(icon => {
                        icon.style.visibility = 'visible';
                        icon.style.opacity = '1';
                    });
                }
            }
        };

        // Removed initial 'loading' state to avoid unintended icon hiding
    </script>

    <!-- Additional CSS -->
    @stack('styles')
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    @include('components.navbar')

    <!-- Main Content -->
    <main class="min-h-screen">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="flash-alert bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mx-4 mt-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0 icon-container">
                        <i class="fas fa-check-circle text-green-500" data-fallback="✅"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="flash-alert bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mx-4 mt-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0 icon-container">
                        <i class="fas fa-exclamation-circle text-red-500" data-fallback="❌"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="flash-alert bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mx-4 mt-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0 icon-container">
                        <i class="fas fa-exclamation-triangle text-yellow-500" data-fallback="⚠️"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="flash-alert bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mx-4 mt-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0 icon-container">
                        <i class="fas fa-info-circle text-blue-500" data-fallback="ℹ️"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('info') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
                        <img src="{{ asset('images/Infoma_Branding.png') }}" alt="Infoma Logo" class="w-6 h-6">
                        Infoma
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md">Platform terpercaya untuk mahasiswa dalam mencari tempat
                        tinggal dan kegiatan kampus. Memudahkan kehidupan mahasiswa dengan teknologi modern.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors icon-container">
                            <i class="fab fa-facebook text-xl" data-fallback="📘"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors icon-container">
                            <i class="fab fa-twitter text-xl" data-fallback="🐦"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors icon-container">
                            <i class="fab fa-instagram text-xl" data-fallback="📸"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors icon-container">
                            <i class="fab fa-linkedin text-xl" data-fallback="💼"></i>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-6">Layanan</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('residences.index') }}" class="text-gray-400 hover:text-white transition-colors">Residence</a></li>
                        <li><a href="{{ route('activities.index') }}" class="text-gray-400 hover:text-white transition-colors">Kegiatan Kampus</a></li>
                        <li><a href="{{ route('marketplace.index') }}" class="text-gray-400 hover:text-white transition-colors">Marketplace</a></li>
                        <li><a href="{{ route('search') }}" class="text-gray-400 hover:text-white transition-colors">Cari</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Customer Support</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-6">Kontak</h3>
                    <ul class="space-y-3">
                        <li class="text-gray-400 flex items-center">
                            <i class="fas fa-envelope mr-2 icon-container" data-fallback="📧"></i>info@infoma.com
                        </li>
                        <li class="text-gray-400 flex items-center">
                            <i class="fas fa-phone mr-2 icon-container" data-fallback="📞"></i>+62 123 456 7890
                        </li>
                        <li class="text-gray-400 flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 icon-container" data-fallback="📍"></i>Jakarta, Indonesia
                        </li>
                        <li class="text-gray-400 flex items-center">
                            <i class="fas fa-clock mr-2 icon-container" data-fallback="⏰"></i>24/7 Support
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400">&copy; {{ date('Y') }} Infoma. All rights reserved. Made with ❤️ for Indonesian students.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Initialize Font Awesome when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            FontAwesomeManager.init();
        });

        // Also initialize on window load as backup
        window.addEventListener('load', function() {
            setTimeout(() => {
                FontAwesomeManager.init();
            }, 500);
        });

        // Auto hide flash messages (target only flash alerts)
        setTimeout(function() {
            const alerts = document.querySelectorAll('.flash-alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Add scroll effect to navbar
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (navbar) {
                if (window.scrollY > 100) {
                    navbar.classList.add('bg-white/95', 'backdrop-blur-sm');
                } else {
                    navbar.classList.remove('bg-white/95', 'backdrop-blur-sm');
                }
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Monitor for dynamically added content
        if (window.MutationObserver) {
            const observer = new MutationObserver(function(mutations) {
                let needsReinit = false;
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                const icons = node.querySelectorAll ? node.querySelectorAll('i[data-fallback]') : [];
                                if (icons.length > 0) {
                                    needsReinit = true;
                                }
                            }
                        });
                    }
                });

                if (needsReinit) {
                    setTimeout(() => FontAwesomeManager.init(), 100);
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
