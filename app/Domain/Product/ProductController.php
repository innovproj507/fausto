<?php

namespace App\Domain\Product;

use App\Core\Request;
use App\Core\Response;

/**
 * Product Controller
 * Maneja las peticiones relacionadas con productos
 */
class ProductController
{
    private ProductRepository $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List all products
     */
    public function index(Request $request): Response
    {
        $page = (int) $request->get('page', 1);
        $search = $request->get('search');
        $categoryId = $request->get('category');

        $filters = [];
        if ($search) {
            $filters['search'] = $search;
        }
        if ($categoryId) {
            $filters['category_id'] = $categoryId;
        }

        $products = $this->repository->paginate($page, 20, $filters);

        return view('frontend.products.index', [
            'products' => $products['data'],
            'pagination' => $products,
            'search' => $search,
            'categories' => $this->repository->getCategories(),
            'categoryId' => $categoryId
        ]);
    }

    /**
     * Show product detail
     */
    public function show(Request $request, string $slug): Response
    {
        $product = $this->repository->findBySlug($slug);

        if (!$product) {
            return Response::view('frontend.404', [], 404);
        }

        // Increment view count
        event('product.viewed', $product);

        return view('frontend.products.show', [
            'product' => $product
        ]);
    }

    /**
     * Show products by category
     */
    public function category(Request $request, string $slug): Response
    {
        $category = $this->repository->findCategoryBySlug($slug);

        if (!$category) {
            return Response::view('frontend.404', [], 404);
        }

        $page = (int) $request->get('page', 1);
        $products = $this->repository->paginate($page, 20, [
            'category_id' => $category['id']
        ]);

        return view('frontend.products.category', [
            'products' => $products['data'],
            'pagination' => $products,
            'category' => $category
        ]);
    }
}
