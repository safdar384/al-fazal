<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Catagory;
use App\Models\Colour;
use App\Models\newProduct;
use App\Models\Packing;
use App\Models\ProductCompany;
use App\Models\ProductVariant;
use App\Models\UnitPerPack;
use App\Support\ProductImageStorage;
use App\Support\ProductLookupUsage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class newProductController extends Controller
{
    /**
     * Stream a product/variant image from the public disk (no storage symlink required).
     */
    public function media(string $path): StreamedResponse|Response
    {
        $path = ProductImageStorage::normalizeRelativePath($path);

        if ($path === '' || ! str_starts_with($path, ProductImageStorage::PRODUCT_DIR.'/')) {
            abort(404);
        }

        if (! Storage::disk(ProductImageStorage::DISK)->exists($path)) {
            abort(404);
        }

        return Storage::disk(ProductImageStorage::DISK)->response($path);
    }

    /**
     * Display a listing of products.
     */
    public function index(): View
    {
        $products = newProduct::orderBy('productName', 'asc')->get();

        return view('Pages.Setup.product', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $colours = Colour::orderBy('id', 'asc')->get();
        $catagorys = Catagory::orderBy('id', 'asc')->get();
        $proCompanys = ProductCompany::orderBy('id', 'asc')->get();
        $packings = Packing::orderBy('pakingName', 'asc')->get();
        $unitPerPacks = UnitPerPack::orderBy('unitName', 'asc')->get();
        $lookupUsage = $this->lookupUsage();

        return view('Pages.Setup.newProduct', array_merge(
            compact('colours', 'catagorys', 'proCompanys', 'packings', 'unitPerPacks'),
            $lookupUsage
        ));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse|RedirectResponse
    {
        $storedPaths = [];

        try {
            $productData = $this->productPayload($request);

            if ($request->has('variants')) {
                $totalStock = 0;
                foreach ($request->variants as $variant) {
                    if (! empty($variant['name']) && ! empty($variant['value'])) {
                        $totalStock += (int) ($variant['stockQty'] ?? 0);
                    }
                }
                $productData['stockQty'] = $totalStock;
            }

            if ($request->hasFile('image')) {
                $path = ProductImageStorage::storeProductImage($request->file('image'));
                $storedPaths[] = $path;
                $productData['image'] = $path;
            }

            $product = newProduct::create($productData);

            if ($request->has('variants')) {
                foreach ($request->variants as $index => $variant) {
                    if (empty($variant['name']) || empty($variant['value'])) {
                        continue;
                    }

                    $variantData = [
                        'productCode' => $product->productCode,
                        'variantName' => $variant['name'],
                        'variantValue' => $variant['value'],
                        'additionalCost' => $variant['additionalCost'] ?? 0,
                        'additionalPrice' => $variant['additionalPrice'] ?? 0,
                        'stockQty' => $variant['stockQty'] ?? 0,
                        'isActive' => true,
                    ];

                    if ($request->hasFile("variants.{$index}.image")) {
                        $vPath = ProductImageStorage::storeVariantImage(
                            $request->file("variants.{$index}.image")
                        );
                        $storedPaths[] = $vPath;
                        $variantData['image'] = $vPath;
                    }

                    ProductVariant::create($variantData);
                }
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully!',
                    'product' => [
                        'id' => $product->id,
                        'productCode' => $product->productCode,
                        'productName' => $product->productName,
                    ],
                ]);
            }

            return redirect()
                ->route('product.create')
                ->with('success', 'Product created successfully!');

        } catch (Exception $e) {
            foreach ($storedPaths as $orphan) {
                ProductImageStorage::deletePath($orphan);
            }

            report($e);

            $message = 'Failed to create product. Please try again.';
            if (str_contains(strtolower($e->getMessage()), 'image')
                || str_contains(strtolower($e->getMessage()), 'gd')
                || str_contains(strtolower($e->getMessage()), 'webp')) {
                $message = $e->getMessage();
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $message);
        }
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(int $id): View
    {
        $colours = Colour::orderBy('id', 'asc')->get();
        $catagorys = Catagory::orderBy('id', 'asc')->get();
        $proCompanys = ProductCompany::orderBy('id', 'asc')->get();
        $packings = Packing::orderBy('id', 'asc')->get();
        $unitPerPacks = UnitPerPack::orderBy('unitName', 'asc')->get();
        $product = newProduct::with('variants')->findOrFail($id);
        $lookupUsage = $this->lookupUsage();

        return view('Pages.Setup.editProduct', array_merge(
            compact('product', 'colours', 'catagorys', 'proCompanys', 'packings', 'unitPerPacks'),
            $lookupUsage
        ));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, int $id): RedirectResponse
    {
        try {
            $product = newProduct::findOrFail($id);
            $productData = $this->productPayload($request);
            $hasVariants = $request->has('existing_variants') || $request->has('variants');

            if ($hasVariants) {
                $totalStock = 0;

                if ($request->has('existing_variants')) {
                    foreach ($request->existing_variants as $variant) {
                        $totalStock += (int) ($variant['stockQty'] ?? 0);
                    }
                }

                if ($request->has('variants')) {
                    foreach ($request->variants as $variant) {
                        if (! empty($variant['name']) && ! empty($variant['value'])) {
                            $totalStock += (int) ($variant['stockQty'] ?? 0);
                        }
                    }
                }

                $productData['stockQty'] = $totalStock;
            }

            $oldProductImage = $product->image;
            $newProductImage = null;

            if ($request->boolean('remove_image')) {
                $productData['image'] = null;
            } elseif ($request->hasFile('image')) {
                // Store new image first; only delete old after DB update succeeds
                $newProductImage = ProductImageStorage::storeProductImage($request->file('image'));
                $productData['image'] = $newProductImage;
            }

            try {
                $product->update($productData);
            } catch (Exception $e) {
                if ($newProductImage) {
                    ProductImageStorage::deletePath($newProductImage);
                }
                throw $e;
            }

            if (array_key_exists('image', $productData) && $oldProductImage && $oldProductImage !== ($productData['image'] ?? null)) {
                ProductImageStorage::deleteIfUnused($oldProductImage);
            }

            // Delete variants (and their images when unused)
            if ($request->has('deleted_variants')) {
                $toDelete = ProductVariant::whereIn('id', $request->deleted_variants)
                    ->where('productCode', $product->productCode)
                    ->get();

                foreach ($toDelete as $variant) {
                    $imagePath = $variant->image;
                    $variant->delete();
                    ProductImageStorage::deleteIfUnused($imagePath);
                }
            }

            // Update existing variants
            if ($request->has('existing_variants')) {
                foreach ($request->existing_variants as $variantId => $variantData) {
                    $variant = ProductVariant::where('id', $variantId)
                        ->where('productCode', $product->productCode)
                        ->first();

                    if (! $variant) {
                        continue;
                    }

                    $update = [
                        'variantName' => $variantData['name'],
                        'variantValue' => $variantData['value'],
                        'additionalCost' => $variantData['additionalCost'] ?? 0,
                        'additionalPrice' => $variantData['additionalPrice'] ?? 0,
                        'stockQty' => $variantData['stockQty'] ?? 0,
                    ];

                    $oldVariantImage = $variant->image;
                    $newVariantImage = null;

                    if (! empty($variantData['remove_image'])) {
                        $update['image'] = null;
                    } elseif ($request->hasFile("existing_variants.{$variantId}.image")) {
                        $newVariantImage = ProductImageStorage::storeVariantImage(
                            $request->file("existing_variants.{$variantId}.image")
                        );
                        $update['image'] = $newVariantImage;
                    }

                    try {
                        $variant->update($update);
                    } catch (Exception $e) {
                        if ($newVariantImage) {
                            ProductImageStorage::deletePath($newVariantImage);
                        }
                        throw $e;
                    }

                    if (array_key_exists('image', $update) && $oldVariantImage && $oldVariantImage !== ($update['image'] ?? null)) {
                        ProductImageStorage::deleteIfUnused($oldVariantImage);
                    }
                }
            }

            // Add new variants
            if ($request->has('variants')) {
                foreach ($request->variants as $index => $variant) {
                    if (empty($variant['name']) || empty($variant['value'])) {
                        continue;
                    }

                    $variantData = [
                        'productCode' => $product->productCode,
                        'variantName' => $variant['name'],
                        'variantValue' => $variant['value'],
                        'additionalCost' => $variant['additionalCost'] ?? 0,
                        'additionalPrice' => $variant['additionalPrice'] ?? 0,
                        'stockQty' => $variant['stockQty'] ?? 0,
                        'isActive' => true,
                    ];

                    if ($request->hasFile("variants.{$index}.image")) {
                        $variantData['image'] = ProductImageStorage::storeVariantImage(
                            $request->file("variants.{$index}.image")
                        );
                    }

                    ProductVariant::create($variantData);
                }
            }

            return redirect()
                ->route('product.index')
                ->with('success', 'Product updated successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update product. Please try again.');
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $product = newProduct::with('variants')->findOrFail($id);
            $productName = $product->productName;
            $imagePaths = collect([$product->image])
                ->merge($product->variants->pluck('image'))
                ->filter()
                ->unique()
                ->values();

            $product->variants()->delete();
            $product->delete();

            foreach ($imagePaths as $path) {
                ProductImageStorage::deleteIfUnused($path);
            }

            return redirect()
                ->route('product.index')
                ->with('success', "Product '{$productName}' deleted successfully!");

        } catch (Exception $e) {
            return redirect()
                ->route('product.index')
                ->with('error', 'Failed to delete product. Please try again.');
        }
    }

    /**
     * Scalar product fields only (exclude uploaded files / remove flags).
     */
    private function productPayload(Request $request): array
    {
        return collect($request->validated())
            ->except([
                'image',
                'remove_image',
                'variants',
                'existing_variants',
                'deleted_variants',
            ])
            ->all();
    }

    /**
     * Lowercased lookup values currently assigned to products.
     */
    private function lookupUsage(): array
    {
        return [
            'usedCompanyNames' => ProductLookupUsage::usedNames('companyName'),
            'usedColourNames' => ProductLookupUsage::usedNames('colourName'),
            'usedPackingNames' => ProductLookupUsage::usedNames('pakingName'),
            'usedUnitPerPackNames' => ProductLookupUsage::usedNames('unitPerPackName'),
            'usedCatagoryNames' => ProductLookupUsage::usedNames('catagoryName'),
        ];
    }
}
