<?php 
ob_start(); 
$title = $product['name'] ?? 'Producto';
?>

<div class="container mx-auto px-4 py-8">
    <?php if (isset($product)): ?>
        <!-- Breadcrumb -->
        <nav class="text-sm mb-6 flex items-center gap-2 text-gray-500">
            <a href="<?= url('/') ?>" class="hover:text-primary transition">Inicio</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="<?= url('/products') ?>" class="hover:text-primary transition">Productos</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-800 font-medium truncate"><?= sanitize($product['name']) ?></span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Product Images -->
            <div class="bg-gray-100 rounded-xl p-8 flex items-center justify-center border border-gray-100">
                <?php if ($product['featured_image']): ?>
                    <img src="<?= url('uploads/' . $product['featured_image']) ?>"
                         alt="<?= sanitize($product['name']) ?>"
                         class="max-w-full h-auto rounded-lg">
                <?php else: ?>
                    <i class="fas fa-box text-9xl text-gray-300"></i>
                <?php endif; ?>
            </div>

            <!-- Product Details -->
            <div>
                <?php if (!empty($product['category_name'])): ?>
                    <span class="text-primary font-semibold text-sm uppercase tracking-widest"><?= sanitize($product['category_name']) ?></span>
                <?php endif; ?>
                <h1 class="font-display text-3xl md:text-4xl font-bold text-secondary mt-1 mb-4"><?= sanitize($product['name']) ?></h1>

                <div class="flex items-center gap-3 mb-6">
                    <span class="text-sm text-gray-500 font-mono">SKU: <?= sanitize($product['sku']) ?></span>
                    <?php if ($product['status'] == 'active'): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                            <i class="fas fa-check-circle mr-1"></i>Disponible
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user'])): ?>
                    <div class="mb-6 p-6 bg-gray-50 rounded-xl border border-gray-100">
                        <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                            <div class="mb-2">
                                <span class="text-gray-400 line-through text-xl">$<?= number_format($product['compare_price'], 2) ?></span>
                                <?php $discount = round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100); ?>
                                <span class="ml-2 px-2 py-1 bg-primary text-white rounded text-sm font-semibold">
                                    -<?= $discount ?>% OFF
                                </span>
                            </div>
                        <?php endif; ?>
                        <div class="text-4xl font-bold text-primary">$<?= number_format($product['price'], 2) ?></div>
                    </div>

                    <form action="<?= url('/cart/add') ?>" method="POST" class="mb-6">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                        <div class="flex gap-4 items-end mb-4">
                            <div class="flex-1">
                                <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Cantidad:
                                </label>
                                <input type="number" id="quantity" name="quantity"
                                       value="1" min="1" max="100"
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition">
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full bg-primary text-white py-4 rounded-lg hover:bg-primary-dark transition font-semibold text-lg shadow-md hover:shadow-lg">
                            <i class="fas fa-shopping-cart mr-2"></i>Agregar al Carrito
                        </button>
                    </form>
                <?php else: ?>
                    <div class="mb-6 p-6 bg-yellow-50 border-2 border-yellow-200 rounded-xl">
                        <p class="text-lg font-semibold text-yellow-800 mb-3">
                            <i class="fas fa-lock mr-2"></i>Inicia sesión para ver el precio
                        </p>
                        <a href="<?= url('/account/login') ?>"
                           class="inline-block bg-primary text-white px-8 py-3 rounded-lg hover:bg-primary-dark transition font-semibold">
                            Iniciar Sesión
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($product['description']): ?>
                    <div class="border-t pt-6">
                        <h3 class="font-display text-xl font-bold text-secondary uppercase tracking-wide mb-4">Descripción</h3>
                        <div class="text-gray-700 leading-relaxed">
                            <?= nl2br(sanitize($product['description'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-exclamation-triangle text-6xl text-gray-300 mb-4"></i>
            <p class="text-xl text-gray-600">Producto no encontrado</p>
            <a href="<?= url('/products') ?>" class="mt-4 inline-block text-primary hover:underline">
                Ver todos los productos
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main_tailwind.php';
?>
