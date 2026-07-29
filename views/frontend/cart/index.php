<?php
ob_start();
$title = 'Carrito de Compras - Fausto Salazar, S.A.';
?>

<div class="bg-secondary py-10">
    <div class="container mx-auto px-4">
        <h1 class="font-display text-4xl font-bold text-white uppercase tracking-wide">
            <i class="fas fa-shopping-cart text-primary mr-3"></i>Carrito de Compras
        </h1>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <?php if (!empty($items)): ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Items -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                <?php foreach ($items as $item): ?>
                    <div class="flex flex-col sm:flex-row items-center gap-4 p-6 border-b border-gray-100 last:border-b-0">
                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                            <?php if (!empty($item['featured_image'])): ?>
                                <img src="<?= url('uploads/products/' . $item['featured_image']) ?>"
                                     alt="<?= sanitize($item['name']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-box text-3xl text-gray-300"></i>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1 text-center sm:text-left">
                            <h3 class="font-semibold text-secondary"><?= sanitize($item['name']) ?></h3>
                            <p class="text-primary font-bold mt-1">$<?= number_format($item['price'], 2) ?></p>
                        </div>

                        <form action="<?= url('/cart/update') ?>" method="POST" class="flex items-center gap-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>"
                                   min="1" max="100"
                                   class="w-20 px-3 py-2 border-2 border-gray-200 rounded-lg text-center focus:outline-none focus:border-primary">
                            <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-secondary transition" title="Actualizar">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </form>

                        <div class="w-24 text-center font-bold text-secondary">
                            $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </div>

                        <form action="<?= url('/cart/remove') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <button type="submit" class="text-gray-400 hover:text-primary transition text-lg" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary -->
            <div>
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 sticky top-24">
                    <h3 class="font-display text-xl font-bold text-secondary uppercase tracking-wide mb-4">Resumen del Pedido</h3>
                    <div class="flex justify-between py-3 border-b border-gray-100 text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-semibold text-secondary">$<?= number_format($total ?? 0, 2) ?></span>
                    </div>
                    <div class="flex justify-between py-4 text-lg font-bold">
                        <span class="text-secondary">Total</span>
                        <span class="text-primary">$<?= number_format($total ?? 0, 2) ?></span>
                    </div>

                    <a href="<?= url('/checkout') ?>"
                       class="block w-full text-center bg-primary text-white py-3 rounded-lg hover:bg-primary-dark transition font-semibold shadow-md hover:shadow-lg mb-3">
                        <i class="fas fa-lock mr-2"></i>Proceder al Pago
                    </a>
                    <a href="<?= url('/products') ?>"
                       class="block w-full text-center bg-gray-100 text-secondary py-3 rounded-lg hover:bg-gray-200 transition font-medium">
                        Seguir Comprando
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-20">
            <i class="fas fa-shopping-basket text-7xl text-gray-200 mb-6"></i>
            <p class="text-xl text-gray-600 mb-6">Tu carrito está vacío</p>
            <a href="<?= url('/products') ?>"
               class="inline-flex items-center gap-2 bg-primary text-white px-8 py-3 rounded-lg hover:bg-primary-dark transition font-semibold">
                <i class="fas fa-th-large"></i>Ver Productos
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main_tailwind.php';
?>
