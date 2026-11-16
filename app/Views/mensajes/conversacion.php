<?= view('templates/header', ['pageTitle' => 'Conversación con ' . esc($otraEmpresa['nombre'] ?? 'Empresa') . ' | MateriaX']) ?>

<?php
$tid = $transaccion['id'] ?? null;
$pid = $producto['id'] ?? ($transaccion['producto_id'] ?? null);
$rid = $receptorId ?? ($otraEmpresa['id'] ?? 0);
?>

<div class="container-lg stack-md">
    <div class="card flex-between">
        <div class="stack-sm">
            <a href="<?= site_url('mensajes') ?>" class="text-sec">Volver a Bandeja de Mensajes</a>
            <h1 class="page-title"><?= esc($otraEmpresa['nombre'] ?? 'Empresa') ?></h1>
            <p class="text-muted">
                <?php if ($tid): ?>
                    Operación <?= esc($transaccion['codigo_operacion'] ?? ('#' . $tid)) ?>
                <?php elseif ($pid): ?>
                    Consulta sobre Lote #<?= esc($pid) ?> — <?= esc($producto['nombre'] ?? '') ?>
                <?php endif; ?>
            </p>
        </div>
        <div>
            <?php if ($tid): ?>
                <a href="<?= site_url('solicitudes/ver/' . $tid) ?>" class="btn btn-secondary">Ver Operación</a>
            <?php elseif ($pid): ?>
                <a href="<?= site_url('productos/ver/' . $pid) ?>" class="btn btn-secondary">Ver Lote</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card stack-sm">
        <div class="flex-between">
            <span><strong>CUIT:</strong> <span class="text-mono"><?= esc($otraEmpresa['cuit'] ?? '—') ?></span></span>
            <span><strong>Correo:</strong> <?= esc($otraEmpresa['email'] ?? '—') ?></span>
            <span><strong>Teléfono:</strong> <?= esc($otraEmpresa['telefono'] ?? '—') ?></span>
        </div>
    </div>

    <section class="card stack-md">
        <div class="flex-between">
            <div>
                <h2>Sala de Mensajería</h2>
                <span class="badge badge-success">Sincronización en vivo activa</span>
            </div>
            <span id="chatMsgCount" class="text-muted"><?= count($mensajes ?? []) ?> mensaje(s)</span>
        </div>
        <?= view('templates/chat_box', [
            'mensajes'      => $mensajes ?? [],
            'userId'        => $userId,
            'transaccionId' => $tid,
            'productoId'    => $pid,
            'receptorId'    => $rid,
        ]) ?>
    </section>
</div>

<script src="<?= base_url('assets/chat.js') ?>?v=2.2"></script>
<?= view('templates/footer') ?>
