{{-- Logo de la tienda: antifaz de diablada. variante="claro" para fondos oscuros (menú lateral) --}}
@props(['variante' => 'color'])

@php
    $claro = $variante === 'claro';
    $antifaz = $claro ? '#86efac' : '#15803d';
    $cuernos = $claro ? '#fca5a5' : '#b91c1c';
    $ojos = $claro ? '#14532d' : '#ffffff';
@endphp

<svg viewBox="0 0 120 70" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{{ config('negocio.nombre') }}" {{ $attributes }}>
    <path d="M26 24 C22 14 24 4 32 0 C30 8 33 15 40 19 Z" fill="{{ $cuernos }}"/>
    <path d="M94 24 C98 14 96 4 88 0 C90 8 87 15 80 19 Z" fill="{{ $cuernos }}"/>
    <path d="M8 32 C8 20 30 18 46 26 C52 29 56 32 60 32 C64 32 68 29 74 26 C90 18 112 20 112 32 C112 52 96 66 80 64 C70 63 66 54 60 54 C54 54 50 63 40 64 C24 66 8 52 8 32 Z" fill="{{ $antifaz }}"/>
    <ellipse cx="38" cy="40" rx="13" ry="8" fill="{{ $ojos }}"/>
    <ellipse cx="82" cy="40" rx="13" ry="8" fill="{{ $ojos }}"/>
    <circle cx="60" cy="28" r="4" fill="#d4a017"/>
</svg>
