<?= view('templates/header', ['pageTitle' => 'MateriaX — Mercado de Polímeros Industriales']) ?>

<?php
$lotesDemo = [
    ['id' => '#FIL-108', 'polimero' => 'PEAD', 'titulo' => 'PEAD Soplado Natural Post-Industrial', 'origen' => 'Córdoba', 'kg' => '8.500 kg', 'precio' => '$1.420/kg'],
    ['id' => '#ROT-204', 'polimero' => 'PP',   'titulo' => 'PP Homopolímero Recuperado Molido',     'origen' => 'Santa Fe', 'kg' => '12.000 kg', 'precio' => '$1.180/kg'],
    ['id' => '#MOD-019', 'polimero' => 'PETG', 'titulo' => 'PETG Cristal Lámina Virgen Excedente',   'origen' => 'Buenos Aires', 'kg' => '3.200 kg', 'precio' => '$2.350/kg'],
    ['id' => '#FIL-092', 'polimero' => 'PEBD', 'titulo' => 'PEBD Film Natural Pelletizado',         'origen' => 'San Luis', 'kg' => '6.000 kg', 'precio' => '$1.290/kg'],
];
?>

<section id="inicio" class="container-lg hero-section">
    <div class="hero-content">
        <h1 class="hero-heading">Excedentes y recuperados plásticos con trazabilidad verificada.</h1>
        <p class="hero-desc">Conectamos plantas generadoras e industrias transformadoras de Argentina con control de báscula, acuerdo directo y certificado de ahorro de emisiones ISO 14044.</p>
        <div class="hero-actions">
            <a href="<?= site_url('productos') ?>" class="btn btn-primary">Explorar Mercado</a>
            <a href="<?= site_url('register') ?>" class="btn btn-secondary">Registrar Empresa</a>
        </div>
    </div>
</section>

<section id="feed" class="container-lg">
    <div class="page-header flex-between">
        <div>
            <h2 class="page-title">Lotes de Referencia en Pizarra</h2>
            <p class="page-sub">Muestra representativa de partidas industriales verificadas en planta.</p>
        </div>
        <a href="<?= site_url('productos') ?>" class="btn btn-secondary">Ver todos los lotes</a>
    </div>
    <div class="exchange-feed-grid">
        <?php foreach ($lotesDemo as $lote): ?>
            <article class="card stack-sm">
                <div class="flex-between">
                    <span class="text-mono"><?= esc($lote['id']) ?></span>
                    <span class="badge <?= esc(badge_polimero_class($lote['polimero'])) ?>"><?= esc($lote['polimero']) ?></span>
                </div>
                <h3><?= esc($lote['titulo']) ?></h3>
                <p class="text-sec"><?= esc($lote['origen']) ?> · <?= esc($lote['kg']) ?></p>
                <div class="flex-between">
                    <strong class="text-mono"><?= esc($lote['precio']) ?></strong>
                    <a href="<?= site_url('productos') ?>" class="btn btn-secondary btn-sm">Consultar</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section id="contacto" class="container-lg">
    <div class="card flex-between">
        <div class="stack-sm">
            <h2>Contacto Técnico e Institucional</h2>
            <p class="text-sec">Soporte operativo, alta de plantas y consultas académicas — Instituto Técnico Río Tercero (6° B).</p>
            <p>Escribinos a <a href="mailto:contacto@materiax.com.ar">contacto@materiax.com.ar</a></p>
        </div>
        <a href="<?= site_url('register') ?>" class="btn btn-primary">Crear Cuenta Empresarial</a>
    </div>
</section>

<?= view('templates/footer') ?>
