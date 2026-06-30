<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\TrajeController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\ReporteController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::resource('clientes', ClienteController::class);
    Route::resource('categorias', CategoriaController::class);
    Route::resource('trajes', TrajeController::class);
    Route::get('/alquileres/{id}/recibo', [AlquilerController::class, 'recibo'])->name('alquileres.recibo');
    Route::get('/alquileres/{id}/devolucion', [DevolucionController::class, 'create'])->name('devoluciones.create');
    Route::post('/alquileres/{id}/devolucion', [DevolucionController::class, 'store'])->name('devoluciones.store');
    Route::resource('alquileres', AlquilerController::class);
    
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/alquileres-periodo', [ReporteController::class, 'alquileresPorPeriodo'])->name('reportes.alquileres-periodo');
    Route::get('/reportes/ingresos-mensuales', [ReporteController::class, 'ingresosMensuales'])->name('reportes.ingresos-mensuales');
    Route::get('/reportes/vencidos', [ReporteController::class, 'vencidos'])->name('reportes.vencidos');
});

require __DIR__.'/auth.php';
