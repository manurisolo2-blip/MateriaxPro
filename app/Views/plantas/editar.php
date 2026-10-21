<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Editar Planta Industrial | MateriaX']) ?>

<!-- Migas de pan -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 760px; margin: 0 auto 1rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('plantas') ?>">Plantas Industriales</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Editar Planta</span>
</nav>

<div style="max-width: 760px; margin: 0 auto 2.5rem;">
  <div style="margin-bottom: 1.25rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--color-ink); margin-top: 0.2rem;">
      Editar Sede Fabril: <?= esc($planta['nombre_planta']) ?>
    </h1>
    <p style="color: var(--color-ink-2); font-size: 0.95rem;">
      Actualiza los datos técnicos, de báscula o de contacto para esta ubicación.
    </p>
  </div>

  <div class="card">
    <div class="card-body">
      <?= view('plantas/_form', [
        'action'  => site_url('plantas/actualizar/' . $planta['id']),
        'btnText' => 'Actualizar Datos',
        'planta'  => $planta,
      ]) ?>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
