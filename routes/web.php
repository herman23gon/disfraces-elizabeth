<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\TrajeController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RolPermisoController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\BackupController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/buscar-recibo', [DashboardController::class, 'buscarRecibo'])->name('dashboard.buscar-recibo');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Los permisos por acción (ver/crear/editar/eliminar) se aplican en cada controlador
    Route::resource('clientes', ClienteController::class);
    Route::resource('categorias', CategoriaController::class);
    Route::resource('trajes', TrajeController::class);
    Route::get('/alquileres/{id}/recibo', [AlquilerController::class, 'recibo'])->middleware('permission:alquileres.ver')->name('alquileres.recibo');
    Route::get('/alquileres/{id}/devolucion', [DevolucionController::class, 'create'])->middleware('permission:alquileres.editar')->name('devoluciones.create');
    Route::post('/alquileres/{id}/devolucion', [DevolucionController::class, 'store'])->middleware('permission:alquileres.editar')->name('devoluciones.store');
    Route::resource('alquileres', AlquilerController::class);
    
    Route::middleware(['permission:reportes.ver'])->group(function () {
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/alquileres-periodo', [ReporteController::class, 'alquileresPorPeriodo'])->name('reportes.alquileres-periodo');
    Route::get('/reportes/ingresos-mensuales', [ReporteController::class, 'ingresosMensuales'])->name('reportes.ingresos-mensuales');
    Route::get('/reportes/vencidos', [ReporteController::class, 'vencidos'])->name('reportes.vencidos');
    });

    Route::middleware(['role:administrador'])->group(function () {
    Route::resource('usuarios', UsuarioController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::patch('usuarios/{usuario}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle-activo');
    Route::get('/roles-permisos', [RolPermisoController::class, 'index'])->name('roles-permisos.index');
    Route::put('/roles-permisos', [RolPermisoController::class, 'update'])->name('roles-permisos.update');
    });

    // Bitácora y Copia de Seguridad: acceso según permisos (bitacora.ver, backup.ver, backup.generar)
    Route::resource('bitacoras', BitacoraController::class)->only(['index']);
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups/generar', [BackupController::class, 'generar'])->name('backups.generar');
    Route::get('/backups/{nombre}/descargar', [BackupController::class, 'descargar'])->name('backups.descargar');
});

require __DIR__.'/auth.php';
