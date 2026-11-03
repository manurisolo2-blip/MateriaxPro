<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Conversación | MateriaX']) ?>

<div style="max-width: 900px; margin: 0 auto 3rem;">

  <!-- Barra de navegación y título -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <a href="<?= site_url('mensajes') ?>" class="btn btn-secondary btn-sm" style="font-size: 0.85rem;">
        &larr; Volver a Mensajes
      </a>
      <div>
        <h1 style="font-size: 1.45rem; font-weight: 700; color: var(--color-ink); margin: 0;">
          💬 <?= esc($otraEmpresa['nombre']) ?>
        </h1>
        <div style="font-size: 0.85rem; color: var(--color-ink-2); margin-top: 0.15rem;">
          <?php if ($tipo === 'transaccion' && !empty($transaccion)): ?>
            Operación <strong><?= esc($transaccion['codigo_operacion']) ?></strong> &bull; Lote: <strong><?= esc($transaccion['producto_nombre'] ?? 'Polímero') ?></strong> (<?= esc($transaccion['tipo_polimero'] ?? '') ?>)
          <?php elseif ($tipo === 'producto' && !empty($producto)): ?>
            Consulta previa sobre <strong><?= esc($producto['nombre']) ?></strong> (<?= esc($producto['tipo_polimero']) ?>)
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center;">
      <?php if ($tipo === 'transaccion' && !empty($transaccion)): ?>
        <a href="<?= site_url('solicitudes/ver/' . $transaccion['id']) ?>" class="btn btn-secondary btn-sm">
          Ver Ficha de Solicitud &rarr;
        </a>
      <?php elseif ($tipo === 'producto' && !empty($producto)): ?>
        <a href="<?= site_url('productos/ver/' . $producto['id']) ?>" class="btn btn-secondary btn-sm">
          Ver Ficha del Lote &rarr;
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tarjeta informativa del contexto comercial -->
  <div class="card" style="padding: 1rem 1.25rem; margin-bottom: 1.25rem; background-color: var(--color-paper-2); border: 1px solid var(--color-rule);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; font-size: 0.88rem;">
      <?php if ($tipo === 'transaccion' && !empty($transaccion)): ?>
        <div>
          <span style="color: var(--color-ink-2);">Cantidad pactada:</span>
          <strong style="color: var(--color-ink);"><?= number_format($transaccion['cantidad_kg'], 0, ',', '.') ?> kg</strong>
          &bull;
          <span style="color: var(--color-ink-2);">Total:</span>
          <strong style="color: var(--color-success-text);"><?= format_precio($transaccion['monto_total']) ?></strong>
        </div>
        <span class="badge <?= badge_transaccion_class($transaccion['estado']) ?>">
          <?= label_transaccion_estado($transaccion['estado']) ?>
        </span>
      <?php elseif ($tipo === 'producto' && !empty($producto)): ?>
        <div>
          <span style="color: var(--color-ink-2);">Volumen disponible:</span>
          <strong style="color: var(--color-ink);"><?= number_format($producto['cantidad_kg'], 0, ',', '.') ?> kg</strong>
          &bull;
          <span style="color: var(--color-ink-2);">Precio unitario:</span>
          <strong style="color: var(--color-ink);"><?= format_precio($producto['precio_unitario']) ?>/kg</strong>
          &bull;
          <span style="color: var(--color-ink-2);">Ubicación:</span>
          <strong><?= esc($producto['ubicacion']) ?></strong>
        </div>
        <span class="badge <?= badge_polimero_class($producto['tipo_polimero']) ?>">
          <?= esc($producto['tipo_polimero']) ?>
        </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Contenedor del Chat en Tiempo Real -->
  <div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 0.65rem;">
        <h3 class="card-title" style="margin: 0; font-size: 1.05rem;">
          Historial de Conversación
        </h3>
        <span id="chatMsgCount" style="font-size: 0.8rem; color: var(--color-ink-2);">
          <?= count($mensajes ?? []) ?> mensaje(s)
        </span>
      </div>
      <span style="font-size: 0.82rem; color: var(--color-success-text); display: flex; align-items: center; gap: 0.35rem;">
        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: var(--color-success-text);"></span>
        Sincronización en vivo activa
      </span>
    </div>

    <div class="card-body">
      <!-- Hilo de mensajes -->
      <div 
        id="chatContainer" 
        class="chat-thread" 
        data-transaccion-id="<?= $tipo === 'transaccion' ? esc($transaccionId ?? '') : '' ?>" 
        data-producto-id="<?= esc($productoId ?? '') ?>" 
        data-receptor-id="<?= esc($receptorId ?? '') ?>" 
        data-user-id="<?= esc($userId) ?>" 
        data-base-url="<?= site_url('/') ?>" 
        style="display: flex; flex-direction: column; gap: 0.85rem; max-height: 460px; overflow-y: auto; padding: 1.25rem; background-color: var(--color-paper-2); border: 1px solid var(--color-rule); border-radius: var(--radius-sm); margin-bottom: 1.25rem;"
      >
        <?php if (!empty($mensajes)): ?>
          <?php foreach ($mensajes as $msg): ?>
            <?php $esMio = ((int)$msg['emisor_id'] === $userId); ?>
            <div id="msg-<?= $msg['id'] ?>" data-msg-id="<?= $msg['id'] ?>" class="chat-bubble-row" style="display: flex; flex-direction: column; align-items: <?= $esMio ? 'flex-end' : 'flex-start' ?>;">
              <div style="font-size: 0.75rem; color: var(--color-ink-2); margin-bottom: 0.2rem; padding: 0 0.25rem; text-transform: uppercase; font-weight: 600;">
                <?= $esMio ? 'Tú' : esc($msg['emisor_nombre']) ?>
              </div>
              <div class="chat-bubble-box" style="max-width: 80%; padding: 0.75rem 1rem; border-radius: 12px; font-size: 0.92rem; line-height: 1.5; word-break: break-word; <?= $esMio ? 'background-color: var(--color-primary); color: #ffffff; border-bottom-right-radius: 2px;' : 'background-color: var(--color-paper-surface); color: var(--color-ink); border: 1px solid var(--color-rule); border-bottom-left-radius: 2px;' ?>">
                <div class="chat-bubble-text"><?= nl2br(esc($msg['mensaje'])) ?></div>
                <div class="chat-bubble-meta" style="font-size: 0.7rem; color: <?= $esMio ? 'rgba(255,255,255,0.82)' : 'var(--color-ink-2)' ?>; text-align: right; margin-top: 0.35rem;">
                  <span class="msg-time"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
                  <?php if ($esMio): ?>
                    <span class="msg-status-tick" style="margin-left: 0.25rem;"><?= $msg['leido'] ? '&check;&check; Leído' : '&check; Enviado' ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div id="emptyChatPlaceholder" style="text-align: center; padding: 3rem 1rem; color: var(--color-ink-2); font-size: 0.92rem;">
            Aún no hay mensajes en esta conversación. Escribe el primero a continuación.
          </div>
        <?php endif; ?>
      </div>

      <!-- Formulario para enviar mensaje -->
      <form id="chatForm" action="<?= site_url('mensajes/enviar') ?>" method="POST" style="margin: 0;">
        <?= csrf_field() ?>
        <?php if (!empty($transaccionId)): ?>
          <input type="hidden" name="transaccion_id" value="<?= esc($transaccionId) ?>">
        <?php endif; ?>
        <input type="hidden" name="producto_id" value="<?= esc($productoId) ?>">
        <input type="hidden" name="receptor_id" value="<?= esc($receptorId) ?>">

        <div class="form-group" style="margin-bottom: 0.75rem;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
            <label for="chatInput" class="form-label" style="font-size: 0.88rem; margin: 0;">
              Enviar mensaje a <strong><?= esc($otraEmpresa['nombre']) ?></strong>:
            </label>
            <span style="font-size: 0.75rem; color: var(--color-ink-2);">
              Presiona <kbd style="background: var(--color-paper-surface); border: 1px solid var(--color-rule); padding: 1px 4px; border-radius: 4px;">Enter</kbd> para envío instantáneo
            </span>
          </div>
          <textarea 
            name="mensaje" 
            id="chatInput" 
            rows="2" 
            class="form-control" 
            placeholder="Escribe tu mensaje aquí y presiona Enter..." 
            required 
            minlength="1" 
            maxlength="2000"
            autofocus
            style="resize: vertical;"
          ></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end;">
          <button type="submit" class="btn btn-primary">
            Enviar Mensaje &rarr;
          </button>
        </div>
      </form>
    </div>
  </div>

</div>

<script src="<?= base_url('assets/chat.js') ?>?v=2.4"></script>

<?= view('templates/footer') ?>
