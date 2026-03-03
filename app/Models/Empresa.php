<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'ruc', 'razon_social', 'nombre_comercial', 'direccion',
        'ubigeo', 'departamento', 'provincia', 'distrito', 'regimen',
        'telefono', 'email', 'web',
        'facebook', 'instagram', 'tiktok',
        'logo_path', 'logo_pdf_path',
        'sunat_usuario_sol', 'sunat_clave_sol', 'sunat_token', 'sunat_modo',
        'api_url', 'api_key',
    ];

    protected $hidden = ['sunat_clave_sol', 'sunat_token', 'api_key'];

    /**
     * Obtener el único registro (singleton).
     */
    public static function instancia(): ?self
    {
        return self::first();
    }

    /**
     * Nombre para mostrar (comercial o razón social).
     */
    public function getNombreDisplayAttribute(): string
    {
        return $this->nombre_comercial ?: $this->razon_social;
    }

    /**
     * URL pública del logo principal.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    /**
     * URL pública del logo PDF.
     */
    public function getLogoPdfUrlAttribute(): ?string
    {
        return $this->logo_pdf_path ? asset('storage/' . $this->logo_pdf_path) : null;
    }
}
