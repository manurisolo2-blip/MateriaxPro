<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Panel de Empresa | MateriaX']) ?>

<div class="container-lg stack-md">
    <div class="page-header flex-between">
        <div class="stack-sm">
            <div class="flex-row">
                <h1 class="page-title"><?= esc($user['nombre']) ?></h1>
                <span class="badge badge-success">Cuenta Habilitada</span>
            </div>
            <p class="page-sub">
                <strong>CUIT:</strong> <?= esc($user['cuit'] ?? 'N/D') ?> ·
                <strong>Rubro:</strong> <?= esc($user['rubro'] ?? 'Industrial') ?> ·
                <strong>Radicación:</strong> <?= esc($user['ciudad'] ?? 'Córdoba') ?>, <?= esc($user['provincia'] ?? 'Argentina') ?>
            </p>
        </div>
        <div class="flex-row">
            <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary">Publicar Lote</a>
            <a href="<?= site_url('solicitudes/recibidas') ?>" class="btn btn-secondary">Solicitudes<?= !empty($solicitudesPendientes) ? " ({$solicitudesPendientes} pendientes)" : '' ?></a>
            <a href="<?= site_url('plantas') ?>" class="btn btn-secondary">Mis Plantas (<?= $totalPlantas ?? 0 ?>)</a>
            <a href="<?= site_url('certificados') ?>" class="btn btn-secondary">Certificados Ambientales</a>
            <a href="<?= site_url('productos') ?>" class="btn btn-secondary">Mercado</a>
            <a href="<?= site_url('perfil') ?>" class="btn btn-secondary">Perfil</a>
        </div>
    </div>

    <?php if (!empty($solicitudesPendientes)): ?>
        <div class="alert alert-warning flex-between">
            <div>
                <strong>Tienes <?= $solicitudesPendientes ?> solicitud(es) de compra pendiente(s) de evaluación.</strong>
                <div class="text-sec">Compradores interesados en tus lotes esperan tu confirmación para coordinar el retiro.</div>
            </div>
            <a href="<?= site_url('solicitudes/recibidas') ?>" class="btn btn-primary btn-sm">Revisar Solicitudes Recibidas &rarr;</a>
        </div>
    <?php endif; ?>

    <div class="grid-4">
        <div class="metric-card">
            <span class="metric-card-label">Mis Lotes Publicados</span>
            <div class="metric-card-value"><?= $totalLotes ?></div>
            <span class="metric-card-sub"><?= $lotesDisponibles ?> disponibles en la red</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Kilos en Oferta</span>
            <div class="metric-card-value text-mono"><?= number_format($totalKilos, 0, ',', '.') ?> kg</div>
            <span class="metric-card-sub">Materia prima circular</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Valor en Oferta (Est.)</span>
            <div class="metric-card-value text-mono">$<?= number_format($valorEstimado, 0, ',', '.') ?></div>
            <span class="metric-card-sub">Valorización total de inventario</span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Sedes Industriales</span>
            <div class="metric-card-value"><?= $totalPlantas ?? 0 ?> Activa(s)</div>
            <span class="metric-card-sub"><a href="<?= site_url('plantas') ?>">Gestionar sedes</a></span>
        </div>
    </div>

    <div class="card">
        <div class="card-header flex-between">
            <div>
                <h3 class="card-title">Mis Publicaciones en MateriaX</h3>
                <p class="text-sec">Lotes registrados bajo la titularidad de tu empresa.</p>
            </div>
            <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary btn-sm">Publicar Lote</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Lote / Material</th><th>Polímero</th><th>Cantidad</th><th>Precio / Kg</th>
                        <th>Ubicación</th><th>Estado</th><th>Fecha</th><th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($misProductos)): ?>
                        <?php foreach ($misProductos as $p): ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('productos/ver/' . $p['id']) ?>"><strong><?= esc($p['nombre']) ?></strong></a>
                                    <div class="text-muted">ID #<?= esc($p['id']) ?></div>
                                </td>
                                <td><span class="badge badge-polimero <?= badge_polimero_class($p['tipo_polimero']) ?>"><?= esc($p['tipo_polimero']) ?></span></td>
                                <td><strong><?= format_kg($p['cantidad_kg']) ?></strong></td>
                                <td><?= format_precio($p['precio_unitario']) ?></td>
                                <td><?= esc($p['ubicacion']) ?></td>
                                <td><span class="badge <?= badge_estado_class($p['estado']) ?>"><?= esc($p['estado']) ?></span></td>
                                <td class="text-muted"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                                <td style="text-align:right;white-space:nowrap;">
                                    <a href="<?= site_url('productos/ver/' . $p['id']) ?>" class="btn btn-secondary btn-sm">Ver</a>
                                    <a href="<?= site_url('productos/editar/' . $p['id']) ?>" class="btn btn-secondary btn-sm">Editar</a>
                                    <a href="<?= site_url('productos/confirmar-eliminar/' . $p['id']) ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                <p class="empty-state-title">Aún no has publicado ningún lote de excedente industrial</p>
                                <p class="empty-state-desc">Comienza a circular tus mermas, descartes o granzas plásticas para conectar con compradores.</p>
                                <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary">Publicar Primer Lote de Material</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($lotesMercado)): ?>
        <div class="flex-between">
            <div>
                <h3>Oportunidades Recientes en la Red</h3>
                <p class="text-sec">Materiales industriales publicados por otras empresas registradas en MateriaX.</p>
            </div>
            <a href="<?= site_url('productos') ?>" class="btn btn-secondary btn-sm">Ver Todo el Inventario &rarr;</a>
        </div>
        <div class="grid-2">
            <?php foreach ($lotesMercado as $lm): ?>
                <article class="card stack-sm">
                    <div class="flex-between">
                        <span class="badge badge-polimero <?= badge_polimero_class($lm['tipo_polimero']) ?>"><?= esc($lm['tipo_polimero']) ?></span>
                        <span class="badge badge-success">Disponible</span>
                    </div>
                    <h4><a href="<?= site_url('productos/ver/' . $lm['id']) ?>"><?= esc($lm['nombre']) ?></a></h4>
                    <p class="text-sec">Ofertado por: <strong><?= esc($lm['empresa_nombre'] ?? 'Empresa Registrada') ?></strong><br>Ubicación: <?= esc($lm['ubicacion']) ?></p>
                    <div class="flex-between">
                        <strong class="text-mono"><?= format_kg($lm['cantidad_kg']) ?> · <?= format_precio($lm['precio_unitario']) ?>/kg</strong>
                        <a href="<?= site_url('productos/ver/' . $lm['id']) ?>" class="btn btn-secondary btn-sm">Ver Ficha</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?= view('templates/footer') ?>
