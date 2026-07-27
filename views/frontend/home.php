<?php 
ob_start(); 
$title = 'Inicio - Fausto Salazar, S.A.';
?>

<!-- Hero Carousel -->
<?php
    $heroSlides = [
        [
            'image' => asset('images/banner1.png'),
            'alt' => 'Ferretería Especializada Más Completa de Panamá - Herramientas Eléctricas',
            'tag' => 'Ferretería Especializada',
            'heading' => 'Todo Para Tu Proyecto',
            'highlight' => '+9,000 Productos en Stock',
            'cta_text' => 'Ver Catálogo',
            'cta_link' => url('/products'),
        ],
        [
            'image' => asset('images/banner2.png'),
            'alt' => 'El Mundo de la Soldadura - Máquinas, Electrodos y Cortadoras',
            'tag' => 'Soldadura y Cortes',
            'heading' => 'Equipa Tu Taller',
            'highlight' => 'Máquinas, Electrodos y Cortadoras',
            'cta_text' => 'Ver Soldadura',
            'cta_link' => url('/products?search=sold'),
        ],
    ];
?>
<section class="relative overflow-hidden bg-secondary" id="heroCarousel">
    <div class="relative h-[320px] sm:h-[400px] lg:h-[480px]">
        <?php foreach ($heroSlides as $i => $slide): ?>
            <div class="hero-slide absolute inset-0 transition-opacity duration-700 <?= $i === 0 ? 'opacity-100' : 'opacity-0 pointer-events-none' ?>">
                <div class="grid grid-cols-1 sm:grid-cols-3 h-full">
                    <div class="sm:col-span-2 h-full overflow-hidden">
                        <img src="<?= $slide['image'] ?>" alt="<?= sanitize($slide['alt']) ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="hidden sm:flex sm:col-span-1 bg-primary text-white flex-col items-center justify-center text-center p-8">
                        <span class="text-sm uppercase tracking-widest opacity-80 mb-2"><?= sanitize($slide['tag']) ?></span>
                        <h2 class="font-display text-3xl lg:text-4xl font-bold uppercase leading-tight mb-3"><?= sanitize($slide['heading']) ?></h2>
                        <p class="text-accent text-lg lg:text-xl font-semibold mb-6"><?= sanitize($slide['highlight']) ?></p>
                        <a href="<?= $slide['cta_link'] ?>"
                           class="inline-block bg-white text-primary px-6 py-3 rounded-lg font-bold uppercase tracking-wide hover:bg-gray-100 transition">
                            <?= sanitize($slide['cta_text']) ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (count($heroSlides) > 1): ?>
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
            <?php foreach ($heroSlides as $i => $slide): ?>
                <button onclick="goToHeroSlide(<?= $i ?>)"
                        class="hero-dot h-2 rounded-full transition-all <?= $i === 0 ? 'w-8 bg-primary' : 'w-2 bg-white/70' ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if (count($heroSlides) > 1): ?>
<script>
(function () {
    const slides = document.querySelectorAll('#heroCarousel .hero-slide');
    const dots = document.querySelectorAll('#heroCarousel .hero-dot');
    let current = 0;

    window.goToHeroSlide = function (i) {
        slides[current].classList.add('opacity-0', 'pointer-events-none');
        slides[current].classList.remove('opacity-100');
        dots[current]?.classList.replace('w-8', 'w-2');
        dots[current]?.classList.replace('bg-primary', 'bg-white/70');

        current = i;
        slides[current].classList.remove('opacity-0', 'pointer-events-none');
        slides[current].classList.add('opacity-100');
        dots[current]?.classList.replace('w-2', 'w-8');
        dots[current]?.classList.replace('bg-white/70', 'bg-primary');
    };

    setInterval(() => goToHeroSlide((current + 1) % slides.length), 5000);
})();
</script>
<?php endif; ?>

<!-- Categories (circular browse row) -->
<?php if (!empty($categories)): ?>
<section class="py-10 bg-gray-50 border-b">
    <div class="container mx-auto px-4">
        <div class="flex gap-6 overflow-x-auto no-scrollbar pb-2">
            <?php foreach ($categories as $category): ?>
                <a href="<?= url('/category/' . $category['slug']) ?>"
                   class="flex-shrink-0 w-28 text-center group">
                    <div class="w-24 h-24 mx-auto rounded-full bg-white border-2 border-gray-100 shadow-sm flex items-center justify-center mb-3 group-hover:border-primary group-hover:shadow-lg transition-all">
                        <i class="<?= $category['icon_class'] ?? 'fas fa-box' ?> text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-secondary leading-snug group-hover:text-primary transition"><?= sanitize($category['name']) ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Features -->
<section class="py-10 bg-white border-b">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <div class="flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 transition">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shipping-fast text-2xl text-primary"></i>
                </div>
                <div>
                    <h3 class="font-bold text-secondary">Envío Rápido</h3>
                    <p class="text-sm text-gray-500">Entregas eficientes</p>
                </div>
            </div>
            <div class="flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 transition">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shield-alt text-2xl text-primary"></i>
                </div>
                <div>
                    <h3 class="font-bold text-secondary">Pago Seguro</h3>
                    <p class="text-sm text-gray-500">100% Protegido</p>
                </div>
            </div>
            <div class="flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 transition">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-award text-2xl text-primary"></i>
                </div>
                <div>
                    <h3 class="font-bold text-secondary">Calidad</h3>
                    <p class="text-sm text-gray-500">Productos garantizados</p>
                </div>
            </div>
            <div class="flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 transition">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-headset text-2xl text-primary"></i>
                </div>
                <div>
                    <h3 class="font-bold text-secondary">Soporte</h3>
                    <p class="text-sm text-gray-500">Atención personalizada</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<?php if (!empty($featured_products)): ?>
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <div>
                <span class="text-primary font-semibold text-sm uppercase tracking-widest">Lo más buscado</span>
                <h2 class="font-display text-4xl font-bold text-secondary uppercase tracking-wide mt-1">Productos Destacados</h2>
            </div>
            <a href="<?= url('/products') ?>" class="text-primary hover:text-primary-dark font-semibold flex items-center gap-1">Ver Todos <i class="fas fa-arrow-right text-sm"></i></a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($featured_products as $product): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all border border-gray-100">
                    <div class="relative overflow-hidden bg-gray-100 h-64 flex items-center justify-center">
                        <?php if ($product['featured_image']): ?>
                            <img src="<?= url('uploads/' . $product['featured_image']) ?>" 
                                 alt="<?= sanitize($product['name']) ?>"
                                 class="w-full h-full object-cover">
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
                    <div class="p-4">
                        <?php if (!empty($product['category_name'])): ?>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1"><?= sanitize($product['category_name']) ?></p>
                        <?php endif; ?>
                        <h3 class="font-semibold text-secondary mb-2 line-clamp-2"><?= sanitize($product['name']) ?></h3>
                        
                        <?php if (isset($_SESSION['user'])): ?>
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                        <span class="text-gray-400 line-through text-sm">$<?= number_format($product['compare_price'], 2) ?></span>
                                    <?php endif; ?>
                                    <span class="text-2xl font-bold text-primary ml-2">$<?= number_format($product['price'], 2) ?></span>
                                </div>
                            </div>
                            <form action="<?= url('/cart/add') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit"
                                    class="w-full bg-secondary text-white py-2 rounded-lg hover:bg-primary transition font-semibold">
                                    Agregar al Carrito
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?= url('/account/login') ?>"
                               class="block w-full bg-gray-200 text-secondary text-center py-2 rounded-lg hover:bg-gray-300 transition">
                                Inicia sesión para ver precio
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Promo Banner -->
<section class="relative bg-gradient-to-br from-secondary via-secondary to-primary py-16 overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <i class="fas fa-tools absolute -right-10 -top-10 text-[18rem] rotate-12"></i>
    </div>
    <div class="container mx-auto px-4 relative">
        <div class="flex flex-col md:flex-row items-center justify-between text-white">
            <div class="md:w-1/2 mb-6 md:mb-0">
                <span class="text-accent font-semibold text-sm uppercase tracking-widest">¿Tienes un proyecto?</span>
                <h2 class="font-display text-4xl font-bold uppercase tracking-wide mt-1 mb-4">¿Necesitas Asesoría?</h2>
                <p class="text-xl mb-6 text-gray-200">Contáctanos y te ayudaremos a encontrar lo que necesitas</p>
                <a href="<?= url('/contacto') ?>"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-dark text-white px-8 py-3 rounded-lg font-semibold transition shadow-lg">
                    <i class="fas fa-comment-dots"></i>Contáctanos
                </a>
            </div>
            <div class="md:w-1/2 text-center">
                <div class="inline-block bg-white/10 backdrop-blur-sm p-8 rounded-2xl border border-white/20">
                    <i class="fas fa-phone-volume text-9xl text-accent"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/main_tailwind.php';
?>
