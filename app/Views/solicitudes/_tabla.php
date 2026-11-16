<?php $recibidas = ($modo ?? 'recibidas') === 'recibidas'; ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $recibidas ? 'Bandeja de Solicitudes Recibidas' : 'Historial de Solicitudes de Adquisición' ?></h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Código Operación</th>
                    <th>Lote Solicitado</th>
                    <th><?= $recibidas ? 'Empresa Compradora' : 'Empresa Oferente' ?></th>
                    <th>Cantidad</th>
                    <th>Total Propuesto</th>
                    <th>Modalidad</th>
                    <th>Estado</th>
                    <th><?= $recibidas ? 'Fecha' : 'Fecha Envío' ?></th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($solicitudes)): ?>
                    <?php foreach ($solicitudes as $s): ?>
                        <?php
                        $parteNombre = $recibidas ? ($s['comprador_nombre'] ?? '') : ($s['vendedor_nombre'] ?? '');
                        $parteCuit   = $recibidas ? ($s['comprador_cuit'] ?? 'N/D') : ($s['vendedor_cuit'] ?? 'N/D');
                        ?>
                        <tr>
                            <td><a href="<?= site_url('solicitudes/ver/' . $s['id']) ?>"><strong><?= esc($s['codigo_operacion']) ?></strong></a></td>
                            <td>
                                <a href="<?= site_url('productos/ver/' . $s['producto_id']) ?>"><?= esc($s['producto_nombre']) ?></a>
                                <div class="text-muted"><?= esc($s['tipo_polimero']) ?></div>
                            </td>
                            <td>
                                <strong><?= esc($parteNombre) ?></strong>
                                <div class="text-muted">CUIT: <?= esc($parteCuit) ?></div>
                            </td>
                            <td><strong><?= format_kg($s['cantidad_kg']) ?></strong></td>
                            <td>
                                <strong><?= format_precio($s['monto_total']) ?></strong>
                                <div class="text-muted"><?= format_precio($s['precio_unitario_pactado']) ?>/kg</div>
                            </td>
                            <td class="text-sec"><?= label_modalidad_retiro($s['modalidad_retiro']) ?></td>
                            <td><span class="badge <?= badge_transaccion_class($s['estado']) ?>"><?= label_transaccion_estado($s['estado']) ?></span></td>
                            <td class="text-sec"><?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?></td>
                            <td style="text-align:right;white-space:nowrap;">
                                <a href="<?= site_url('solicitudes/ver/' . $s['id']) ?>" class="btn btn-secondary btn-sm">Ver</a>
                                <?php if ($s['estado'] === 'solicitada'): ?>
                                    <?php if ($recibidas): ?>
                                        <form action="<?= site_url('solicitudes/responder/' . $s['id']) ?>" method="POST" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="accion" value="aceptar">
                                            <button type="submit" class="btn btn-primary btn-sm">Aceptar</button>
                                        </form>
                                        <form action="<?= site_url('solicitudes/responder/' . $s['id']) ?>" method="POST" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="accion" value="rechazar">
                                            <button type="submit" class="btn btn-danger btn-sm">Rechazar</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= site_url('solicitudes/cancelar/' . $s['id']) ?>" method="POST" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger btn-sm">Cancelar</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="empty-state">
                            <p class="empty-state-title"><?= $recibidas ? 'No has recibido solicitudes de compra todavía' : 'Aún no has enviado ninguna solicitud de compra' ?></p>
                            <p class="empty-state-desc">
                                <?= $recibidas
                                    ? 'Cuando otra industria se interese en tus lotes publicados, su propuesta aparecerá aquí.'
                                    : 'Explora el catálogo de polímeros industriales disponibles y solicita lotes para tus procesos.' ?>
                            </p>
                            <a href="<?= site_url($recibidas ? 'productos/crear' : 'productos') ?>" class="btn btn-primary">
                                <?= $recibidas ? 'Publicar Nuevo Lote' : 'Explorar Mercado de Materiales' ?>
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
