<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('negocio.nombre') }}</title>
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        <div class="min-h-screen flex flex-col">

            <!-- Barra superior -->
            <header class="bg-white border-b">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                    <a href="{{ url('/') }}" class="flex items-center gap-2 font-semibold text-green-900">
                        <x-application-logo class="h-8 w-auto" />
                        {{ config('negocio.nombre') }}
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">Ir al panel</a>
                    @else
                        <a href="{{ route('login') }}" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">Iniciar sesión</a>
                    @endauth
                </div>
            </header>

            <!-- Presentación -->
            <main class="flex-1 flex items-center">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 w-full">
                    <div class="text-center">
                        <x-application-logo class="h-28 w-auto mx-auto" />
                        <h1 class="mt-4 text-3xl sm:text-4xl font-bold text-green-900">{{ config('negocio.nombre') }}</h1>
                        <p class="mt-2 text-lg text-gray-600">{{ config('negocio.eslogan') }}</p>

                        <div class="mt-6">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-block bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded-lg font-semibold shadow">Ir al panel</a>
                            @else
                                <a href="{{ route('login') }}" class="inline-block bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded-lg font-semibold shadow">Ingresar al sistema</a>
                            @endauth
                        </div>

                        <p class="mt-4 text-sm text-gray-500">{{ config('negocio.direccion') }} · {{ config('negocio.ciudad') }}</p>
                    </div>

                    <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white rounded-xl shadow-sm p-5 border-t-4 border-green-600">
                            <p class="font-semibold text-gray-800">Alquileres y cursos</p>
                            <p class="text-sm text-gray-500 mt-1">Individuales o por grupo, con garantías por persona.</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-5 border-t-4 border-green-600">
                            <p class="font-semibold text-gray-800">Devoluciones</p>
                            <p class="text-sm text-gray-500 mt-1">Por partes, con control de piezas y estado del traje.</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-5 border-t-4 border-green-600">
                            <p class="font-semibold text-gray-800">Inventario</p>
                            <p class="text-sm text-gray-500 mt-1">Stock, reparaciones y bajas de cada traje.</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-5 border-t-4 border-green-600">
                            <p class="font-semibold text-gray-800">Reportes</p>
                            <p class="text-sm text-gray-500 mt-1">Ingresos, vencidos, bitácora y copias de seguridad.</p>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="text-center text-xs text-gray-400 py-4">
                © {{ date('Y') }} {{ config('negocio.nombre') }}
            </footer>
        </div>
    </body>
</html>
