<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Registrar Nueva Planta Industrial | MateriaX']) ?>

<!-- Migas de pan -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 760px; margin: 0 auto 1rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('plantas') ?>">Plantas Industriales</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Nueva Planta</span>
</nav>

<div style="max-width: 760px; margin: 0 auto 2.5rem;">
  <div style="margin-bottom: 1.25rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--color-ink); margin-top: 0.2rem;">
      Registrar Nueva Planta Industrial
    </h1>
    <p style="color: var(--color-ink-2); font-size: 0.95rem;">
      Ingresa los datos del predio fabril o depósito para habilitar retiros logísticos de tus materiales.
    </p>
  </div>

  <div class="card">
    <div class="card-body">
      <?= view('plantas/_form', [
        'action'  => site_url('plantas/guardar'),
        'btnText' => 'Registrar Planta',
      ]) ?>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
