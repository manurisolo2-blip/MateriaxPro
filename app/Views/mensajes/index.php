<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Bandeja de Mensajes | MateriaX']) ?>

<div style="max-width: 950px; margin: 0 auto 3rem;">

  <!-- Encabezado de sección -->
  <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
    <div>
      <h1 style="font-size: 1.9rem; font-weight: 700; color: var(--color-ink); margin: 0 0 0.35rem 0;">
        Bandeja de Mensajes y Consultas B2B
      </h1>
      <p style="color: var(--color-ink-2); font-size: 0.95rem; margin: 0;">
        Canal directo de comunicación entre empresas para coordinar retiros, fletes, especificaciones y acuerdos comerciales.
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
      <div class="metric-card-value">
        <?= count($conversaciones) ?>
      </div>
      <span class="metric-card-sub">Hilos de negociación abiertos</span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Mensajes Pendientes de Lectura</span>
      <div class="metric-card-value" style="color: <?= $totalNoLeidos > 0 ? 'var(--color-primary)' : 'var(--color-ink-2)' ?>;">
        <?= $totalNoLeidos ?>
      </div>
      <span class="metric-card-sub">Esperando tu respuesta</span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Privacidad y Seguridad</span>
      <div class="metric-card-value" style="font-size: 1.25rem; font-weight: 600; color: var(--color-ink);">
        Canal Privado
      </div>
      <span class="metric-card-sub">Exclusivo entre empresas autorizadas</span>
    </div>
  </div>

  <!-- Listado de conversaciones -->
  <?php if (!empty($conversaciones)): ?>
    <div style="display: flex; flex-direction: column; gap: 0.85rem;">
      <?php foreach ($conversaciones as $conv): ?>
        <?php 
          $tieneNoLeidos = ($conv['no_leidos'] > 0);
          $linkDestino   = !empty($conv['transaccion_id']) 
            ? site_url('mensajes/chat/transaccion/' . $conv['transaccion_id']) 
            : site_url('mensajes/chat/producto/' . $conv['producto_id'] . '/' . $conv['otra_empresa_id']);
        ?>
        <div class="card" style="padding: 1.25rem 1.5rem; transition: transform 0.15s ease; <?= $tieneNoLeidos ? 'border-left: 4px solid var(--color-primary); background-color: var(--color-paper-surface);' : '' ?>">
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

                <?php if ($tieneNoLeidos): ?>
                  <span class="badge badge-success" style="font-size: 0.78rem;">
                    <?= $conv['no_leidos'] ?> nuevo(s)
                  </span>
                <?php endif; ?>
              </div>

              <div style="font-size: 0.88rem; color: var(--color-ink-2); margin-bottom: 0.5rem;">
                Lote vinculado: <strong><?= esc($conv['producto_nombre']) ?></strong>
                <?php if (!empty($conv['tipo_polimero'])): ?>
                  <span class="badge <?= badge_polimero_class($conv['tipo_polimero']) ?>" style="font-size: 0.72rem; margin-left: 0.35rem;">
                    <?= esc($conv['tipo_polimero']) ?>
                  </span>
                <?php endif; ?>
              </div>

              <div style="font-size: 0.9rem; color: var(--color-ink); background-color: var(--color-paper-2); padding: 0.65rem 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--color-rule); max-width: 650px;">
                <strong style="color: var(--color-ink-2); font-size: 0.8rem;">
                  <?= ((int)$conv['ultimo_emisor_id'] === (int)session()->get('user_id')) ? 'Tú: ' : esc($conv['otra_empresa_nombre']) . ': ' ?>
                </strong>
                <?= esc(mb_strimwidth($conv['ultimo_mensaje'], 0, 110, '...')) ?>
              </div>
            </div>

            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem;">
              <span style="font-size: 0.8rem; color: var(--color-ink-2);">
                <?= date('d/m/Y H:i', strtotime($conv['ultima_fecha'])) ?>
              </span>
              <a href="<?= $linkDestino ?>" class="btn <?= $tieneNoLeidos ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                Abrir Conversación &rarr;
              </a>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="card" style="text-align: center; padding: 3.5rem 1.5rem;">
      <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">💬</div>
      <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-ink); margin-bottom: 0.5rem;">
        No tienes mensajes ni consultas activas
      </h3>
      <p style="color: var(--color-ink-2); max-width: 480px; margin: 0 auto 1.5rem; font-size: 0.92rem; line-height: 1.5;">
        Cuando inicies o recibas una solicitud de compra sobre un lote de polímero, podrás chatear directamente con la contraparte para coordinar el retiro, logística y precios.
      </p>
      <a href="<?= site_url('productos') ?>" class="btn btn-primary">
        Explorar Mercado de Excedentes
      </a>
    </div>
  <?php endif; ?>

</div>

<?= view('templates/footer') ?>
