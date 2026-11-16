<?php
$tid = $transaccionId ?? '';
$pid = $productoId ?? '';
$rid = $receptorId ?? '';
$uid = (int) ($userId ?? 0);
$msgs = $mensajes ?? [];
?>
<div id="chatContainer" class="chat-thread"
     data-transaccion-id="<?= esc($tid) ?>"
     data-producto-id="<?= esc($pid) ?>"
     data-receptor-id="<?= esc($rid) ?>"
     data-user-id="<?= $uid ?>"
     data-base-url="<?= site_url('/') ?>">
    <?php if (empty($msgs)): ?>
        <p id="emptyChatPlaceholder" class="text-muted text-center"><?= esc($emptyText ?? 'Todavía no hay mensajes en esta conversación.') ?></p>
    <?php else: ?>
        <?php foreach ($msgs as $msg): ?>
            <?php $esMio = ((int) ($msg['emisor_id'] ?? 0) === $uid); ?>
            <div class="chat-bubble-row<?= $esMio ? ' is-own' : '' ?>" id="msg-<?= esc($msg['id']) ?>" data-msg-id="<?= esc($msg['id']) ?>">
                <div class="chat-bubble-sender"><?= esc($esMio ? 'Usted' : ($msg['emisor_nombre'] ?? 'Empresa')) ?></div>
                <div class="chat-bubble-box">
                    <div class="chat-bubble-text"><?= nl2br(esc($msg['mensaje'])) ?></div>
                    <div class="chat-bubble-meta">
                        <span class="msg-time"><?= date('d/m H:i', strtotime($msg['created_at'])) ?></span>
                        <?php if ($esMio): ?>
                            <span class="msg-status-tick"><?= !empty($msg['leido']) ? '&check;&check; Leído' : '&check; Enviado' ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<form id="chatForm" action="<?= site_url('mensajes/enviar') ?>" method="POST" class="chat-form-row">
    <?= csrf_field() ?>
    <?php if (!empty($tid)): ?><input type="hidden" name="transaccion_id" value="<?= esc($tid) ?>"><?php endif; ?>
    <?php if (!empty($pid)): ?><input type="hidden" name="producto_id" value="<?= esc($pid) ?>"><?php endif; ?>
    <input type="hidden" name="receptor_id" value="<?= esc($rid) ?>">
    <textarea id="chatInput" name="mensaje" rows="2" class="form-control form-textarea" placeholder="<?= esc($placeholder ?? 'Escriba su mensaje...') ?>" required minlength="1" maxlength="2000"></textarea>
    <button type="submit" class="btn btn-primary"><?= esc($submitLabel ?? 'Enviar') ?></button>
</form>
