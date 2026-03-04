<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Product;

class ProductController
{
    protected $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_products'))->handle();

        $search = trim($_GET['search'] ?? '');
        $category = trim($_GET['category'] ?? '');

        $parts = $this->productModel->all($search, $category !== '' ? $category : null);
        $categories = $this->productModel->getCategories();

        return view('products/index', [
            'menu' => menu(),
            'parts' => $parts,
            'categories' => $categories,
            'selectedCategory' => $category,
        ]);
    }
}
