<?= view('templates/header', ['pageTitle' => 'Panel de Administración y Auditoría | MateriaX']) ?>

<div class="container-lg stack-md">
    <div class="page-header flex-between">
        <div>
            <h1 class="page-title">Auditoría y Gestión de la Red MateriaX</h1>
            <p class="page-sub">Revisa solicitudes de registro empresarial, audita CUITs y supervisa los lotes de polímeros.</p>
        </div>
        <div class="flex-row">
            <a href="<?= site_url('admin/lotes') ?>" class="btn btn-secondary">Supervisar Lotes</a>
            <a href="<?= site_url('productos') ?>" class="btn btn-secondary">Inventario General</a>
        </div>
    </div>

    <div class="grid-4">
        <div class="metric-card">
            <span class="metric-card-label">Auditorías Pendientes</span>
            <div class="metric-card-value"><?= $totalPendientes ?></div>
            <span class="metric-card-sub"><?= ($totalPendientes > 0) ? 'Requieren aprobación' : 'Sin solicitudes pendientes' ?></span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Empresas Habilitadas</span>
            <div class="metric-card-value"><?= $empresasActivas ?></div>
            <span class="metric-card-sub">Cuentas operando en la red</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Lotes Publicados</span>
            <div class="metric-card-value"><?= $totalLotes ?></div>
            <span class="metric-card-sub">Inventario circular activo</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Volumen Total en Red</span>
            <div class="metric-card-value"><?= format_kg($totalKg) ?></div>
            <span class="metric-card-sub">Materiales reciclables</span>
        </div>
    </div>

    <div class="card">
        <div class="card-header flex-between">
            <div>
                <h2 class="card-title">Solicitudes de Registro en Auditoría</h2>
                <p class="text-sec">Empresas esperando verificación de personería y datos fiscales.</p>
            </div>
            <span class="badge badge-warning"><?= $totalPendientes ?> pendientes de aprobación</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th><th>Empresa Solicitante</th><th>CUIT</th><th>Contacto</th>
                        <th>Rubro y Ubicación</th><th>Fecha Solicitud</th><th>Estado</th><th style="text-align:right;">Decisión</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($empresasPendientes)): ?>
                        <?php foreach ($empresasPendientes as $ep): ?>
                            <tr>
                                <td class="text-muted text-mono">#<?= esc($ep['id']) ?></td>
                                <td><strong><?= esc($ep['nombre']) ?></strong><div class="text-muted"><?= esc($ep['email']) ?></div></td>
                                <td><span class="badge badge-warning text-mono"><?= esc($ep['cuit']) ?></span></td>
                                <td class="text-sec"><div><?= esc($ep['telefono']) ?></div><div class="text-muted"><?= esc($ep['direccion'] ?? 'Sin dirección') ?></div></td>
                                <td><div><strong><?= esc($ep['rubro'] ?? 'No especificado') ?></strong></div><div class="text-muted"><?= esc($ep['ciudad'] ?? '-') ?>, <?= esc($ep['provincia'] ?? '-') ?></div></td>
                                <td class="text-sec"><?= date('d/m/Y H:i', strtotime($ep['created_at'])) ?></td>
                                <td><span class="badge badge-warning">En Auditoría</span></td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a href="<?= site_url('admin/empresa/' . $ep['id']) ?>" class="btn btn-secondary btn-sm">Ver Ficha</a>
                                    <form action="<?= site_url('admin/empresa/aprobar/' . $ep['id']) ?>" method="POST" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-primary btn-sm">Aprobar</button>
                                    </form>
                                    <form action="<?= site_url('admin/empresa/rechazar/' . $ep['id']) ?>" method="POST" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger btn-sm">Rechazar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">No hay empresas pendientes de auditoría en este momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header flex-between">
            <div>
                <h2 class="card-title">Empresas Procesadas en la Red</h2>
                <p class="text-sec">Historial de empresas aprobadas, suspendidas o rechazadas.</p>
            </div>
            <span class="badge badge-info"><?= count($empresasAuditadas) ?> empresas procesadas</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th><th>Empresa / Razón Social</th><th>CUIT</th><th>Contacto</th>
                        <th>Rubro y Ubicación</th><th>Lotes</th><th>Último Login</th><th>Estado</th><th style="text-align:right;">Moderación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($empresasAuditadas)): ?>
                        <?php foreach ($empresasAuditadas as $empresa): ?>
                            <tr>
                                <td class="text-muted text-mono">#<?= esc($empresa['id']) ?></td>
                                <td><strong><?= esc($empresa['nombre']) ?></strong><div class="text-muted"><?= esc($empresa['email']) ?></div></td>
                                <td class="text-mono"><?= esc($empresa['cuit']) ?></td>
                                <td class="text-sec"><div><?= esc($empresa['telefono']) ?></div><div class="text-muted"><?= esc($empresa['direccion'] ?? 'Sin dirección') ?></div></td>
                                <td><div><?= esc($empresa['rubro'] ?? 'No especificado') ?></div><div class="text-muted"><?= esc($empresa['ciudad'] ?? '-') ?>, <?= esc($empresa['provincia'] ?? '-') ?></div></td>
                                <td><span class="badge badge-secondary"><?= (int) $empresa['total_lotes'] ?></span></td>
                                <td class="text-sec"><?= !empty($empresa['ultimo_login']) ? date('d/m/Y H:i', strtotime($empresa['ultimo_login'])) : 'Nunca ingresó' ?></td>
                                <td>
                                    <?php if ($empresa['estado'] === 'activo'): ?>
                                        <span class="badge badge-success">Habilitada</span>
                                    <?php elseif ($empresa['estado'] === 'rechazado'): ?>
                                        <span class="badge badge-danger">Solicitud Rechazada</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Suspendida</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a href="<?= site_url('admin/empresa/' . $empresa['id']) ?>" class="btn btn-secondary btn-sm">Ver</a>
                                    <?php if ($empresa['estado'] === 'activo' || $empresa['estado'] === 'inactivo'): ?>
                                        <form action="<?= site_url('admin/empresa/cambiar-estado/' . $empresa['id']) ?>" method="POST" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-secondary btn-sm"><?= ($empresa['estado'] === 'activo') ? 'Pausar' : 'Reactivar' ?></button>
                                        </form>
                                    <?php elseif ($empresa['estado'] === 'rechazado'): ?>
                                        <form action="<?= site_url('admin/empresa/aprobar/' . $empresa['id']) ?>" method="POST" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-secondary btn-sm">Aprobar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="empty-state">No hay empresas procesadas registradas aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>
