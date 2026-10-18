@php
    $linkBase = 'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition';
    $clase = fn ($activo) => $linkBase . ' ' . ($activo ? 'bg-green-700 text-white font-semibold' : 'text-green-100 hover:bg-green-800 hover:text-white');
    $usuario = Auth::user();
@endphp

<!-- Fondo oscuro al abrir el menú en móvil -->
<div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
     class="fixed inset-0 z-30 bg-black/50 lg:hidden" style="display: none;"></div>

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-40 w-64 bg-green-900 text-white flex flex-col transform transition-transform duration-200 -translate-x-full lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:shrink-0">

    <!-- Logo -->
    <div class="flex items-center justify-between h-16 px-5 border-b border-green-800">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <x-application-logo class="h-8 w-auto fill-current text-green-300" />
            <span class="font-bold leading-tight">Disfraces<br><span class="text-green-300 text-sm font-semibold">Elizabeth</span></span>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded hover:bg-green-800" aria-label="Cerrar menú">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Menú -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        <div>
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-green-400">General</p>
            <a href="{{ route('dashboard') }}" class="{{ $clase(request()->routeIs('dashboard')) }}">
                <span>🏠</span> Inicio
            </a>
        </div>

        @role('administrador')
        <div>
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-green-400">Administración</p>
            <div class="space-y-1">
                <a href="{{ route('usuarios.index') }}" class="{{ $clase(request()->routeIs('usuarios.*')) }}">
                    <span>👤</span> Usuarios
                </a>
                <a href="{{ route('roles-permisos.index') }}" class="{{ $clase(request()->routeIs('roles-permisos.*')) }}">
                    <span>🔐</span> Roles y Permisos
                </a>
            </div>
        </div>
        @endrole

        @canany(['categorias.ver', 'trajes.ver'])
        <div>
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-green-400">Parámetros</p>
            <div class="space-y-1">
                @can('categorias.ver')
                <a href="{{ route('categorias.index') }}" class="{{ $clase(request()->routeIs('categorias.*')) }}">
                    <span>🏷️</span> Categorías
                </a>
                @endcan
                @can('trajes.ver')
                <a href="{{ route('trajes.index') }}" class="{{ $clase(request()->routeIs('trajes.*')) }}">
                    <span>👗</span> Trajes
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        @canany(['clientes.ver', 'alquileres.ver'])
        <div>
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-green-400">Alquileres</p>
            <div class="space-y-1">
                @can('clientes.ver')
                <a href="{{ route('clientes.index') }}" class="{{ $clase(request()->routeIs('clientes.*')) }}">
                    <span>👥</span> Clientes
                </a>
                @endcan
                @can('alquileres.ver')
                <a href="{{ route('alquileres.index') }}" class="{{ $clase(request()->routeIs('alquileres.*') || request()->routeIs('devoluciones.*')) }}">
                    <span>📋</span> Alquileres
                </a>
                @endcan
            </div>
        </div>
        @endcanany

        @can('reportes.ver')
        <div>
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-green-400">Reportes</p>
            <div class="space-y-1">
                <a href="{{ route('reportes.alquileres-periodo') }}" class="{{ $clase(request()->routeIs('reportes.alquileres-periodo')) }}">
                    <span>📅</span> Por período
                </a>
                <a href="{{ route('reportes.ingresos-mensuales') }}" class="{{ $clase(request()->routeIs('reportes.ingresos-mensuales')) }}">
                    <span>💰</span> Ingresos mensuales
                </a>
                <a href="{{ route('reportes.vencidos') }}" class="{{ $clase(request()->routeIs('reportes.vencidos')) }}">
                    <span>⏰</span> Vencidos
                </a>
            </div>
        </div>
        @endcan
    </nav>

    <!-- Usuario logueado -->
    <div x-data="{ menuUsuario: false }" class="relative border-t border-green-800 p-3">
        <button @click="menuUsuario = !menuUsuario" class="w-full flex items-center gap-3 rounded-md p-2 hover:bg-green-800 text-left">
            <div class="h-9 w-9 shrink-0 rounded-full bg-green-600 flex items-center justify-center font-bold">
                {{ mb_strtoupper(mb_substr($usuario->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-semibold truncate">{{ $usuario->name }}</div>
                <div class="text-xs text-green-300 capitalize">{{ $usuario->getRoleNames()->first() ?? 'Sin rol asignado' }}</div>
            </div>
            <svg class="h-4 w-4 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="menuUsuario" @click.outside="menuUsuario = false" x-transition
             class="absolute bottom-full left-3 right-3 mb-2 bg-white text-gray-700 rounded-md shadow-lg py-1 text-sm" style="display: none;">
            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-green-50">Mi perfil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-green-50 text-red-600">Cerrar sesión</button>
            </form>
        </div>
    </div>
</aside>
