<?php

namespace App\Controllers;

use App\Core\CloudinaryStorage;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Product;

class ProductController
{
    private const PRODUCTS_ROUTE = '/products';

    protected $productModel;
    protected $cloudinaryStorage;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->cloudinaryStorage = new CloudinaryStorage();
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

    public function create()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_products'))->handle();

        return view('products/create');
    }

    public function store()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_products'))->handle();

        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $model3d = trim((string) ($_POST['model_3d'] ?? ''));

        if ($name === '') {
            $_SESSION['error'] = 'El nombre del producto es obligatorio.';
            return redirect(self::PRODUCTS_ROUTE . '/create');
        }

        $file = $_FILES['image_2d'] ?? null;
        $uploadedUrl = $this->uploadImageToCloudinary($file, 'new');
        if ($uploadedUrl === null) {
            return redirect(self::PRODUCTS_ROUTE . '/create');
        }

        $created = $this->productModel->create([
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'model_3d' => $model3d !== '' ? $model3d : null,
            'image_2d' => $uploadedUrl,
        ]);

        if (!$created) {
            $_SESSION['error'] = 'No se pudo registrar el producto.';
            return redirect(self::PRODUCTS_ROUTE . '/create');
        }

        $_SESSION['success'] = 'Producto registrado correctamente.';
        return redirect(self::PRODUCTS_ROUTE);
    }

    public function edit()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_products'))->handle();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Producto inválido.';
            return redirect(self::PRODUCTS_ROUTE);
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado.';
            return redirect(self::PRODUCTS_ROUTE);
        }

        return view('products/edit', [
            'product' => $product,
        ]);
    }

    public function update()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_products'))->handle();

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $model3d = trim((string) ($_POST['model_3d'] ?? ''));

        if ($id <= 0 || $name === '') {
            $_SESSION['error'] = 'Datos inválidos para actualizar el producto.';
            return redirect(self::PRODUCTS_ROUTE . '/edit?id=' . $id);
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado.';
            return redirect(self::PRODUCTS_ROUTE);
        }

        $currentImage = trim((string) ($product['image_2d'] ?? ''));
        $newImageUrl = $currentImage !== '' ? $currentImage : null;

        $file = $_FILES['image_2d'] ?? null;
        if ($file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $uploadedUrl = $this->uploadImageToCloudinary($file, (string) $id);
            if ($uploadedUrl === null) {
                return redirect(self::PRODUCTS_ROUTE . '/edit?id=' . $id);
            }
            $newImageUrl = $uploadedUrl;
        }

        $updated = $this->productModel->update($id, [
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'model_3d' => $model3d !== '' ? $model3d : null,
            'image_2d' => $newImageUrl,
        ]);

        if (!$updated) {
            $_SESSION['error'] = 'No se pudo actualizar el producto.';
            return redirect(self::PRODUCTS_ROUTE . '/edit?id=' . $id);
        }

        if ($newImageUrl !== null && $currentImage !== '' && $currentImage !== $newImageUrl && stripos($currentImage, 'res.cloudinary.com') !== false) {
            $this->cloudinaryStorage->deleteByUrl($currentImage);
        }

        $_SESSION['success'] = 'Producto actualizado correctamente.';
        return redirect(self::PRODUCTS_ROUTE);
    }

    public function updateImage()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_products'))->handle();

        $productId = (int) ($_POST['product_id'] ?? 0);
        if ($productId <= 0) {
            $_SESSION['error'] = 'Producto inválido.';
            return redirect('/products');
        }

        $product = $this->productModel->find($productId);
        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado.';
            return redirect('/products');
        }

        $file = $_FILES['image_2d'] ?? null;
        if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Debes seleccionar una imagen válida.';
            return redirect('/products');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            $_SESSION['error'] = 'No se pudo procesar la imagen subida.';
            return redirect('/products');
        }

        $mimeType = (string) mime_content_type($tmpName);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (!in_array($mimeType, $allowedTypes, true)) {
            $_SESSION['error'] = 'Formato no permitido. Usa JPG, PNG o WEBP.';
            return redirect('/products');
        }

        if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'La imagen no debe superar 5MB.';
            return redirect('/products');
        }

        if (!$this->cloudinaryStorage->isEnabled()) {
            $_SESSION['error'] = 'Cloudinary no está configurado.';
            return redirect('/products');
        }

        $folder = 'carlumbre/products/' . $productId;
        $uploadedUrl = $this->cloudinaryStorage->uploadImage($tmpName, $folder);

        if (!$uploadedUrl) {
            $_SESSION['error'] = 'No se pudo subir la imagen a Cloudinary.';
            return redirect('/products');
        }

        $this->productModel->updateImage2D($productId, $uploadedUrl);

        $currentImage = trim((string) ($product['image_2d'] ?? ''));
        if ($currentImage !== '' && stripos($currentImage, 'res.cloudinary.com') !== false && $currentImage !== $uploadedUrl) {
            $this->cloudinaryStorage->deleteByUrl($currentImage);
        }

        $_SESSION['success'] = 'Imagen del producto actualizada correctamente.';
        return redirect('/products');
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_products'))->handle();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Producto inválido.';
            return redirect(self::PRODUCTS_ROUTE);
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado.';
            return redirect(self::PRODUCTS_ROUTE);
        }

        if ($this->productModel->hasQuoteReferences($id)) {
            $_SESSION['error'] = 'No se puede eliminar el producto porque está asociado a una o más cotizaciones.';
            return redirect(self::PRODUCTS_ROUTE);
        }

        $deleted = $this->productModel->delete($id);
        if (!$deleted) {
            $_SESSION['error'] = 'No se pudo eliminar el producto.';
            return redirect(self::PRODUCTS_ROUTE);
        }

        $currentImage = trim((string) ($product['image_2d'] ?? ''));
        if ($currentImage !== '' && stripos($currentImage, 'res.cloudinary.com') !== false) {
            $this->cloudinaryStorage->deleteByUrl($currentImage);
        }

        $_SESSION['success'] = 'Producto eliminado correctamente.';
        return redirect(self::PRODUCTS_ROUTE);
    }

    private function uploadImageToCloudinary(?array $file, string $folderSuffix): ?string
    {
        if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Debes seleccionar una imagen válida.';
            return null;
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            $_SESSION['error'] = 'No se pudo procesar la imagen subida.';
            return null;
        }

        $mimeType = (string) mime_content_type($tmpName);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/gif'];
        if (!in_array($mimeType, $allowedTypes, true)) {
            $_SESSION['error'] = 'Formato no permitido. Usa JPG, PNG, GIF o WEBP.';
            return null;
        }

        if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'La imagen no debe superar 5MB.';
            return null;
        }

        $folder = 'carlumbre/products/' . trim($folderSuffix, '/');
        $uploadedUrl = $this->cloudinaryStorage->uploadImage($tmpName, $folder);

        if (!$uploadedUrl) {
            $_SESSION['error'] = 'No se pudo subir la imagen.';
            return null;
        }

        return $uploadedUrl;
    }
}
