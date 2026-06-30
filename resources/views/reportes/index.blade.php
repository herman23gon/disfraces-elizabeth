<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reportes
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <h3 class="font-bold text-lg mb-4">Seleccione un reporte</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('reportes.alquileres-periodo') }}" class="block border rounded p-4 hover:bg-gray-50">
                        <div class="text-2xl mb-2">📋</div>
                        <div class="font-semibold">Alquileres por Período</div>
                        <div class="text-sm text-gray-500">Consultar alquileres entre dos fechas</div>
                    </a>

                    <a href="{{ route('reportes.ingresos-mensuales') }}" class="block border rounded p-4 hover:bg-gray-50">
                        <div class="text-2xl mb-2">💰</div>
                        <div class="font-semibold">Ingresos Mensuales</div>
                        <div class="text-sm text-gray-500">Control de caja: efectivo vs QR, por día</div>
                    </a>

                    <a href="{{ route('reportes.vencidos') }}" class="block border rounded p-4 hover:bg-gray-50">
                        <div class="text-2xl mb-2">⚠️</div>
                        <div class="font-semibold">Alquileres Vencidos</div>
                        <div class="text-sm text-gray-500">Prendas pendientes de devolución</div>
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>