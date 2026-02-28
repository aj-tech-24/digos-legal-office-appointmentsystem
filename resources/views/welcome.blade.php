<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Digos City Legal Office</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-bg-gray">
    <!-- Navbar -->
    <nav class="glass-dark text-white shadow-3d sticky top-0 z-50 transition-all-smooth">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-4 animate-slide-down">
                    <img src="{{ asset('images/digos-logo.png') }}" class="h-12 w-12" alt="Logo" onerror="this.style.display='none'">
                    <div>
                        <h1 class="text-xl font-bold leading-tight">Digos City Legal Office</h1>
                        <p class="text-xs text-gray-300 tracking-wide">JUSTICE • INTEGRITY • SERVICE</p>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-8 animate-slide-down" style="animation-delay: 0.1s;">
                    <a href="#" class="text-gray-200 hover:text-white transition-all-smooth hover:scale-105">Home</a>
                    <a href="#services" class="text-gray-200 hover:text-white transition-all-smooth hover:scale-105">Services</a>
                    <a href="#process" class="text-gray-200 hover:text-white transition-all-smooth hover:scale-105">How it Works</a>
                    <a href="{{ route('login') }}" class="text-gov-yellow hover:text-yellow-400 font-medium transition-all-smooth hover:scale-105">Login</a>
                </div>
                <div class="md:hidden">
                    <!-- Mobile menu button can go here -->
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative gradient-navy-blue overflow-hidden">
        <div class="absolute inset-0 gradient-mesh opacity-50"></div>
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1589829085413-56de8ae18c73?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" 
                 class="w-full h-full object-cover opacity-10" alt="Legal Background">
            <div class="absolute inset-0 bg-gradient-to-r from-gov-navy via-[#012357]/90 to-transparent"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
            <div class="md:w-2/3 lg:w-1/2">
                <span class="inline-block py-1 px-3 rounded-full glass text-blue-200 text-sm font-medium mb-6 animate-fade-in">
                    🏛️ Official Appointment System
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6 animate-slide-up">
                    <span class="text-gov-yellow">Serving the People</span> Convenient Legal Booking for Digoseños
                </h1>
                <p class="text-lg text-gray-300 mb-8 leading-relaxed max-w-xl animate-slide-up" style="animation-delay: 0.1s;">
                    Schedule your legal consultations online with the City Legal Office of Digos. 
                    Book appointments, choose services, and get timely assistance from our legal team, all in one platform.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 animate-slide-up" style="animation-delay: 0.2s;">
                    <a href="{{ route('book.index') }}" 
                       class="inline-flex justify-center items-center px-8 py-4 border border-transparent text-base font-bold rounded-lg text-white gradient-orange-yellow hover:opacity-90 md:text-lg transition-all-smooth shadow-glow-orange hover-lift animate-pulse-glow">
                        Book an Appointment
                        <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                    <a href="#services" 
                       class="inline-flex justify-center items-center px-8 py-4 border-2 glass text-base font-medium rounded-lg text-white hover:bg-white/10 md:text-lg transition-all-smooth hover-lift">
                        View Services
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Features/Services -->
    <div id="services" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in">
                <h2 class="text-3xl font-bold text-gov-navy sm:text-4xl">Our Legal Services</h2>
                <div class="w-24 h-1 gradient-orange-yellow mx-auto mt-4 rounded-full"></div>
                <p class="mt-4 text-xl text-gray-600">Free legal assistance for residents of Digos City</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Service 1 -->
                <div class="group glass-white rounded-xl shadow-md border border-gray-100 hover:border-gov-navy hover-lift transition-all-smooth overflow-hidden animate-scale-in">
                    <div class="p-8">
                        <div class="w-14 h-14 gradient-navy-blue rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-all-smooth shadow-glow-blue">
                            <svg class="w-8 h-8 text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-gov-navy transition-colors">Legal Consultation</h3>
                        <p class="text-gray-600">Expert advice on various legal matters including civil, criminal, and administrative cases.</p>
                    </div>
                </div>

                <!-- Service 2 -->
                <div class="group glass-white rounded-xl shadow-md border border-gray-100 hover:border-gov-navy hover-lift transition-all-smooth overflow-hidden animate-scale-in" style="animation-delay: 0.1s;">
                    <div class="p-8">
                        <div class="w-14 h-14 gradient-navy-blue rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-all-smooth shadow-glow-blue">
                            <svg class="w-8 h-8 text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-gov-navy transition-colors">Notarial Services</h3>
                        <p class="text-gray-600">Authentication of affidavits, sworn statements, Special Power of Attorney (SPA), and other legal instruments.</p>
                    </div>
                </div>

                <!-- Service 3 -->
                <div class="group glass-white rounded-xl shadow-md border border-gray-100 hover:border-gov-navy hover-lift transition-all-smooth overflow-hidden animate-scale-in" style="animation-delay: 0.2s;">
                    <div class="p-8">
                        <div class="w-14 h-14 gradient-navy-blue rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-all-smooth shadow-glow-blue">
                            <svg class="w-8 h-8 text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-gov-navy transition-colors">Preparation of Affidavits</h3>
                        <p class="text-gray-600">Assistance in drafting sworn statements for employment, scholarship, and other official purposes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Section -->
    <div id="process" class="py-20 bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in">
                <h2 class="text-3xl font-bold text-gov-navy sm:text-4xl">How It Works</h2>
                <div class="w-24 h-1 gradient-orange-yellow mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="relative text-center animate-scale-in">
                    <div class="w-16 h-16 bg-white border-2 border-gov-navy rounded-full flex items-center justify-center text-2xl font-bold text-gov-navy mx-auto mb-4 relative z-10 shadow-md transition-all-smooth hover:scale-110">1</div>
                    <div class="hidden md:block absolute top-8 left-1/2 w-full h-0.5 bg-gray-300 -z-0"></div>
                    <h3 class="text-lg font-bold mb-2">Fill Details</h3>
                    <p class="text-sm text-gray-600">Provide your personal information and case details.</p>
                </div>

                <!-- Step 2 -->
                <div class="relative text-center animate-scale-in" style="animation-delay: 0.1s;">
                    <div class="w-16 h-16 bg-white border-2 border-gov-navy rounded-full flex items-center justify-center text-2xl font-bold text-gov-navy mx-auto mb-4 relative z-10 shadow-md transition-all-smooth hover:scale-110">2</div>
                    <div class="hidden md:block absolute top-8 left-1/2 w-full h-0.5 bg-gray-300 -z-0"></div>
                    <h3 class="text-lg font-bold mb-2">Upload ID</h3>
                    <p class="text-sm text-gray-600">Submit a valid government ID for verification.</p>
                </div>

                <!-- Step 3 -->
                <div class="relative text-center animate-scale-in" style="animation-delay: 0.2s;">
                    <div class="w-16 h-16 bg-white border-2 border-gov-navy rounded-full flex items-center justify-center text-2xl font-bold text-gov-navy mx-auto mb-4 relative z-10 shadow-md transition-all-smooth hover:scale-110">3</div>
                    <div class="hidden md:block absolute top-8 left-1/2 w-full h-0.5 bg-gray-300 -z-0"></div>
                    <h3 class="text-lg font-bold mb-2">Get Matched</h3>
                    <p class="text-sm text-gray-600">AI matches you with the best lawyer for your case.</p>
                </div>

                <!-- Step 4 -->
                <div class="relative text-center animate-scale-in" style="animation-delay: 0.3s;">
                    <div class="w-16 h-16 gradient-orange-yellow text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 relative z-10 shadow-glow-orange transition-all-smooth hover:scale-110">4</div>
                    <h3 class="text-lg font-bold mb-2">Confirmation</h3>
                    <p class="text-sm text-gray-600">Receive appointment schedule and confirmation.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="gradient-navy-blue py-16 relative overflow-hidden">
        <div class="absolute inset-0 gradient-mesh opacity-30"></div>
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10 animate-fade-in">
            <h2 class="text-3xl font-bold text-white mb-6">Ready to schedule your consultation?</h2>
            <p class="text-blue-100 text-lg mb-8">Secure your slot today. Walk-ins are subject to availability.</p>
            <a href="{{ route('book.index') }}" class="inline-block px-8 py-4 gradient-orange-yellow text-white font-bold rounded-lg hover:opacity-90 transition-all-smooth shadow-glow-orange hover-lift animate-pulse-glow">
                Start Booking Now
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-[#011c45] text-white py-12 border-t border-blue-900 relative">
        <div class="absolute inset-0 gradient-mesh opacity-20"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div class="animate-fade-in">
                    <h3 class="text-lg font-bold mb-4 bg-clip-text text-white">Contact Us</h3>
                    <p class="text-gray-300 mb-2">📍 Jose Abad Santos St, Digos City</p>
                    <p class="text-gray-300 mb-2">📞 (082) 553-8396</p>
                    <p class="text-gray-300">📧 legal@digos.gov.ph</p>
                </div>
                <div class="animate-fade-in" style="animation-delay: 0.1s;">
                    <h3 class="text-lg font-bold mb-4 bg-clip-text text-white">Office Hours</h3>
                    <p class="text-gray-300 mb-2">Monday - Friday</p>
                    <p class="text-gray-300">8:00 AM - 5:00 PM</p>
                </div>
                <div class="animate-fade-in" style="animation-delay: 0.2s;">
                    <h3 class="text-lg font-bold mb-4 bg-clip-text text-white">Legal</h3>
                    <a href="#" class="block text-gray-300 hover:text-white mb-2 transition-all-smooth">Privacy Policy</a>
                    <a href="#" class="block text-gray-300 hover:text-white transition-all-smooth">Terms of Service</a>
                </div>
            </div>
            <div class="border-t border-blue-900 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} Digos City Legal Office. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>