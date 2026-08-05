<?php
ob_start();

$directAnswer = 'Para colgar algo pesado en pared de concreto, usa un anclaje mecánico '
    . '(de cuña o expansión) para cargas medias, o uno químico si necesitas máxima '
    . 'resistencia. La elección depende del peso a soportar y del diámetro del anclaje, '
    . 'no del tamaño del objeto.';

$metaDescription = $directAnswer;

$qaSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'QAPage',
    'mainEntity' => [
        '@type' => 'Question',
        'name' => '¿Qué anclaje debo usar para colgar algo pesado en una pared de concreto?',
        'text' => '¿Qué anclaje debo usar para colgar algo pesado en una pared de concreto?',
        'answerCount' => 1,
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $directAnswer,
        ],
    ],
];
?>

<script type="application/ld+json"><?= json_encode($qaSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<!-- Page Header -->
<div class="bg-secondary py-10">
    <div class="container mx-auto px-4">
        <nav class="text-sm mb-3 text-gray-400">
            <a href="<?= url('/') ?>" class="hover:text-accent transition">Inicio</a>
            <span class="mx-2">/</span>
            <a href="<?= url('/category/anclajes-y-fijaciones-07') ?>" class="hover:text-accent transition">Anclajes y Fijaciones</a>
        </nav>
        <h1 class="font-display text-3xl md:text-4xl font-bold text-white uppercase tracking-wide max-w-3xl">
            ¿Qué anclaje debo usar para colgar algo pesado en una pared de concreto?
        </h1>
    </div>
</div>

<div class="container mx-auto px-4 py-10 max-w-3xl">
    <!-- Direct answer: kept short and self-contained on purpose -->
    <p class="text-lg leading-relaxed text-secondary font-medium bg-gray-50 border-l-4 border-primary rounded-r-lg p-5 mb-10">
        <?= sanitize($directAnswer) ?>
    </p>

    <h2 class="font-display text-2xl font-bold text-secondary uppercase tracking-wide mb-4">
        Tipos de anclaje según el peso y la pared
    </h2>
    <div class="table-wrap overflow-x-auto mb-10">
        <table class="w-full border-collapse bg-white rounded-lg shadow-sm overflow-hidden">
            <thead>
                <tr class="bg-secondary text-white text-sm uppercase tracking-wide">
                    <th class="text-left px-4 py-3">Tipo de anclaje</th>
                    <th class="text-left px-4 py-3">Carga recomendada</th>
                    <th class="text-left px-4 py-3">Cuándo usarlo</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <tr class="border-b">
                    <td class="px-4 py-3 font-semibold">Anclaje de expansión / cuña</td>
                    <td class="px-4 py-3">Media a alta</td>
                    <td class="px-4 py-3">Concreto sólido, instalación rápida, la mayoría de repisas y soportes</td>
                </tr>
                <tr class="border-b">
                    <td class="px-4 py-3 font-semibold">Anclaje químico (resina)</td>
                    <td class="px-4 py-3">Alta a muy alta</td>
                    <td class="px-4 py-3">Cargas estructurales, cerca del borde de la pared, o concreto con fisuras</td>
                </tr>
                <tr>
                    <td class="px-4 py-3 font-semibold">Taco plástico + tornillo</td>
                    <td class="px-4 py-3">Ligera</td>
                    <td class="px-4 py-3">Cuadros, espejos livianos, objetos decorativos</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h2 class="font-display text-2xl font-bold text-secondary uppercase tracking-wide mb-4">
        3 puntos a verificar antes de comprar
    </h2>
    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-10">
        <li><strong>Peso total real</strong>, no solo el del objeto — incluye lo que vas a colgar de él.</li>
        <li><strong>Diámetro del anclaje</strong>, no el tamaño del hueco — un anclaje más grueso reparte mejor la carga.</li>
        <li><strong>Estado del concreto</strong> — un anclaje químico tolera mejor concreto fisurado o cercano al borde.</li>
    </ul>

    <div class="bg-gray-50 border border-gray-100 rounded-xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4 mb-10">
        <p class="text-secondary font-medium">Encuentra anclajes de expansión, químicos y tacos en nuestro catálogo.</p>
        <a href="<?= url('/category/anclajes-y-fijaciones-07') ?>"
           class="flex-shrink-0 inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition font-semibold whitespace-nowrap">
            Ver Anclajes y Fijaciones
        </a>
    </div>

    <p class="text-sm text-gray-400 border-t pt-4">
        Guía revisada por el equipo técnico de Fausto Salazar, S.A. — ferretería industrial con sucursales en
        Casa Matriz, Tocumen, Santiago, David y Chorrera.
    </p>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main_tailwind.php';
?>
