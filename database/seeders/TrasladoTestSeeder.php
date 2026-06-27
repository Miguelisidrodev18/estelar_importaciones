<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Almacen;
use App\Models\Categoria;
use App\Models\Catalogo\Color;
use App\Models\Catalogo\Marca;
use App\Models\Catalogo\Modelo;
use App\Models\Catalogo\UnidadMedida;
use App\Models\Imei;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\StockAlmacen;

class TrasladoTestSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('Omitido en produccion.');
            return;
        }

        // ── Almacenes ────────────────────────────────────────────
        $almacenCentral = Almacen::firstOrCreate(
            ['codigo' => 'ALM-CENTRAL'],
            ['nombre' => 'Almacen Central', 'tipo' => 'principal', 'estado' => 'activo']
        );
        $almacenTienda = Almacen::firstOrCreate(
            ['codigo' => 'ALM-TIENDA1'],
            ['nombre' => 'Tienda Miraflores', 'tipo' => 'tienda', 'estado' => 'activo']
        );

        // ── Categoria ────────────────────────────────────────────
        $catCelulares = Categoria::firstOrCreate(
            ['nombre' => 'Celulares'],
            ['codigo' => 'CEL', 'estado' => 'activo']
        );
        $catAccesorios = Categoria::firstOrCreate(
            ['nombre' => 'Accesorios'],
            ['codigo' => 'ACC', 'estado' => 'activo']
        );

        // ── Marcas y Modelos ─────────────────────────────────────
        $samsung = Marca::firstOrCreate(['nombre' => 'Samsung']);
        $apple   = Marca::firstOrCreate(['nombre' => 'Apple']);

        $catCelulares->marcas()->syncWithoutDetaching([$samsung->id, $apple->id]);

        $galaxyA54 = Modelo::firstOrCreate(
            ['nombre' => 'Galaxy A54', 'marca_id' => $samsung->id]
        );
        $iphone15 = Modelo::firstOrCreate(
            ['nombre' => 'iPhone 15', 'marca_id' => $apple->id]
        );

        // ── Colores ──────────────────────────────────────────────
        $negro    = Color::firstOrCreate(['nombre' => 'Negro'],    ['codigo_hex' => '#000000']);
        $blanco   = Color::firstOrCreate(['nombre' => 'Blanco'],   ['codigo_hex' => '#FFFFFF']);
        $azul     = Color::firstOrCreate(['nombre' => 'Azul'],     ['codigo_hex' => '#0000FF']);
        $dorado   = Color::firstOrCreate(['nombre' => 'Dorado'],   ['codigo_hex' => '#FFD700']);
        $rosado   = Color::firstOrCreate(['nombre' => 'Rosado'],   ['codigo_hex' => '#FFC0CB']);

        $und = UnidadMedida::firstOrCreate(
            ['abreviatura' => 'UND'],
            ['nombre' => 'Unidad', 'categoria' => 'unidad', 'permite_decimales' => false]
        );

        // ════════════════════════════════════════════════════════
        // PRODUCTO 1: Samsung Galaxy A54 (IMEI) — 3 colores × 2 capacidades
        // ════════════════════════════════════════════════════════
        $prod1 = Producto::firstOrCreate(
            ['codigo' => 'CEL-SA54'],
            [
                'nombre'           => 'Samsung Galaxy A54 5G',
                'categoria_id'     => $catCelulares->id,
                'marca_id'         => $samsung->id,
                'modelo_id'        => $galaxyA54->id,
                'unidad_medida_id' => $und->id,
                'tipo_inventario'  => 'serie',
                'estado'           => 'activo',
                'stock_actual'     => 0,
                'dias_garantia'    => 365,
                'tipo_garantia'    => 'proveedor',
            ]
        );

        $variantes1 = [
            ['color' => $negro,  'capacidad' => '128GB'],
            ['color' => $blanco, 'capacidad' => '128GB'],
            ['color' => $azul,   'capacidad' => '128GB'],
            ['color' => $negro,  'capacidad' => '256GB'],
            ['color' => $blanco, 'capacidad' => '256GB'],
            ['color' => $azul,   'capacidad' => '256GB'],
        ];

        $imeisCount = 0;
        foreach ($variantes1 as $vData) {
            $sku = 'SA54-' . strtoupper(substr($vData['color']->nombre, 0, 3)) . '-' . str_replace('GB', 'G', $vData['capacidad']);
            $variante = ProductoVariante::firstOrCreate(
                ['producto_id' => $prod1->id, 'color_id' => $vData['color']->id, 'capacidad' => $vData['capacidad']],
                ['sku' => $sku, 'estado' => 'activo', 'stock_actual' => 0]
            );

            $cantImeis = match (true) {
                $vData['color']->nombre === 'Negro' && $vData['capacidad'] === '128GB' => 5,
                $vData['color']->nombre === 'Blanco' && $vData['capacidad'] === '128GB' => 3,
                $vData['color']->nombre === 'Azul' && $vData['capacidad'] === '128GB' => 0,
                $vData['color']->nombre === 'Negro' && $vData['capacidad'] === '256GB' => 2,
                $vData['color']->nombre === 'Blanco' && $vData['capacidad'] === '256GB' => 1,
                default => 0,
            };

            for ($i = 0; $i < $cantImeis; $i++) {
                $imeiCode = $this->generarImei();
                Imei::firstOrCreate(
                    ['codigo_imei' => $imeiCode],
                    [
                        'producto_id'  => $prod1->id,
                        'variante_id'  => $variante->id,
                        'modelo_id'    => $galaxyA54->id,
                        'color_id'     => $vData['color']->id,
                        'almacen_id'   => $almacenCentral->id,
                        'estado_imei'  => Imei::ESTADO_EN_STOCK,
                        'fecha_ingreso'=> now(),
                    ]
                );
                $imeisCount++;
            }

            $realStock = Imei::where('variante_id', $variante->id)
                ->where('estado_imei', Imei::ESTADO_EN_STOCK)->count();
            $variante->update(['stock_actual' => $realStock]);
        }
        $prod1->update(['stock_actual' => $imeisCount]);

        // ════════════════════════════════════════════════════════
        // PRODUCTO 2: iPhone 15 (IMEI) — 3 colores × 1 capacidad
        // ════════════════════════════════════════════════════════
        $prod2 = Producto::firstOrCreate(
            ['codigo' => 'CEL-IP15'],
            [
                'nombre'           => 'iPhone 15',
                'categoria_id'     => $catCelulares->id,
                'marca_id'         => $apple->id,
                'modelo_id'        => $iphone15->id,
                'unidad_medida_id' => $und->id,
                'tipo_inventario'  => 'serie',
                'estado'           => 'activo',
                'stock_actual'     => 0,
                'dias_garantia'    => 365,
                'tipo_garantia'    => 'fabricante',
            ]
        );

        $variantes2 = [
            ['color' => $negro,  'capacidad' => '128GB', 'cant' => 4],
            ['color' => $rosado, 'capacidad' => '128GB', 'cant' => 2],
            ['color' => $azul,   'capacidad' => '128GB', 'cant' => 3],
        ];

        $imeisCount2 = 0;
        foreach ($variantes2 as $vData) {
            $sku = 'IP15-' . strtoupper(substr($vData['color']->nombre, 0, 3)) . '-128G';
            $variante = ProductoVariante::firstOrCreate(
                ['producto_id' => $prod2->id, 'color_id' => $vData['color']->id, 'capacidad' => $vData['capacidad']],
                ['sku' => $sku, 'estado' => 'activo', 'stock_actual' => 0]
            );

            for ($i = 0; $i < $vData['cant']; $i++) {
                $imeiCode = $this->generarImei();
                Imei::firstOrCreate(
                    ['codigo_imei' => $imeiCode],
                    [
                        'producto_id'  => $prod2->id,
                        'variante_id'  => $variante->id,
                        'modelo_id'    => $iphone15->id,
                        'color_id'     => $vData['color']->id,
                        'almacen_id'   => $almacenCentral->id,
                        'estado_imei'  => Imei::ESTADO_EN_STOCK,
                        'fecha_ingreso'=> now(),
                    ]
                );
                $imeisCount2++;
            }

            $realStock = Imei::where('variante_id', $variante->id)
                ->where('estado_imei', Imei::ESTADO_EN_STOCK)->count();
            $variante->update(['stock_actual' => $realStock]);
        }
        $prod2->update(['stock_actual' => $imeisCount2]);

        // ════════════════════════════════════════════════════════
        // PRODUCTO 3: Funda Silicona (stock por cantidad, con variantes de color)
        // ════════════════════════════════════════════════════════
        $prod3 = Producto::firstOrCreate(
            ['codigo' => 'ACC-FUNDA01'],
            [
                'nombre'           => 'Funda Silicona Universal',
                'categoria_id'     => $catAccesorios->id,
                'marca_id'         => Marca::firstOrCreate(['nombre' => 'Generico'])->id,
                'unidad_medida_id' => $und->id,
                'tipo_inventario'  => 'cantidad',
                'estado'           => 'activo',
                'stock_actual'     => 0,
            ]
        );

        $stockTotal3 = 0;
        $fundaVariantes = [
            ['color' => $negro,  'cant' => 25],
            ['color' => $blanco, 'cant' => 15],
            ['color' => $azul,   'cant' => 10],
            ['color' => $rosado, 'cant' => 8],
            ['color' => $dorado, 'cant' => 0],
        ];

        foreach ($fundaVariantes as $fv) {
            $sku = 'FUNDA-' . strtoupper(substr($fv['color']->nombre, 0, 3));
            $variante = ProductoVariante::firstOrCreate(
                ['producto_id' => $prod3->id, 'color_id' => $fv['color']->id],
                ['sku' => $sku, 'estado' => 'activo', 'stock_actual' => $fv['cant']]
            );
            $variante->update(['stock_actual' => $fv['cant']]);
            $stockTotal3 += $fv['cant'];
        }
        $prod3->update(['stock_actual' => $stockTotal3]);

        StockAlmacen::updateOrCreate(
            ['producto_id' => $prod3->id, 'almacen_id' => $almacenCentral->id],
            ['cantidad' => $stockTotal3]
        );

        // ════════════════════════════════════════════════════════
        // PRODUCTO 4: Cargador (stock simple, sin variantes)
        // ════════════════════════════════════════════════════════
        $prod4 = Producto::firstOrCreate(
            ['codigo' => 'ACC-CARG01'],
            [
                'nombre'           => 'Cargador Rapido USB-C 25W',
                'categoria_id'     => $catAccesorios->id,
                'marca_id'         => $samsung->id,
                'unidad_medida_id' => $und->id,
                'tipo_inventario'  => 'cantidad',
                'estado'           => 'activo',
                'stock_actual'     => 30,
            ]
        );
        StockAlmacen::updateOrCreate(
            ['producto_id' => $prod4->id, 'almacen_id' => $almacenCentral->id],
            ['cantidad' => 30]
        );

        // ════════════════════════════════════════════════════════
        // STOCK EN TIENDA (para que el POS muestre productos)
        // ════════════════════════════════════════════════════════
        $tiendaReal = Almacen::where('tipo', 'tienda')->where('estado', 'activo')
            ->whereNotIn('id', [$almacenTienda->id])
            ->first() ?? $almacenTienda;

        $this->command->line("Distribuyendo stock a tienda: {$tiendaReal->nombre} (ID:{$tiendaReal->id})");

        // Mover algunos IMEIs del Galaxy A54 a la tienda
        $imeisParaMover1 = Imei::where('producto_id', $prod1->id)
            ->where('almacen_id', $almacenCentral->id)
            ->where('estado_imei', Imei::ESTADO_EN_STOCK)
            ->limit(4)
            ->get();
        foreach ($imeisParaMover1 as $imei) {
            $imei->update(['almacen_id' => $tiendaReal->id]);
        }

        // Mover algunos IMEIs del iPhone 15 a la tienda
        $imeisParaMover2 = Imei::where('producto_id', $prod2->id)
            ->where('almacen_id', $almacenCentral->id)
            ->where('estado_imei', Imei::ESTADO_EN_STOCK)
            ->limit(3)
            ->get();
        foreach ($imeisParaMover2 as $imei) {
            $imei->update(['almacen_id' => $tiendaReal->id]);
        }

        // Stock de accesorios en la tienda
        StockAlmacen::updateOrCreate(
            ['producto_id' => $prod3->id, 'almacen_id' => $tiendaReal->id],
            ['cantidad' => 20]
        );
        StockAlmacen::updateOrCreate(
            ['producto_id' => $prod4->id, 'almacen_id' => $tiendaReal->id],
            ['cantidad' => 10]
        );

        // Recalcular stock en almacen central (descontando lo movido)
        $centralImeis1 = Imei::where('producto_id', $prod1->id)
            ->where('almacen_id', $almacenCentral->id)
            ->where('estado_imei', Imei::ESTADO_EN_STOCK)->count();
        $centralImeis2 = Imei::where('producto_id', $prod2->id)
            ->where('almacen_id', $almacenCentral->id)
            ->where('estado_imei', Imei::ESTADO_EN_STOCK)->count();

        // Actualizar stock_almacen del central para los accesorios (restar lo enviado a tienda)
        $stockCentralFunda = max(0, $stockTotal3 - 20);
        StockAlmacen::updateOrCreate(
            ['producto_id' => $prod3->id, 'almacen_id' => $almacenCentral->id],
            ['cantidad' => $stockCentralFunda]
        );
        StockAlmacen::updateOrCreate(
            ['producto_id' => $prod4->id, 'almacen_id' => $almacenCentral->id],
            ['cantidad' => 20]
        );

        // ── Resumen ──────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('=== Datos de prueba para traslados ===');
        $this->command->info('');
        $this->command->info("Almacenes:");
        $this->command->line("  - {$almacenCentral->nombre} (origen con stock)");
        $this->command->line("  - {$almacenTienda->nombre} (destino vacio)");
        $this->command->info('');
        $this->command->info("Productos con variantes (IMEI):");
        $this->command->line("  - Samsung Galaxy A54 5G: 6 variantes, {$prod1->stock_actual} IMEIs en stock");
        $this->command->line("    Negro/128GB=5, Blanco/128GB=3, Azul/128GB=0");
        $this->command->line("    Negro/256GB=2, Blanco/256GB=1, Azul/256GB=0");
        $this->command->line("  - iPhone 15: 3 variantes, {$prod2->stock_actual} IMEIs en stock");
        $this->command->line("    Negro=4, Rosado=2, Azul=3");
        $this->command->info('');
        $this->command->info("Productos con variantes (cantidad):");
        $this->command->line("  - Funda Silicona: 5 colores, {$prod3->stock_actual} unidades");
        $this->command->line("    Negro=25, Blanco=15, Azul=10, Rosado=8, Dorado=0");
        $this->command->info('');
        $this->command->info("Productos simples:");
        $this->command->line("  - Cargador USB-C 25W: 30 unidades en almacen central");
        $this->command->info('');
        $this->command->info("Stock en tienda '{$tiendaReal->nombre}':");
        $tiendaImeis1 = Imei::where('producto_id', $prod1->id)->where('almacen_id', $tiendaReal->id)->where('estado_imei', Imei::ESTADO_EN_STOCK)->count();
        $tiendaImeis2 = Imei::where('producto_id', $prod2->id)->where('almacen_id', $tiendaReal->id)->where('estado_imei', Imei::ESTADO_EN_STOCK)->count();
        $this->command->line("  - Galaxy A54: {$tiendaImeis1} IMEIs");
        $this->command->line("  - iPhone 15: {$tiendaImeis2} IMEIs");
        $this->command->line("  - Funda Silicona: 20 unidades");
        $this->command->line("  - Cargador USB-C: 10 unidades");
        $this->command->info('');
        $this->command->info("Prueba POS: Ventas > Nueva Venta");
        $this->command->line("  Selecciona la tienda y veras los productos con stock");
        $this->command->info('');
        $this->command->info("Prueba Traslados: Traslados > Nuevo Traslado");
        $this->command->line("  1. Almacen Central como origen");
        $this->command->line("  2. Cualquier tienda como destino");
        $this->command->line("  3. Busca 'Samsung' o 'Funda' para ver variantes con stock");
    }

    private function generarImei(): string
    {
        do {
            $imei = '';
            for ($i = 0; $i < 14; $i++) {
                $imei .= random_int(0, 9);
            }
            $suma = 0;
            for ($i = 0; $i < 14; $i++) {
                $d = (int) $imei[$i];
                if ($i % 2 === 0) {
                    $d *= 2;
                    if ($d > 9) $d -= 9;
                }
                $suma += $d;
            }
            $imei .= (10 - ($suma % 10)) % 10;
        } while (Imei::where('codigo_imei', $imei)->exists());

        return $imei;
    }
}
