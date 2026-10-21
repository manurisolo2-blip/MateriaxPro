<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Sedes Fabriles | MateriaX']) ?>

<!-- Migas de pan -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 1080px; margin: 0 auto 1.25rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('panel') ?>">Mi Panel</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Plantas Industriales</span>
</nav>

<div style="max-width: 1080px; margin: 0 auto 2.5rem;">
  
  <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <div>
      <h1 style="font-size: 1.8rem; font-weight: 700; color: var(--color-ink); margin: 0 0 0.35rem 0;">
        Plantas Industriales y Centros de Retiro
      </h1>
      <p style="color: var(--color-ink-2); font-size: 0.95rem; margin: 0;">
        Gestiona las sedes de acopio, balanzas y depósitos habilitados para la carga logística de tus lotes.
      </p>
    </div>

    <a href="<?= site_url('plantas/crear') ?>" class="btn btn-primary">
      + Registrar Nueva Planta
    </a>
  </div>

  <?php if (empty($plantas)): ?>
    <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
      <div style="max-width: 480px; margin: 0 auto;">
        <h2 style="font-size: 1.25rem; font-weight: 600; color: var(--color-ink); margin-bottom: 0.5rem;">
          Aún no tienes plantas registradas
        </h2>
        <p style="color: var(--color-ink-2); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1.5rem;">
          Registrar tu primera sede fabril te permitirá asociar tus lotes de polímeros directamente a una ubicación física con información logística para camiones y pesaje.
        </p>
        <a href="<?= site_url('plantas/crear') ?>" class="btn btn-primary">
          Registrar Mi Primera Planta
        </a>
      </div>
    </div>
  <?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem;">
      <?php foreach ($plantas as $planta): ?>
        <article class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
          <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.75rem;">
              <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-ink); margin: 0;">
                <?= esc($planta['nombre_planta']) ?>
              </h2>
              <?php if (!empty($planta['activa'])): ?>
                <span class="badge badge-success">Operativa</span>
              <?php else: ?>
                <span class="badge badge-danger">Inactiva</span>
              <?php endif; ?>
            </div>

            <div style="font-size: 0.88rem; color: var(--color-ink-2); line-height: 1.6; margin-bottom: 1rem;">
              <div><strong>Dirección:</strong> <?= esc($planta['direccion']) ?></div>
              <div><strong>Localidad:</strong> <?= esc($planta['localidad']) ?>, <?= esc($planta['provincia']) ?></div>
              <div><strong>Báscula de Camiones:</strong> <?= !empty($planta['posee_bascula']) ? 'Sí, homologada' : 'No dispone' ?></div>
              <div><strong>Carga Admitida:</strong> <?= esc($planta['capacidad_carga_camiones'] ?: 'Hasta 45 Ton') ?></div>
              <?php if (!empty($planta['contacto_responsable'])): ?>
                <div><strong>Responsable:</strong> <?= esc($planta['contacto_responsable']) ?></div>
              <?php endif; ?>
              <?php if (!empty($planta['telefono_planta'])): ?>
                <div><strong>Teléfono:</strong> <?= esc($planta['telefono_planta']) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div style="padding: 0.75rem 1.25rem; border-top: 1px solid var(--color-rule); display: flex; gap: 0.5rem; justify-content: flex-end; background-color: var(--color-paper-2);">
            <a href="<?= site_url('plantas/editar/' . $planta['id']) ?>" class="btn btn-secondary btn-sm">
              Editar
            </a>
            <form action="<?= site_url('plantas/cambiar-estado/' . $planta['id']) ?>" method="POST" style="margin: 0;">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-secondary btn-sm">
                <?= !empty($planta['activa']) ? 'Desactivar' : 'Reactivar' ?>
              </button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<?= view('templates/footer') ?>
