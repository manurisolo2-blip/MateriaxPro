<?= view('templates/header', ['pageTitle' => 'Supervisión de Lotes | MateriaX Admin']) ?>

<div class="container-lg stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('admin') ?>">Panel Admin</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Supervisión de Lotes</span>
    </nav>

    <div class="page-header flex-between">
        <div>
            <h1 class="page-title">Supervisión Global de Lotes</h1>
            <p class="page-sub">Auditoría integral de publicaciones, excedentes y materiales industriales publicados.</p>
        </div>
        <a href="<?= site_url('admin') ?>" class="btn btn-secondary">Volver al Panel</a>
    </div>

    <div class="card">
        <div class="card-header flex-between">
            <div>
                <h2 class="card-title">Todos los Lotes Publicados en la Red</h2>
                <p class="text-sec">Listado consolidado con trazabilidad de la empresa oferente.</p>
            </div>
            <span class="badge badge-info"><?= count($lotes) ?> publicaciones</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th><th>Lote / Denominación</th><th>Empresa Oferente</th><th>Polímero</th>
                        <th>Volumen</th><th>Precio Unit.</th><th>Ubicación</th><th>Estado</th><th style="text-align:right;">Moderación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lotes)): ?>
                        <?php foreach ($lotes as $lote): ?>
                            <tr>
                                <td class="text-muted text-mono">#<?= esc($lote['id']) ?></td>
                                <td><strong><?= esc($lote['nombre']) ?></strong><div class="text-muted">Publicado: <?= date('d/m/Y', strtotime($lote['created_at'])) ?></div></td>
                                <td>
                                    <a href="<?= site_url('admin/empresa/' . $lote['user_id']) ?>"><strong><?= esc($lote['empresa_nombre'] ?? 'Empresa #' . $lote['user_id']) ?></strong></a>
                                    <div class="text-muted text-mono">CUIT: <?= esc($lote['empresa_cuit'] ?? '-') ?></div>
                                </td>
                                <td><span class="badge badge-polimero <?= badge_polimero_class($lote['tipo_polimero']) ?>"><?= esc($lote['tipo_polimero']) ?></span></td>
                                <td><strong><?= format_kg($lote['cantidad_kg']) ?></strong></td>
                                <td><?= format_precio($lote['precio_unitario']) ?> /kg</td>
                                <td class="text-sec"><?= esc($lote['ubicacion']) ?></td>
                                <td><span class="badge <?= badge_estado_class($lote['estado']) ?>"><?= esc($lote['estado']) ?></span></td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a href="<?= site_url('productos/ver/' . $lote['id']) ?>" class="btn btn-secondary btn-sm" target="_blank">Ver</a>
                                    <form action="<?= site_url('admin/lotes/eliminar/' . $lote['id']) ?>" method="POST" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger btn-sm">Dar de baja</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="empty-state">No hay lotes de polímeros publicados en la plataforma actualmente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
