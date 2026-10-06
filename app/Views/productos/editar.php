<?= view('templates/header', ['pageTitle' => 'Editar Lote #' . $producto['id'] . ' | MateriaX']) ?>

<!-- Migas de Pan (WCAG Breadcrumbs) -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 760px; margin: 0 auto 1rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('productos') ?>">Mercado</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('productos/ver/' . $producto['id']) ?>">Lote #<?= esc($producto['id']) ?></a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Editar Lote</span>
</nav>

<div style="max-width: 760px; margin: 0 auto 2rem;">
  <div style="margin-bottom: 1.25rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--text-primary); margin-top: 0.2rem;">
      Editar Lote: <?= esc($producto['nombre']) ?>
    </h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem;">
      Modifica los datos técnicos, stock o precio del lote publicado.
    </p>
  </div>

  <div class="card">
    <div class="card-body">
      <?= view('productos/_form', [
        'action'   => site_url('productos/actualizar/' . $producto['id']),
        'producto' => $producto,
        'btnText'  => 'Guardar Cambios',
      ]) ?>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
