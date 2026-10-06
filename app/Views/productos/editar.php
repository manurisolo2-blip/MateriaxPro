<?= view('templates/header', ['pageTitle' => 'Editar Lote #' . $producto['id'] . ' | MateriaX']) ?>

<div style="max-width: 760px; margin: 1rem auto;">
  <div style="margin-bottom: 1.25rem;">
    <a href="<?= site_url('productos') ?>" style="color: var(--text-secondary); font-size: 0.9rem;">
      &larr; Volver al inventario de polímeros
    </a>
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--text-primary); margin-top: 0.4rem;">
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
