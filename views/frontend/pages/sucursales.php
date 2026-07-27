<?php 
$title = 'Nuestras Sucursales';
ob_start();
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-primary to-red-700 text-white py-16">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Nuestras Sucursales</h1>
        <p class="text-xl opacity-90">Encuentra la sucursal más cercana a ti</p>
    </div>
</div>

<!-- Sucursales Grid -->
<div class="container mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        $sucursales = [
            ['name' => 'Casa Matriz', 'address' => 'Calle 65 Oeste, Panamá', 'phone' => '6566-1892', 'principal' => true],
            ['name' => 'Tocumen', 'address' => 'Avenida Domingo Díaz, Panamá', 'phone' => '6613-8715'],
            ['name' => 'Santiago', 'address' => 'Avenida Central, Calle 15 A Sur', 'phone' => '6613-9385'],
            ['name' => 'David', 'address' => 'Carretera Interamericana', 'phone' => '6400-0813'],
            ['name' => 'Chorrera', 'address' => 'Calle El Carmen', 'phone' => '6613-9239'],
        ];
        $colors = [
            'from-primary to-red-600',
            'from-blue-500 to-blue-700',
            'from-green-500 to-green-700',
            'from-purple-500 to-purple-700',
            'from-amber-500 to-amber-700',
        ];
        ?>
        <?php foreach ($sucursales as $i => $sucursal): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                <div class="h-48 bg-gradient-to-br <?= $colors[$i % count($colors)] ?> flex items-center justify-center">
                    <i class="fas <?= $sucursal['principal'] ?? false ? 'fa-building' : 'fa-store' ?> text-6xl text-white opacity-80"></i>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-secondary"><?= sanitize($sucursal['name']) ?></h3>
                        <?php if ($sucursal['principal'] ?? false): ?>
                            <span class="px-3 py-1 bg-primary text-white rounded-full text-xs font-semibold">Principal</span>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-3 text-gray-600">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-primary mt-1 mr-3"></i>
                            <p><?= sanitize($sucursal['address']) ?><br>Panamá</p>
                        </div>

                        <div class="flex items-center">
                            <i class="fas fa-phone text-primary mr-3"></i>
                            <a href="tel:+507<?= str_replace('-', '', $sucursal['phone']) ?>" class="hover:text-primary transition">
                                <?= sanitize($sucursal['phone']) ?>
                            </a>
                        </div>
                    </div>

                    <a href="https://maps.google.com/?q=<?= urlencode($sucursal['name'] . ', ' . $sucursal['address'] . ', Panamá') ?>" target="_blank"
                       class="mt-6 block w-full text-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                        <i class="fas fa-directions mr-2"></i>Cómo Llegar
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Map Section -->
<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-8">Ubicación Casa Matriz</h2>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.494834722552!2d-79.51702492473893!3d9.018082191038073!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8faca8f1dbe80363%3A0xb2c1e6e8f6c8b8e8!2sPanam%C3%A1!5e0!3m2!1ses!2spa!4v1234567890123!5m2!1ses!2spa"
                width="100%" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main_tailwind.php';
?>
