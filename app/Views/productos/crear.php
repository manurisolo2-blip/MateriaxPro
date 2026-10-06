<?= view('templates/header', ['pageTitle' => 'Publicar Excedente Industrial | MateriaX']) ?>

<!-- Migas de Pan (WCAG Breadcrumbs) -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 760px; margin: 0 auto 1rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('productos') ?>">Mercado</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Publicar Lote</span>
</nav>

<div style="max-width: 760px; margin: 0 auto 2rem;">
  <div style="margin-bottom: 1.25rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--text-primary); margin-top: 0.2rem;">
      Publicar Nuevo Lote de Material
    </h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem;">
      Ingresa los datos técnicos del excedente polimérico para ofrecerlo a industrias de la red.
    </p>
  </div>

  <div class="card">
    <div class="card-body">
      <?= view('productos/_form', [
        'action'  => site_url('productos/guardar'),
        'btnText' => 'Publicar Lote',
      ]) ?>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
