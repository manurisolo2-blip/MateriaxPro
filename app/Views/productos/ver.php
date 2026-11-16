<?= view('templates/header', ['pageTitle' => esc($producto['nombre'] ?? 'Detalle de Lote') . ' | MateriaX']) ?>

<?php
$totalLote = (float) ($producto['cantidad_kg'] ?? 0) * (float) ($producto['precio_unitario'] ?? 0);
?>

<div class="container-lg stack-md">
    <nav class="breadcrumb">
        <span class="breadcrumb-item"><a href="<?= site_url('productos') ?>">Mercado</a></span>
        <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
        <span class="breadcrumb-item" aria-current="page">Lote #<?= esc($producto['id']) ?></span>
    </nav>

    <div class="card flex-between">
        <div class="stack-sm">
            <div class="flex-row">
                <span class="badge badge-polimero <?= badge_polimero_class($producto['tipo_polimero'] ?? '') ?>"><?= esc($producto['tipo_polimero']) ?></span>
                <span class="badge <?= badge_estado_class($producto['estado'] ?? 'Disponible') ?>"><?= esc($producto['estado']) ?></span>
                <span class="text-muted">Publicado <?= date('d/m/Y', strtotime($producto['created_at'])) ?></span>
            </div>
            <h1 class="page-title"><?= esc($producto['nombre']) ?></h1>
        </div>
        <div class="flex-row">
            <?php if (!empty($esPropio) || !empty($esAdmin)): ?>
                <a href="<?= site_url('productos/editar/' . $producto['id']) ?>" class="btn btn-secondary">Editar Lote</a>
                <a href="<?= site_url('productos/confirmar-eliminar/' . $producto['id']) ?>" class="btn btn-danger">Eliminar</a>
            <?php elseif (($producto['estado'] ?? '') === 'Disponible'): ?>
                <a href="<?= site_url('solicitudes/crear/' . $producto['id']) ?>" class="btn btn-primary">Solicitar Compra</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid-3">
        <div class="metric-card">
            <span class="metric-card-label">Volumen Disponible</span>
            <div class="metric-card-value text-mono"><?= format_kg($producto['cantidad_kg']) ?></div>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Precio Unitario</span>
            <div class="metric-card-value text-mono"><?= format_precio($producto['precio_unitario']) ?>/kg</div>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Valor Estimado del Lote</span>
            <div class="metric-card-value text-mono">$<?= number_format($totalLote, 2, ',', '.') ?></div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card stack-sm">
            <h2>Especificaciones y Retiro</h2>
            <p><strong>Ubicación:</strong> <?= esc($producto['ubicacion'] ?: 'No especificada') ?></p>
            <?php if (!empty($producto['nombre_planta'])): ?>
                <p>
                    <strong>Planta de Origen:</strong> <?= esc($producto['nombre_planta']) ?>
                    <?= !empty($producto['planta_posee_bascula']) ? '<span class="badge badge-success">Con báscula</span>' : '' ?>
                </p>
                <p class="text-sec">
                    <?= esc(trim(($producto['planta_direccion'] ?? '') . ', ' . ($producto['planta_localidad'] ?? '') . ', ' . ($producto['planta_provincia'] ?? ''), ', ')) ?>
                </p>
            <?php endif; ?>
            <p><strong>Descripción Técnica:</strong><br><?= nl2br(esc($producto['descripcion'] ?: 'Sin especificaciones adicionales.')) ?></p>
        </div>

        <div class="card stack-sm">
            <h2>Empresa Vendedora</h2>
            <p><strong>Razón Social:</strong> <?= esc($producto['empresa_nombre'] ?? 'Empresa Verificada') ?></p>
            <p><strong>CUIT:</strong> <span class="text-mono"><?= esc($producto['empresa_cuit'] ?? '—') ?></span></p>
            <p><strong>Correo:</strong> <?= esc($producto['empresa_email'] ?? '—') ?></p>
            <p><strong>Teléfono:</strong> <?= esc($producto['empresa_telefono'] ?? '—') ?></p>
        </div>
    </div>

    <?php if (!empty($isLogged) && empty($esPropio)): ?>
        <section id="consultas" class="card stack-md">
            <div class="flex-between">
                <div>
                    <h2>Consultas Directas al Vendedor</h2>
                    <p class="text-muted"><span id="chatMsgCount"><?= count($mensajes ?? []) ?> mensaje(s)</span></p>
                </div>
                <a href="<?= site_url('mensajes/chat/producto/' . $producto['id'] . '/' . $producto['user_id']) ?>" class="btn btn-secondary btn-sm">Abrir Sala Completa</a>
            </div>
            <?= view('templates/chat_box', [
                'mensajes'      => $mensajes ?? [],
                'userId'        => $userId,
                'productoId'    => $producto['id'],
                'receptorId'    => $producto['user_id'],
                'placeholder'   => 'Consulte sobre formato de entrega, ficha técnica o retiro en planta...',
                'emptyText'     => 'Todavía no realizó consultas sobre este lote.',
            ]) ?>
        </section>
        <script src="<?= base_url('assets/chat.js') ?>?v=2.2"></script>
    <?php elseif (!empty($esPropio)): ?>
        <section id="consultas" class="card stack-md">
            <h2>Consultas Recibidas sobre este Lote</h2>
            <?php if (empty($consultasRecibidas)): ?>
                <p class="text-muted">Ninguna empresa interesada envió consultas sobre este lote por el momento.</p>
            <?php else: ?>
                <div class="stack-sm">
                    <?php foreach ($consultasRecibidas as $cons): ?>
                        <div class="flex-between card">
                            <div>
                                <strong><?= esc($cons['interesado_nombre'] ?? ('Empresa #' . $cons['interesado_id'])) ?></strong>
                                <p class="text-sec"><?= esc($cons['ultimo_mensaje'] ?? '') ?></p>
                            </div>
                            <a href="<?= site_url('mensajes/chat/producto/' . $producto['id'] . '/' . $cons['interesado_id']) ?>" class="btn btn-primary btn-sm">Abrir Chat (<?= (int) ($cons['total'] ?? 1) ?>)</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<?= view('templates/footer') ?>
