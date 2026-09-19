<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportService
{
    /**
     * Encabezados esperados en el Excel (reporte de tu sistema de inventario).
     * La fila de encabezados se ubica automáticamente buscando "CODIGO".
     */
    private const COLUMN_MAP = [
        'CODIGO' => 'sku',
        'NOMBRE' => 'name',
        'MARCA' => 'brand',
        'CATEGORIA' => 'category',
        'P. VENTA' => 'price',
        'P. MIN' => 'min_price',
        'STOCK' => 'stock',
    ];

    /**
     * Lee el archivo y arma una vista previa fila por fila, SIN persistir nada.
     */
    public function preview(UploadedFile $file): array
    {
        $rows = $this->readRows($file);

        $existingBySku = Product::whereNotNull('sku')->get(['id', 'sku', 'name', 'price', 'stock'])
            ->keyBy('sku');
        $existingCategories = Category::pluck('name')->map(fn($n) => mb_strtoupper($n));
        $existingBrands = Brand::pluck('name')->map(fn($n) => mb_strtoupper($n));

        return array_map(function (array $row) use ($existingBySku, $existingCategories, $existingBrands) {
            $errors = $this->validateRow($row);

            $existing = $row['sku'] ? $existingBySku->get($row['sku']) : null;

            $result = [
                'row' => $row['row'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'brand' => $row['brand'],
                'category' => $row['category'],
                'price' => $row['price'],
                'min_price' => $row['min_price'],
                'stock' => $row['stock'],
                'category_exists' => $row['category'] ? $existingCategories->contains(mb_strtoupper($row['category'])) : false,
                'brand_exists' => $row['brand'] ? $existingBrands->contains(mb_strtoupper($row['brand'])) : false,
                'existing_product_id' => $existing?->id,
                'errors' => $errors,
            ];

            if (! empty($errors)) {
                $result['status'] = 'invalid';
            } elseif ($existing) {
                $result['status'] = 'update';
                $result['changes'] = $this->diff($existing, $row);
            } else {
                $result['status'] = 'new';
            }

            return $result;
        }, $rows);
    }

    /**
     * Aplica las filas ya confirmadas por el usuario (las que llegan del preview,
     * sin las que haya descartado). Crea categorías/marcas que falten.
     */
    public function commit(array $rows): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($rows, &$created, &$updated, &$skipped, &$errors) {
            foreach ($rows as $row) {
                $rowErrors = $this->validateRow($row);
                if (! empty($rowErrors)) {
                    $skipped++;
                    $errors[] = ['row' => $row['row'] ?? null, 'sku' => $row['sku'] ?? null, 'errors' => $rowErrors];
                    continue;
                }

                $category = Category::firstOrCreate(['name' => trim($row['category'])]);
                $brand = $row['brand'] ? Brand::firstOrCreate(['name' => trim($row['brand'])]) : null;

                $attributes = [
                    'category_id' => $category->id,
                    'brand_id' => $brand?->id,
                    'name' => trim($row['name']),
                    'price' => $row['price'],
                    'min_price' => $row['min_price'],
                    'stock' => $row['stock'],
                ];

                $product = Product::where('sku', $row['sku'])->first();

                if ($product) {
                    $product->update($attributes);
                    $updated++;
                } else {
                    Product::create(array_merge($attributes, [
                        'sku' => $row['sku'],
                        'availability' => $row['stock'] > 0 ? 'in_stock' : 'out_of_stock',
                        'is_active' => true,
                    ]));
                    $created++;
                }
            }
        });

        return compact('created', 'updated', 'skipped', 'errors');
    }

    private function readRows(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $grid = $sheet->toArray(null, true, true, false);

        $headerIndex = null;
        $columnIndex = [];

        foreach ($grid as $i => $line) {
            $normalized = array_map(fn($v) => mb_strtoupper(trim((string) $v)), $line);
            if (in_array('CODIGO', $normalized, true)) {
                $headerIndex = $i;
                foreach (self::COLUMN_MAP as $header => $field) {
                    $pos = array_search($header, $normalized, true);
                    if ($pos !== false) {
                        $columnIndex[$field] = $pos;
                    }
                }
                break;
            }
        }

        if ($headerIndex === null || ! isset($columnIndex['sku'])) {
            throw new \RuntimeException('No se encontró la fila de encabezados (se esperaba una columna "CODIGO").');
        }

        $rows = [];
        foreach ($grid as $i => $line) {
            if ($i <= $headerIndex) {
                continue;
            }

            $sku = trim((string) ($line[$columnIndex['sku']] ?? ''));
            $name = trim((string) ($line[$columnIndex['name']] ?? ''));

            // Fila en blanco o de cierre del reporte.
            if ($sku === '' && $name === '') {
                continue;
            }

            $rows[] = [
                'row' => $i + 1,
                // Reportes de POS a veces duplican el código en la misma celda separado por salto de línea.
                'sku' => trim(explode("\n", $sku)[0]),
                'name' => $name,
                'brand' => isset($columnIndex['brand']) ? trim((string) ($line[$columnIndex['brand']] ?? '')) : null,
                'category' => isset($columnIndex['category']) ? trim((string) ($line[$columnIndex['category']] ?? '')) : null,
                'price' => $this->toNumber($line[$columnIndex['price']] ?? null),
                'min_price' => isset($columnIndex['min_price']) ? $this->toNumber($line[$columnIndex['min_price']] ?? null) : null,
                'stock' => (int) $this->toNumber($line[$columnIndex['stock']] ?? 0),
            ];
        }

        return $rows;
    }

    private function toNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) str_replace(',', '.', (string) $value);
    }

    private function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['sku'])) {
            $errors[] = 'Falta el CODIGO.';
        }
        if (empty($row['name'])) {
            $errors[] = 'Falta el NOMBRE.';
        }
        if (empty($row['category'])) {
            $errors[] = 'Falta la CATEGORIA.';
        }
        if ($row['price'] === null || $row['price'] < 0) {
            $errors[] = 'P. VENTA inválido.';
        }
        if (($row['stock'] ?? null) === null || $row['stock'] < 0) {
            $errors[] = 'STOCK inválido.';
        }

        return $errors;
    }

    private function diff(Product $existing, array $row): array
    {
        $changes = [];

        if ((float) $existing->price !== (float) $row['price']) {
            $changes['price'] = [(float) $existing->price, $row['price']];
        }
        if ((int) $existing->stock !== (int) $row['stock']) {
            $changes['stock'] = [$existing->stock, $row['stock']];
        }
        if ($existing->name !== $row['name']) {
            $changes['name'] = [$existing->name, $row['name']];
        }

        return $changes;
    }
}
