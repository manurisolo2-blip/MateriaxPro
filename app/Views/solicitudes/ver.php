<?= view('templates/header', ['pageTitle' => 'Operación ' . esc($tx['codigo_operacion'] ?? '') . ' | MateriaX']) ?>

<?php
$estado = strtolower($tx['estado'] ?? 'solicitada');
$backUrl = !empty($esVendedor) ? site_url('solicitudes/recibidas') : site_url('solicitudes/enviadas');
?>

<div class="container-lg stack-md">
    <nav class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= $backUrl ?>"><?= !empty($esVendedor) ? 'Solicitudes Recibidas' : 'Solicitudes Enviadas' ?></a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page"><?= esc($tx['codigo_operacion']) ?></span>
    </nav>

    <div class="card flex-between">
        <div class="stack-sm">
            <div class="flex-row">
                <span class="badge <?= badge_transaccion_class($estado) ?>"><?= esc(label_transaccion_estado($estado)) ?></span>
                <span class="text-mono text-muted">Lote #<?= esc($tx['producto_id']) ?></span>
            </div>
            <h1 class="page-title">Operación <?= esc($tx['codigo_operacion']) ?></h1>
        </div>
        <div class="flex-row">
            <?php if (!empty($esVendedor) && $estado === 'solicitada'): ?>
                <form action="<?= site_url('solicitudes/responder/' . $tx['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="aceptar">
                    <button type="submit" class="btn btn-primary">Aceptar Operación</button>
                </form>
                <form action="<?= site_url('solicitudes/responder/' . $tx['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="rechazar">
                    <button type="submit" class="btn btn-danger">Rechazar</button>
                </form>
            <?php elseif (!empty($esComprador) && $estado === 'solicitada'): ?>
                <form action="<?= site_url('solicitudes/cancelar/' . $tx['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">Cancelar Solicitud</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($certificado)): ?>
        <div class="alert alert-success flex-between">
            <span>Certificado ambiental emitido: <strong><?= esc($certificado['codigo_certificado']) ?></strong></span>
            <a href="<?= site_url('certificados/ver/' . $certificado['codigo_certificado']) ?>" class="btn btn-primary btn-sm">Ver Certificado</a>
        </div>
    <?php endif; ?>

    <div class="grid-3">
        <div class="metric-card">
            <span class="metric-card-label">Cantidad Acordada</span>
            <div class="metric-card-value text-mono"><?= format_kg($tx['cantidad_kg']) ?></div>
            <span class="metric-card-sub"><?= esc($tx['tipo_polimero'] ?? '') ?></span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Precio Unitario Pactado</span>
            <div class="metric-card-value text-mono"><?= format_precio($tx['precio_unitario_pactado']) ?>/kg</div>
            <span class="metric-card-sub">Subtotal: <?= format_precio($tx['subtotal']) ?></span>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Monto Total</span>
            <div class="metric-card-value text-mono"><?= format_precio($tx['monto_total']) ?></div>
            <span class="metric-card-sub">Flete: <?= format_precio($tx['costo_flete'] ?? 0) ?></span>
        </div>
    </div>

    <div class="grid-2">
        <div class="card stack-sm">
            <h2>Vendedor</h2>
            <p><strong>Empresa:</strong> <?= esc($tx['vendedor_nombre'] ?? '—') ?></p>
            <p><strong>CUIT:</strong> <span class="text-mono"><?= esc($tx['vendedor_cuit'] ?? '—') ?></span></p>
            <p><strong>Correo:</strong> <?= esc($tx['vendedor_email'] ?? '—') ?></p>
            <p><strong>Teléfono:</strong> <?= esc($tx['vendedor_telefono'] ?? '—') ?></p>
        </div>
        <div class="card stack-sm">
            <h2>Comprador</h2>
            <p><strong>Empresa:</strong> <?= esc($tx['comprador_nombre'] ?? '—') ?></p>
            <p><strong>CUIT:</strong> <span class="text-mono"><?= esc($tx['comprador_cuit'] ?? '—') ?></span></p>
            <p><strong>Correo:</strong> <?= esc($tx['comprador_email'] ?? '—') ?></p>
            <p><strong>Teléfono:</strong> <?= esc($tx['comprador_telefono'] ?? '—') ?></p>
        </div>
    </div>

    <div class="card stack-sm">
        <h2>Logística y Acuerdo</h2>
        <p><strong>Modalidad de Retiro:</strong> <?= esc(label_modalidad_retiro($tx['modalidad_retiro'] ?? '')) ?></p>
        <p><strong>Fecha de Acuerdo:</strong> <?= !empty($tx['fecha_acuerdo']) ? date('d/m/Y H:i', strtotime($tx['fecha_acuerdo'])) : 'Pendiente de confirmación' ?></p>
        <?php if (!empty($tx['nombre_planta'])): ?>
            <p><strong>Planta Despachante:</strong> <?= esc($tx['nombre_planta']) ?></p>
        <?php endif; ?>
        <p><strong>Observaciones:</strong> <?= nl2br(esc($tx['observaciones'] ?? 'Sin observaciones registradas.')) ?></p>
    </div>

    <section id="chat" class="card stack-md">
        <div class="flex-between">
            <h2>Mensajería de la Operación</h2>
            <span id="chatMsgCount" class="text-muted"><?= count($mensajes ?? []) ?> mensaje(s)</span>
        </div>
        <?= view('templates/chat_box', [
            'mensajes'      => $mensajes ?? [],
            'userId'        => $userId,
            'transaccionId' => $tx['id'],
            'productoId'    => $tx['producto_id'],
            'receptorId'    => $receptorId,
            'placeholder'   => 'Coordine fechas de retiro, documentación o pesaje en báscula...',
            'emptyText'     => 'No hay mensajes en esta operación todavía.',
        ]) ?>
    </section>

    <div>
        <a href="<?= $backUrl ?>" class="btn btn-secondary">Volver al listado</a>
    </div>
</div>

<script src="<?= base_url('assets/chat.js') ?>?v=2.2"></script>
<?= view('templates/footer') ?>
