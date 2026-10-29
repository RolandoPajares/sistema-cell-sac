<?php

namespace App\Http\Controllers\Gestion\Productos;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarProductoRequest;
use App\Http\Requests\GuardarProductoRequest;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Compras\Proveedor;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GestionProductosController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $now = now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();
        $previousMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();
        $activeProducts = Producto::query()->where('activo', true)->count();
        $productsAddedThisMonth = Producto::query()
            ->where('activo', true)
            ->whereBetween('creado_en', [$currentMonthStart, $currentMonthEnd])
            ->count();
        $productsAddedPreviousMonth = Producto::query()
            ->where('activo', true)
            ->whereBetween('creado_en', [$previousMonthStart, $previousMonthEnd])
            ->count();
        $outOfStock = Producto::query()->where('activo', true)->where('existencias', 0)->count();
        $lowStock = Producto::query()
            ->where('activo', true)
            ->whereBetween('existencias', [1, 5])
            ->count();
        $categoryCount = Categoria::query()->where('activo', true)->count();
        $inventoryValue = (float) Producto::query()
            ->where('activo', true)
            ->selectRaw('COALESCE(SUM(existencias * COALESCE(precio_oferta, precio)), 0) as inventory_value')
            ->value('inventory_value');
        $offerCount = Producto::query()
            ->where('activo', true)
            ->whereNotNull('precio_oferta')
            ->whereColumn('precio_oferta', '<', 'precio')
            ->count();

        return view('gestion.productos.listar', [
            'products' => $this->productQuery($filters)
                ->with(['category', 'suppliers'])
                ->orderByDesc('id')
                ->paginate($filters['por_pagina'])
                ->withQueryString(),
            'categories' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
            'brands' => Producto::query()
                ->whereNotNull('marca')
                ->where('marca', '<>', '')
                ->distinct()
                ->orderBy('marca')
                ->pluck('marca'),
            'suppliers' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'filters' => $filters,
            'activeProducts' => $activeProducts,
            'productsTrendPercent' => $this->trendPercent($productsAddedThisMonth, $productsAddedPreviousMonth),
            'outOfStock' => $outOfStock,
            'lowStock' => $lowStock,
            'categoryCount' => $categoryCount,
            'inventoryValue' => $inventoryValue,
            'offerCount' => $offerCount,
        ]);
    }

    public function export(Request $request, string $format): Response|StreamedResponse
    {
        $filters = $this->filters($request);
        $products = $this->productQuery($filters)->orderBy('id');

        return match ($format) {
            'csv' => $this->downloadCsv($products),
            'excel' => $this->downloadExcel($products->get()),
            'pdf' => $this->downloadPdf($products->get()),
            default => abort(404),
        };
    }

    public function store(GuardarProductoRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $validated = $request->validated();
        $supplierId = $validated['proveedor_id'] ?? null;
        $data = $this->attributes($validated);

        if ($request->hasFile('imagen')) {
            $data['url_imagen'] = 'storage/'.$request->file('imagen')->store('productos', 'public');
        }

        $product = Producto::create($data + ['activo' => true, 'creado_en' => now()]);
        $product->suppliers()->sync($supplierId ? [$supplierId] : []);
        $audit->record($request->user()->id, 'product.created', 'productos', $product->id, null, $product->only(['marca', 'nombre', 'categoria_id', 'precio', 'existencias']), $request->ip());

        return back()
            ->with('status', 'Producto guardado.')
            ->with('open_product_modal', $request->boolean('continue'));
    }

    public function update(ActualizarProductoRequest $request, Producto $product, RegistradorAuditoria $audit): RedirectResponse
    {
        $this->authorize('update', $product);
        $validated = $request->validated();
        $before = $product->only(['marca', 'nombre', 'categoria_id', 'precio', 'precio_oferta', 'existencias']);
        $data = $this->attributes($validated);

        if ($request->hasFile('imagen')) {
            $data['url_imagen'] = 'storage/'.$request->file('imagen')->store('productos', 'public');
        }

        $product->update($data);

        if (array_key_exists('proveedor_id', $validated)) {
            $product->suppliers()->sync($validated['proveedor_id'] ? [$validated['proveedor_id']] : []);
        }

        $audit->record($request->user()->id, 'product.updated', 'productos', $product->id, $before, $product->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Producto actualizado.');
    }

    public function deactivate(Producto $product, RegistradorAuditoria $audit): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->update(['activo' => false]);
        $audit->record(request()->user()->id, 'product.deactivated', 'productos', $product->id, ['activo' => true], ['activo' => false], request()->ip());

        return back()->with('status', 'Producto desactivado; su historial comercial se conserva.');
    }

    /** @param array<string, mixed> $data */
    private function attributes(array $data): array
    {
        $category = Categoria::findOrFail($data['categoria_id']);
        $data['categoria'] = $category->nombre;
        unset($data['proveedor_id'], $data['imagen'], $data['continue']);

        return $data;
    }

    /**
     * @param  array{buscar:?string,categoria:?int,stock:?string,estado:?string,por_pagina:int}  $filters
     * @return Builder<Producto>
     */
    private function productQuery(array $filters): Builder
    {
        return Producto::query()
            ->when($filters['buscar'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('marca', 'like', '%'.$search.'%')
                        ->orWhere('etiqueta', 'like', '%'.$search.'%')
                        ->orWhere('categoria', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['categoria'], fn (Builder $query, int $categoryId): Builder => $query->where('categoria_id', $categoryId))
            ->when($filters['estado'] === 'activo', fn (Builder $query): Builder => $query->where('activo', true))
            ->when($filters['estado'] === 'inactivo', fn (Builder $query): Builder => $query->where('activo', false))
            ->when($filters['stock'] === 'sin_stock', fn (Builder $query): Builder => $query->where('existencias', 0))
            ->when($filters['stock'] === 'bajo', fn (Builder $query): Builder => $query->whereBetween('existencias', [1, 5]))
            ->when($filters['stock'] === 'disponible', fn (Builder $query): Builder => $query->where('existencias', '>', 5));
    }

    /**
     * @return array{buscar:?string,categoria:?int,stock:?string,estado:?string,por_pagina:int}
     */
    private function filters(Request $request): array
    {
        $stock = trim((string) $request->input('stock', ''));
        $status = trim((string) $request->input('estado', ''));
        $search = trim((string) $request->input('buscar', ''));
        $perPage = $request->integer('por_pagina', 8);

        return [
            'buscar' => $search !== '' ? $search : null,
            'categoria' => $request->integer('categoria') ?: null,
            'stock' => in_array($stock, ['sin_stock', 'bajo', 'disponible'], true) ? $stock : null,
            'estado' => in_array($status, ['activo', 'inactivo'], true) ? $status : null,
            'por_pagina' => in_array($perPage, [8, 10, 20, 50], true) ? $perPage : 8,
        ];
    }

    private function trendPercent(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @param  Builder<Producto>  $query
     */
    private function downloadCsv(Builder $query): StreamedResponse
    {
        $products = $query->cursor();

        return response()->streamDownload(function () use ($products): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['id', 'producto', 'marca', 'categoria', 'precio', 'stock', 'estado']);

            foreach ($products as $product) {
                fputcsv($output, $this->exportRow($product));
            }

            fclose($output);
        }, 'catalogo-productos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  Collection<int, Producto>  $products
     */
    private function downloadExcel(Collection $products): Response
    {
        $rows = $products->map(fn (Producto $product): string => '<tr>'.implode('', array_map(
            fn (string|int|float $value): string => '<td>'.htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</td>',
            $this->exportRow($product),
        )).'</tr>')->implode('');
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif}table{border-collapse:collapse}th,td{border:1px solid #d9e3f0;padding:7px}th{background:#eaf2ff;color:#10234c;text-align:left}</style></head><body><h1>Catálogo de productos</h1><table><thead><tr><th>ID</th><th>Producto</th><th>Marca</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th></tr></thead><tbody>'.$rows.'</tbody></table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="catalogo-productos.xls"',
        ]);
    }

    /**
     * @param  Collection<int, Producto>  $products
     */
    private function downloadPdf(Collection $products): Response
    {
        $header = [
            'CATALOGO DE PRODUCTOS',
            'Generado: '.now()->format('d/m/Y H:i'),
            '',
            'ID | Producto | Marca | Categoria | Precio | Stock | Estado',
            str_repeat('-', 108),
        ];
        $productLines = $products->map(function (Producto $product): string {
            $row = $this->exportRow($product);

            return implode(' | ', [
                $row[0],
                mb_substr((string) $row[1], 0, 30),
                mb_substr((string) $row[2], 0, 18),
                mb_substr((string) $row[3], 0, 18),
                'S/ '.$row[4],
                $row[5],
                $row[6],
            ]);
        })->all();
        $chunks = array_chunk($productLines, 31);
        $pages = [];

        foreach ($chunks ?: [[]] as $index => $chunk) {
            $pages[] = array_merge(
                $index === 0 ? $header : ['CATALOGO DE PRODUCTOS (continuacion)', '', $header[3], $header[4]],
                $chunk,
            );
        }

        return response($this->buildPdfDocument($pages), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="catalogo-productos.pdf"',
        ]);
    }

    /**
     * @return array<int, string|int|float>
     */
    private function exportRow(Producto $product): array
    {
        return [
            $product->id,
            $product->nombre,
            $product->marca,
            $product->categoria ?: 'Sin categoría',
            number_format((float) $product->effective_price, 2, '.', ''),
            $product->existencias,
            $product->activo ? ($product->existencias === 0 ? 'Sin stock' : ($product->precio_oferta ? 'Oferta' : 'Activo')) : 'Inactivo',
        ];
    }

    /**
     * @param  array<int, array<int, string>>  $pages
     */
    private function buildPdfDocument(array $pages): string
    {
        $objects = [];
        $pageObjectIds = [];
        $contentObjectIds = [];
        $nextObjectId = 4;

        foreach ($pages as $page) {
            $pageObjectIds[] = $nextObjectId++;
            $contentObjectIds[] = $nextObjectId++;
        }

        $kids = implode(' ', array_map(static fn (int $id): string => $id.' 0 R', $pageObjectIds));
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids ['.$kids.'] /Count '.count($pageObjectIds).' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';

        foreach ($pages as $index => $page) {
            $content = '';
            $y = 560;

            foreach ($page as $lineIndex => $line) {
                $fontSize = $lineIndex === 0 ? 14 : 8;
                $content .= "BT\n/F1 {$fontSize} Tf\n30 {$y} Td\n(".$this->escapePdfText($line).") Tj\nET\n";
                $y -= 15;
            }

            $contentObjectId = $contentObjectIds[$index];
            $pageObjectId = $pageObjectIds[$index];
            $objects[$contentObjectId] = '<< /Length '.strlen($content)." >>\nstream\n{$content}endstream";
            $objects[$pageObjectId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R >> >> /Contents '.$contentObjectId.' 0 R >>';
        }

        ksort($objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf('%010d 00000 n \n', $offsets[$id]);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
    }

    private function escapePdfText(string $value): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT', $value);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded === false ? $value : $encoded);
    }
}
