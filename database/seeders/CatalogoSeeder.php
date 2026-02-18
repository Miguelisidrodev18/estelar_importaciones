<?php
// database/seeders/CatalogoSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalogo\MotivoMovimiento;
use App\Models\Catalogo\Color;
use App\Models\Catalogo\UnidadMedida;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\Modelo;

class CatalogoSeeder extends Seeder
{
    public function run()
    {
        // Motivos de Movimiento
        $motivos = [
            ['nombre' => 'Compra a proveedor', 'tipo' => 'ingreso', 'codigo' => 'COMP01'],
            ['nombre' => 'Venta a cliente', 'tipo' => 'salida', 'codigo' => 'VENT01'],
            ['nombre' => 'Devolución de cliente', 'tipo' => 'ingreso', 'codigo' => 'DEV01'],
            ['nombre' => 'Devolución a proveedor', 'tipo' => 'salida', 'codigo' => 'DEVP01'],
            ['nombre' => 'Ajuste de inventario', 'tipo' => 'ajuste', 'codigo' => 'AJUS01'],
            ['nombre' => 'Traslado entre almacenes', 'tipo' => 'transferencia', 'codigo' => 'TRAS01'],
            ['nombre' => 'Muestra gratis', 'tipo' => 'salida', 'codigo' => 'MUES01'],
            ['nombre' => 'Producto dañado', 'tipo' => 'salida', 'codigo' => 'DANI01'],
        ];
        foreach ($motivos as $motivo) {
            MotivoMovimiento::create($motivo);
        }

        // Colores
        $colores = [
            ['nombre' => 'Negro', 'codigo_hex' => '#000000'],
            ['nombre' => 'Blanco', 'codigo_hex' => '#FFFFFF'],
            ['nombre' => 'Rojo', 'codigo_hex' => '#FF0000'],
            ['nombre' => 'Azul', 'codigo_hex' => '#0000FF'],
            ['nombre' => 'Verde', 'codigo_hex' => '#00FF00'],
            ['nombre' => 'Amarillo', 'codigo_hex' => '#FFFF00'],
            ['nombre' => 'Gris', 'codigo_hex' => '#808080'],
            ['nombre' => 'Plateado', 'codigo_hex' => '#C0C0C0'],
            ['nombre' => 'Dorado', 'codigo_hex' => '#FFD700'],
            ['nombre' => 'Rosado', 'codigo_hex' => '#FFC0CB'],
        ];
        foreach ($colores as $color) {
            Color::create($color);
        }

        // Unidades de Medida
        $unidades = [
            ['nombre' => 'Unidad', 'abreviatura' => 'UND', 'categoria' => 'unidad'],
            ['nombre' => 'Kilogramo', 'abreviatura' => 'KG', 'categoria' => 'peso', 'permite_decimales' => true],
            ['nombre' => 'Gramo', 'abreviatura' => 'GR', 'categoria' => 'peso', 'permite_decimales' => true],
            ['nombre' => 'Litro', 'abreviatura' => 'LT', 'categoria' => 'volumen', 'permite_decimales' => true],
            ['nombre' => 'Mililitro', 'abreviatura' => 'ML', 'categoria' => 'volumen', 'permite_decimales' => true],
            ['nombre' => 'Metro', 'abreviatura' => 'MT', 'categoria' => 'longitud', 'permite_decimales' => true],
            ['nombre' => 'Centímetro', 'abreviatura' => 'CM', 'categoria' => 'longitud', 'permite_decimales' => true],
            ['nombre' => 'Pulgada', 'abreviatura' => 'IN', 'categoria' => 'longitud', 'permite_decimales' => true],
            ['nombre' => 'Caja', 'abreviatura' => 'CJA', 'categoria' => 'unidad'],
            ['nombre' => 'Pack', 'abreviatura' => 'PCK', 'categoria' => 'unidad'],
        ];
        foreach ($unidades as $unidad) {
            UnidadMedida::create($unidad);
        }

        // Marcas
        $marcas = [
            ['nombre' => 'Apple', 'sitio_web' => 'https://www.apple.com'],
            ['nombre' => 'Samsung', 'sitio_web' => 'https://www.samsung.com'],
            ['nombre' => 'Xiaomi', 'sitio_web' => 'https://www.mi.com'],
            ['nombre' => 'Huawei', 'sitio_web' => 'https://www.huawei.com'],
            ['nombre' => 'Motorola', 'sitio_web' => 'https://www.motorola.com'],
            ['nombre' => 'Sony', 'sitio_web' => 'https://www.sony.com'],
            ['nombre' => 'LG', 'sitio_web' => 'https://www.lg.com'],
            ['nombre' => 'Genérico', 'descripcion' => 'Productos sin marca específica'],
        ];
        foreach ($marcas as $marca) {
            Marca::create($marca);
        }
    }
}