<?= view('templates/header', ['pageTitle' => 'Detalle de Empresa | MateriaX Admin']) ?>

<div class="container-lg stack-md">
    <nav aria-label="Migas de pan" class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item"><a href="<?= site_url('admin') ?>">Panel Admin</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Auditoría: <?= esc($empresa['nombre']) ?></span>
    </nav>

    <div class="page-header flex-between">
        <div>
            <h1 class="page-title"><?= esc($empresa['nombre']) ?></h1>
            <p class="page-sub">Auditoría de datos institucionales y monitoreo de materiales publicados.</p>
        </div>
        <div class="flex-row">
            <?php if ($empresa['estado'] === 'pendiente'): ?>
                <form action="<?= site_url('admin/empresa/aprobar/' . $empresa['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary">Aprobar Empresa</button>
                </form>
                <form action="<?= site_url('admin/empresa/rechazar/' . $empresa['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">Rechazar Solicitud</button>
                </form>
            <?php elseif ($empresa['estado'] === 'activo'): ?>
                <form action="<?= site_url('admin/empresa/cambiar-estado/' . $empresa['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">Suspender Acceso</button>
                </form>
            <?php elseif ($empresa['estado'] === 'inactivo'): ?>
                <form action="<?= site_url('admin/empresa/cambiar-estado/' . $empresa['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary">Reactivar Acceso</button>
                </form>
            <?php elseif ($empresa['estado'] === 'rechazado'): ?>
                <form action="<?= site_url('admin/empresa/aprobar/' . $empresa['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary">Reconsiderar y Aprobar</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Información Fiscal y de Contacto</h3></div>
        <div class="card-body grid-3">
            <div><span class="text-muted">Razón Social</span><br><strong><?= esc($empresa['nombre']) ?></strong></div>
            <div><span class="text-muted">CUIT</span><br><strong class="text-mono"><?= esc($empresa['cuit']) ?></strong></div>
            <div><span class="text-muted">Correo Electrónico</span><br><?= esc($empresa['email']) ?></div>
            <div><span class="text-muted">Teléfono</span><br><?= esc($empresa['telefono']) ?></div>
            <div><span class="text-muted">Rubro / Sector</span><br><?= esc($empresa['rubro'] ?? 'No especificado') ?></div>
            <div><span class="text-muted">Ubicación</span><br><?= esc($empresa['ciudad'] ?? '-') ?>, <?= esc($empresa['provincia'] ?? '-') ?></div>
            <div><span class="text-muted">Dirección / Planta</span><br><?= esc($empresa['direccion'] ?? 'Sin registrar') ?></div>
            <div>
                <span class="text-muted">Estado de la Cuenta</span><br>
                <?php if ($empresa['estado'] === 'pendiente'): ?>
                    <span class="badge badge-warning">En Auditoría (Pendiente)</span>
                <?php elseif ($empresa['estado'] === 'activo'): ?>
                    <span class="badge badge-success">Cuenta Habilitada</span>
                <?php elseif ($empresa['estado'] === 'rechazado'): ?>
                    <span class="badge badge-danger">Solicitud Rechazada</span>
                <?php else: ?>
                    <span class="badge badge-secondary">Cuenta Suspendida</span>
                <?php endif; ?>
            </div>
            <div><span class="text-muted">Registro / Último Login</span><br><?= date('d/m/Y H:i', strtotime($empresa['created_at'])) ?> · <?= !empty($empresa['ultimo_login']) ? date('d/m/Y H:i', strtotime($empresa['ultimo_login'])) : 'Sin login' ?></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header flex-between">
            <div>
                <h3 class="card-title">Lotes Publicados por <?= esc($empresa['nombre']) ?></h3>
                <p class="text-sec">Materiales y excedentes industriales asociados a esta cuenta.</p>
            </div>
            <span class="badge badge-secondary"><?= count($lotes) ?> lotes</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Lote</th><th>Tipo Polímero</th><th>Volumen (kg)</th><th>Precio Unit.</th>
                        <th>Ubicación</th><th>Estado</th><th>Fecha Publicación</th><th style="text-align:right;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lotes)): ?>
                        <?php foreach ($lotes as $lote): ?>
                            <tr>
                                <td><strong><?= esc($lote['nombre']) ?></strong></td>
                                <td><span class="badge badge-polimero <?= badge_polimero_class($lote['tipo_polimero']) ?>"><?= esc($lote['tipo_polimero']) ?></span></td>
                                <td><?= format_kg($lote['cantidad_kg']) ?></td>
                                <td><?= format_precio($lote['precio_unitario']) ?> /kg</td>
                                <td><?= esc($lote['ubicacion']) ?></td>
                                <td><span class="badge <?= badge_estado_class($lote['estado']) ?>"><?= esc($lote['estado']) ?></span></td>
                                <td class="text-sec"><?= date('d/m/Y', strtotime($lote['created_at'])) ?></td>
                                <td style="text-align:right;"><a href="<?= site_url('productos/ver/' . $lote['id']) ?>" class="btn btn-secondary btn-sm" target="_blank">Ver Ficha</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">Esta empresa aún no ha publicado lotes en el inventario.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
