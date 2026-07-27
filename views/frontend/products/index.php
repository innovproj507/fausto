<?php
ob_start();
$title = 'Productos - Fausto Salazar, S.A.';
?>

<!-- Page Header -->
<div class="bg-secondary py-10">
    <div class="container mx-auto px-4">
        <h1 class="font-display text-4xl font-bold text-white uppercase tracking-wide">Catálogo de Productos</h1>
        <?php if (!empty($search)): ?>
            <p class="text-gray-300 mt-2">
                Resultados para: <strong class="text-accent">"<?= sanitize($search) ?>"</strong>
                (<?= number_format($pagination['total'] ?? 0) ?> encontrados)
            </p>
        <?php else: ?>
            <p class="text-gray-300 mt-2"><?= number_format($pagination['total'] ?? 0) ?> productos disponibles</p>
        <?php endif; ?>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <!-- Category Filter Chips -->
    <?php if (!empty($categories)): ?>
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="<?= url('/products' . (!empty($search) ? '?search=' . urlencode($search) : '')) ?>"
               class="px-4 py-2 rounded-full text-sm font-semibold border-2 transition <?= empty($categoryId) ? 'bg-primary text-white border-primary' : 'bg-white text-secondary border-gray-200 hover:border-primary' ?>">
                Todas
            </a>
            <?php foreach ($categories as $cat): ?>
                <?php
                    $params = array_filter(['category' => $cat['id'], 'search' => $search ?? '']);
                    $isActive = (string) ($categoryId ?? '') === (string) $cat['id'];
                ?>
                <a href="<?= url('/products?' . http_build_query($params)) ?>"
                   class="px-4 py-2 rounded-full text-sm font-semibold border-2 transition flex items-center gap-2 <?= $isActive ? 'bg-primary text-white border-primary' : 'bg-white text-secondary border-gray-200 hover:border-primary' ?>">
                    <i class="<?= $cat['icon_class'] ?? 'fas fa-box' ?> text-xs"></i><?= sanitize($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all border border-gray-100">
                    <a href="<?= url('/products/' . $product['slug']) ?>" class="block">
                        <div class="relative overflow-hidden bg-gray-100 h-64 flex items-center justify-center">
                            <?php if ($product['featured_image']): ?>
                                <img src="<?= url('uploads/' . $product['featured_image']) ?>"
                                     alt="<?= sanitize($product['name']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            <?php else: ?>
                                <i class="fas fa-box text-8xl text-gray-300"></i>
                            <?php endif; ?>

                            <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                <?php $discount = round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100); ?>
                                <span class="absolute top-3 left-3 bg-primary text-white px-2.5 py-1 text-xs font-bold uppercase shadow">
                                    -<?= $discount ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>

                    <div class="p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1"><?= sanitize($product['category_name'] ?? 'Sin categoría') ?></p>
                        <h3 class="font-semibold text-secondary mb-2 line-clamp-2 min-h-[2.5rem]">
                            <a href="<?= url('/products/' . $product['slug']) ?>" class="hover:text-primary transition">
                                <?= sanitize($product['name']) ?>
                            </a>
                        </h3>

                        <?php if (isset($_SESSION['user'])): ?>
                            <div class="mb-4">
                                <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                    <span class="text-gray-400 line-through text-sm">$<?= number_format($product['compare_price'], 2) ?></span>
                                <?php endif; ?>
                                <span class="text-2xl font-bold text-primary ml-2">$<?= number_format($product['price'], 2) ?></span>
                            </div>

                            <form action="<?= url('/cart/add') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit"
                                    class="w-full bg-secondary text-white py-2 rounded-lg hover:bg-primary transition font-semibold">
                                    <i class="fas fa-cart-plus mr-2"></i>Agregar
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?= url('/account/login') ?>"
                               class="block w-full bg-gray-100 text-secondary text-center py-2 rounded-lg hover:bg-gray-200 transition font-medium">
                                <i class="fas fa-lock mr-1"></i>Inicia sesión para comprar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-16">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <p class="text-xl text-gray-600">No se encontraron productos</p>
                <a href="<?= url('/products') ?>" class="mt-4 inline-block text-primary hover:underline font-semibold">Ver todo el catálogo</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
        <?php
            $currentPage = $pagination['page'];
            $lastPage = $pagination['last_page'];

            $pageUrl = function (int $i) use ($search, $categoryId) {
                $params = array_filter([
                    'page' => $i,
                    'search' => $search ?? '',
                    'category' => $categoryId ?? ''
                ]);
                return url('/products?' . http_build_query($params));
            };

            $window = 2;
            $pages = array_unique(array_filter(array_merge(
                [1, $lastPage],
                range(max(1, $currentPage - $window), min($lastPage, $currentPage + $window))
            ), fn($p) => $p >= 1 && $p <= $lastPage));
            sort($pages);
        ?>
        <div class="flex justify-center items-center gap-2 mt-10 flex-wrap">
            <?php if ($currentPage > 1): ?>
                <a href="<?= $pageUrl($currentPage - 1) ?>"
                   class="px-4 py-2 bg-white border-2 border-gray-200 rounded-lg hover:border-primary hover:text-primary transition font-semibold">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php $previous = null; ?>
            <?php foreach ($pages as $i): ?>
                <?php if ($previous !== null && $i - $previous > 1): ?>
                    <span class="px-2 text-gray-400">&hellip;</span>
                <?php endif; ?>

                <?php if ($i == $currentPage): ?>
                    <span class="px-4 py-2 bg-primary text-white rounded-lg font-semibold"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $pageUrl($i) ?>"
                       class="px-4 py-2 bg-white border-2 border-gray-200 rounded-lg hover:border-primary hover:text-primary transition font-semibold">
                        <?= $i ?>
                    </a>
                <?php endif; ?>
                <?php $previous = $i; ?>
            <?php endforeach; ?>

            <?php if ($currentPage < $lastPage): ?>
                <a href="<?= $pageUrl($currentPage + 1) ?>"
                   class="px-4 py-2 bg-white border-2 border-gray-200 rounded-lg hover:border-primary hover:text-primary transition font-semibold">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <p class="text-center text-sm text-gray-500 mt-3">
            Página <?= $currentPage ?> de <?= $lastPage ?> (<?= number_format($pagination['total']) ?> productos)
        </p>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main_tailwind.php';
?>
