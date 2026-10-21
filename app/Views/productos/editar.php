<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Editar Lote | MateriaX']) ?>

<!-- Migas de pan -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 760px; margin: 0 auto 1rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('productos') ?>">Mercado</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('productos/ver/' . $producto['id']) ?>"><?= esc($producto['nombre']) ?></a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Editar</span>
</nav>

<div style="max-width: 760px; margin: 0 auto 2rem;">
  <div style="margin-bottom: 1.25rem;">
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--color-ink); margin-top: 0.2rem;">
      Editar Publicación de Lote
    </h1>
    <p style="color: var(--color-ink-2); font-size: 0.95rem;">
      Actualiza los datos técnicos, volumen disponible o condiciones de retiro del material.
    </p>
  </div>

  <div class="card">
    <div class="card-body">
      <?= view('productos/_form', [
        'action'   => site_url('productos/actualizar/' . $producto['id']),
        'btnText'  => 'Guardar Cambios',
        'producto' => $producto,
        'plantas'  => $plantas ?? [],
      ]) ?>
    </div>
  </div>
</div>

<?= view('templates/footer') ?>
