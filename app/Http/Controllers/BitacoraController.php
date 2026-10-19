<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bitacora;
use App\Models\User;

class BitacoraController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:bitacora.ver');
    }

    public function index(Request $request)
    {
        $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'user_id' => 'nullable|exists:users,id',
        ], [
            'hasta.after_or_equal' => 'La fecha "hasta" debe ser igual o posterior a la fecha "desde".',
        ]);

        $bitacoras = Bitacora::with('user')
            ->when($request->desde, fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->hasta, fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $usuarios = User::orderBy('name')->get();

        return view('bitacoras.index', compact('bitacoras', 'usuarios'));
    }
}
