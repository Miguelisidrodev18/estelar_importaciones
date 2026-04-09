<?php

namespace App\Http\Requests;

use App\Models\Venta;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Venta $venta */
        $venta = $this->route('venta');

        if (!$venta) return false;

        // Solo Administrador y Tienda pueden editar
        if (!in_array(auth()->user()->role->nombre, ['Administrador', 'Tienda'])) {
            return false;
        }

        // No editar si está anulado
        if ($venta->estado_pago === 'anulado') {
            return false;
        }

        // Ventana de tiempo configurable
        $ventanaMaxima = config('ventas.edit_window_hours', 24);
        if ($venta->created_at->diffInHours(now()) > $ventanaMaxima) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'observaciones' => 'nullable|string|max:500',
            'metodo_pago'   => 'nullable|in:efectivo,transferencia,yape,plin,mixto',
            'fecha'         => 'nullable|date|before_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
        ];
    }
}
