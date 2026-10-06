<?= view('templates/header', ['pageTitle' => 'Publicar Excedente Industrial | MateriaX']) ?>

<div style="max-width: 760px; margin: 1rem auto;">
  <div style="margin-bottom: 1.25rem;">
    <a href="<?= site_url('productos') ?>" style="color: var(--text-secondary); font-size: 0.9rem;">
      &larr; Volver al inventario de polímeros
    </a>
    <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--text-primary); margin-top: 0.4rem;">
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
