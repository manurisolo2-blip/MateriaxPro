<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Sedes Fabriles | MateriaX']) ?>

<div class="container-lg stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('panel') ?>">Mi Panel</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Plantas Industriales</span>
    </nav>

    <div class="page-header flex-between">
        <div>
            <h1 class="page-title">Plantas Industriales y Centros de Retiro</h1>
            <p class="page-sub">Gestiona sedes de acopio, balanzas y depósitos habilitados para carga logística.</p>
        </div>
        <a href="<?= site_url('plantas/crear') ?>" class="btn btn-primary">+ Registrar Nueva Planta</a>
    </div>

    <?php if (empty($plantas)): ?>
        <div class="card empty-state">
            <h2 class="empty-state-title">Aún no tienes plantas registradas</h2>
            <p class="empty-state-desc">Registrar tu sede fabril permite asociar lotes a una ubicación física con información de pesaje y acceso.</p>
            <a href="<?= site_url('plantas/crear') ?>" class="btn btn-primary">Registrar Mi Primera Planta</a>
        </div>
    <?php else: ?>
        <div class="grid-3">
            <?php foreach ($plantas as $planta): ?>
                <article class="card stack-sm">
                    <div class="card-body stack-sm">
                        <div class="flex-between">
                            <h3><?= esc($planta['nombre_planta']) ?></h3>
                            <span class="badge <?= !empty($planta['activa']) ? 'badge-success' : 'badge-danger' ?>">
                                <?= !empty($planta['activa']) ? 'Operativa' : 'Inactiva' ?>
                            </span>
                        </div>
                        <div class="text-sec">
                            <div><strong>Dirección:</strong> <?= esc($planta['direccion']) ?></div>
                            <div><strong>Localidad:</strong> <?= esc($planta['localidad']) ?>, <?= esc($planta['provincia']) ?></div>
                            <div><strong>Báscula:</strong> <?= !empty($planta['posee_bascula']) ? 'Sí, homologada' : 'No dispone' ?></div>
                            <div><strong>Carga:</strong> <?= esc($planta['capacidad_carga_camiones'] ?: 'Hasta 45 Ton') ?></div>
                            <?php if (!empty($planta['contacto_responsable'])): ?>
                                <div><strong>Responsable:</strong> <?= esc($planta['contacto_responsable']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($planta['telefono_planta'])): ?>
                                <div><strong>Teléfono:</strong> <?= esc($planta['telefono_planta']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex-row">
                        <a href="<?= site_url('plantas/editar/' . $planta['id']) ?>" class="btn btn-secondary btn-sm">Editar</a>
                        <form action="<?= site_url('plantas/cambiar-estado/' . $planta['id']) ?>" method="POST">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-secondary btn-sm"><?= !empty($planta['activa']) ? 'Desactivar' : 'Reactivar' ?></button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?= view('templates/footer') ?>
