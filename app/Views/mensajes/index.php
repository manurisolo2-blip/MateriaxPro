<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Bandeja de Mensajes | MateriaX']) ?>

<div id="inboxContainer" data-base-url="<?= site_url('/') ?>" data-send-url="<?= site_url('mensajes/enviar') ?>" style="max-width: 950px; margin: 0 auto 3rem;">

  <!-- Encabezado de sección -->
  <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
    <div>
      <h1 style="font-size: 1.9rem; font-weight: 700; color: var(--color-ink); margin: 0 0 0.35rem 0;">
        Bandeja de Mensajes y Consultas B2B
      </h1>
      <p style="color: var(--color-ink-2); font-size: 0.95rem; margin: 0;">
        Canal directo de comunicación entre empresas en tiempo real. Puedes leer y responder al instante desde aquí o abrir la sala completa.
      </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center;">
      <a href="<?= site_url('solicitudes') ?>" class="btn btn-secondary btn-sm">
        Ver Solicitudes
      </a>
      <a href="<?= site_url('productos') ?>" class="btn btn-primary btn-sm">
        Explorar Mercado
      </a>
    </div>
  </div>

  <!-- Tarjetas de resumen -->
  <div class="grid-3" style="margin-bottom: 2rem;">
    <div class="metric-card">
      <span class="metric-card-label">Conversaciones Activas</span>
      <div id="metricTotalConversaciones" class="metric-card-value">
        <?= count($conversaciones) ?>
      </div>
      <span class="metric-card-sub">Hilos de negociación abiertos</span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Mensajes Pendientes de Lectura</span>
      <div id="metricTotalNoLeidos" class="metric-card-value" style="color: <?= $totalNoLeidos > 0 ? 'var(--color-primary)' : 'var(--color-ink-2)' ?>;">
        <?= $totalNoLeidos ?>
      </div>
      <span class="metric-card-sub">Sincronización automática activa</span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Estado del Canal</span>
      <div class="metric-card-value" style="font-size: 1.15rem; font-weight: 600; color: var(--color-success-text); display: flex; align-items: center; gap: 0.4rem;">
        <span style="display: inline-block; width: 9px; height: 9px; border-radius: 50%; background-color: var(--color-success-text);"></span>
        En Vivo
      </div>
      <span class="metric-card-sub">Sin necesidad de recargar página</span>
    </div>
  </div>

  <!-- Listado de conversaciones -->
  <div id="inboxList" style="display: flex; flex-direction: column; gap: 0.95rem;">
    <?php if (!empty($conversaciones)): ?>
      <?php foreach ($conversaciones as $conv): ?>
        <?php 
          $tieneNoLeidos = ($conv['no_leidos'] > 0);
          $linkDestino   = !empty($conv['transaccion_id']) 
            ? site_url('mensajes/chat/transaccion/' . $conv['transaccion_id']) 
            : site_url('mensajes/chat/producto/' . $conv['producto_id'] . '/' . $conv['otra_empresa_id']);
          $recientes     = $conv['mensajes_recientes'] ?? [];
          $totalMsgs     = (int) ($conv['total_mensajes'] ?? count($recientes));
        ?>
        <div class="card inbox-conv-card" data-conv-key="<?= esc($conv['clave']) ?>" style="padding: 1.25rem 1.5rem; transition: transform 0.15s ease; <?= $tieneNoLeidos ? 'border-left: 4px solid var(--color-primary); background-color: var(--color-paper-surface);' : '' ?>">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 260px;">
              <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-ink); margin: 0;">
                  <?= esc($conv['otra_empresa_nombre']) ?>
                </h3>

                <?php if (!empty($conv['codigo_operacion'])): ?>
                  <span class="badge <?= badge_transaccion_class($conv['transaccion_estado'] ?? 'solicitada') ?>" style="font-size: 0.78rem;">
                    <?= esc($conv['codigo_operacion']) ?>
                  </span>
                <?php endif; ?>

                <span class="badge badge-success inbox-unread-badge" style="font-size: 0.78rem; <?= $tieneNoLeidos ? '' : 'display: none;' ?>">
                  <?= $conv['no_leidos'] ?> nuevo(s)
                </span>

                <span class="inbox-total-count" style="font-size: 0.78rem; color: var(--color-ink-2);">
                  &bull; <?= $totalMsgs ?> mensaje(s)
                </span>
              </div>

              <div style="font-size: 0.88rem; color: var(--color-ink-2); margin-bottom: 0.65rem;">
                Lote vinculado: <strong><?= esc($conv['producto_nombre']) ?></strong>
                <?php if (!empty($conv['tipo_polimero'])): ?>
                  <span class="badge <?= badge_polimero_class($conv['tipo_polimero']) ?>" style="font-size: 0.72rem; margin-left: 0.35rem;">
                    <?= esc($conv['tipo_polimero']) ?>
                  </span>
                <?php endif; ?>
              </div>

              <!-- Historial reciente en vivo dentro de la bandeja -->
              <div class="inbox-recent-messages inbox-last-msg" style="display: flex; flex-direction: column; gap: 0.4rem; background-color: var(--color-paper-2); padding: 0.75rem 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--color-rule); max-width: 680px; margin-bottom: 0.75rem; max-height: 185px; overflow-y: auto;">
                <?php if (!empty($recientes)): ?>
                  <?php foreach ($recientes as $rm): ?>
                    <div style="font-size: 0.88rem; line-height: 1.4; display: flex; justify-content: space-between; gap: 0.75rem; padding: 0.25rem 0.45rem; border-radius: 6px; <?= !empty($rm['es_mio']) ? 'background-color: rgba(25, 95, 65, 0.08);' : 'background-color: var(--color-paper-surface); border: 1px solid var(--color-rule);' ?>">
                      <div style="word-break: break-word;">
                        <strong style="color: <?= !empty($rm['es_mio']) ? 'var(--color-primary)' : 'var(--color-ink)' ?>; font-size: 0.8rem;">
                          <?= !empty($rm['es_mio']) ? 'Tú:' : esc($rm['emisor_nombre']) . ':' ?>
                        </strong>
                        <span style="color: var(--color-ink);"><?= esc($rm['mensaje']) ?></span>
                      </div>
                      <span style="font-size: 0.72rem; color: var(--color-ink-2); white-space: nowrap; flex-shrink: 0;">
                        <?= esc($rm['fecha'] ?? '') ?>
                      </span>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div style="font-size: 0.88rem; color: var(--color-ink);">
                    <strong style="color: var(--color-ink-2); font-size: 0.8rem;">
                      <?= ((int)$conv['ultimo_emisor_id'] === (int)session()->get('user_id')) ? 'Tú: ' : esc($conv['otra_empresa_nombre']) . ': ' ?>
                    </strong>
                    <?= esc($conv['ultimo_mensaje']) ?>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Respuesta rápida directa sin salir de la bandeja -->
              <form class="quick-reply-form" action="<?= site_url('mensajes/enviar') ?>" method="POST" style="display: flex; gap: 0.5rem; align-items: center; max-width: 680px; margin: 0;">
                <?= csrf_field() ?>
                <?php if (!empty($conv['transaccion_id'])): ?>
                  <input type="hidden" name="transaccion_id" value="<?= esc($conv['transaccion_id']) ?>">
                <?php endif; ?>
                <input type="hidden" name="producto_id" value="<?= esc($conv['producto_id']) ?>">
                <input type="hidden" name="receptor_id" value="<?= esc($conv['otra_empresa_id']) ?>">
                <input 
                  type="text" 
                  name="mensaje" 
                  class="form-control quick-reply-input" 
                  placeholder="Respuesta rápida a <?= esc($conv['otra_empresa_nombre']) ?> (Enter para enviar)..." 
                  required 
                  minlength="1" 
                  maxlength="2000"
                  style="flex: 1; padding: 0.45rem 0.75rem; font-size: 0.86rem;"
                >
                <button type="submit" class="btn btn-secondary btn-sm" style="white-space: nowrap;">
                  Enviar
                </button>
                <span class="quick-reply-status" style="font-size: 0.78rem; font-weight: 600; white-space: nowrap;"></span>
              </form>
            </div>

            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem;">
              <span class="inbox-last-time" style="font-size: 0.8rem; color: var(--color-ink-2);">
                <?= date('d/m/Y H:i', strtotime($conv['ultima_fecha'])) ?>
              </span>
              <a href="<?= $linkDestino ?>" class="btn <?= $tieneNoLeidos ? 'btn-primary' : 'btn-secondary' ?> btn-sm inbox-open-btn">
                Abrir Conversación &rarr;
              </a>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div id="emptyInboxPlaceholder" class="card" style="text-align: center; padding: 3.5rem 1.5rem;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-ink); margin-bottom: 0.5rem;">
          No tienes mensajes ni consultas activas
        </h3>
        <p style="color: var(--color-ink-2); max-width: 480px; margin: 0 auto 1.5rem; font-size: 0.92rem; line-height: 1.5;">
          Cuando inicies o recibas una consulta sobre un lote de polímero o una solicitud de compra, aparecerá aquí en tiempo real sin necesidad de recargar la página.
        </p>
        <a href="<?= site_url('productos') ?>" class="btn btn-primary">
          Explorar Mercado de Excedentes
        </a>
      </div>
    <?php endif; ?>
  </div>

</div>

<script src="<?= base_url('assets/chat.js') ?>?v=2.4"></script>

<?= view('templates/footer') ?>
