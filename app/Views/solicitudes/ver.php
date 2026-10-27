<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Ficha de Operación | MateriaX']) ?>

<?php
  $userId   = (int) session()->get('user_id');
  $esVendedor  = ((int) $transaccion['vendedor_id'] === $userId);
  $esComprador = ((int) $transaccion['comprador_id'] === $userId);
  $esAdmin     = (session()->get('rol') === 'admin');
?>

<!-- Migas de pan -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 900px; margin: 0 auto 1.25rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('solicitudes/' . ($esVendedor ? 'recibidas' : 'enviadas')) ?>">Solicitudes</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page"><?= esc($transaccion['codigo_operacion']) ?></span>
</nav>

<div style="max-width: 900px; margin: 0 auto 2.5rem;">

  <!-- Encabezado de la transacción -->
  <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <div>
      <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap;">
        <h1 style="font-size: 1.9rem; font-weight: 700; color: var(--color-ink); margin: 0;">
          Operación <?= esc($transaccion['codigo_operacion']) ?>
        </h1>
        <span class="badge <?= badge_transaccion_class($transaccion['estado']) ?>" style="font-size: 0.85rem; padding: 0.35rem 0.65rem;">
          <?= label_transaccion_estado($transaccion['estado']) ?>
        </span>
      </div>
      <p style="color: var(--color-ink-2); font-size: 0.92rem; margin: 0;">
        Iniciada el <?= date('d/m/Y H:i', strtotime($transaccion['fecha_solicitud'])) ?> &bull; 
        Lote: <a href="<?= site_url('productos/ver/' . $transaccion['producto_id']) ?>" style="color: inherit; text-decoration: underline;"><strong><?= esc($transaccion['producto_nombre']) ?></strong></a>
      </p>
    </div>

    <!-- Botones de respuesta para el vendedor si está pendiente -->
    <?php if ($esVendedor && $transaccion['estado'] === 'solicitada'): ?>
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <form action="<?= site_url('solicitudes/responder/' . $transaccion['id']) ?>" method="POST" style="margin: 0;">
          <?= csrf_field() ?>
          <input type="hidden" name="accion" value="aceptar">
          <button type="submit" class="btn btn-primary">
            &check; Aceptar Oferta y Reservar Lote
          </button>
        </form>

        <form action="<?= site_url('solicitudes/responder/' . $transaccion['id']) ?>" method="POST" style="margin: 0;">
          <?= csrf_field() ?>
          <input type="hidden" name="accion" value="rechazar">
          <button type="submit" class="btn btn-danger">
            &cross; Rechazar Propuesta
          </button>
        </form>
      </div>
    <?php endif; ?>

    <!-- Botón cancelar para el comprador si está pendiente -->
    <?php if ($esComprador && $transaccion['estado'] === 'solicitada'): ?>
      <form action="<?= site_url('solicitudes/cancelar/' . $transaccion['id']) ?>" method="POST" style="margin: 0;">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger btn-sm">
          Cancelar Mi Solicitud
        </button>
      </form>
    <?php endif; ?>
  </div>

  <!-- Notificación si tiene certificado ambiental emitido -->
  <?php if (!empty($certificado)): ?>
    <div class="card" style="margin-bottom: 1.5rem; background-color: var(--color-success-bg); border-color: var(--color-success-border); padding: 1.25rem 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
          <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.25rem;">
            <span class="badge badge-success">Certificado Ambiental Emitido</span>
            <strong style="color: var(--color-success-text); font-family: var(--font-mono); font-size: 0.95rem;">
              <?= esc($certificado['codigo_certificado']) ?>
            </strong>
          </div>
          <div style="font-size: 0.88rem; color: var(--color-ink-2);">
            Impacto verificado: <strong><?= format_kg($certificado['kg_recuperados']) ?></strong> de polímero circular &bull; 
            <strong><?= number_format($certificado['co2_evitado_kg'], 1, ',', '.') ?> kg de CO₂eq</strong> mitigados.
          </div>
        </div>
        <a href="<?= site_url('certificados/ver/' . $certificado['codigo_certificado']) ?>" class="btn btn-primary btn-sm">
          Ver Certificado Oficial &rarr;
        </a>
      </div>
    </div>
  <?php endif; ?>

  <!-- Métricas económicas del acuerdo -->
  <div class="grid-3" style="margin-bottom: 1.5rem;">
    <div class="metric-card">
      <span class="metric-card-label">Volumen Solicitado</span>
      <div class="metric-card-value">
        <?= format_kg($transaccion['cantidad_kg']) ?>
      </div>
      <span class="metric-card-sub">Polímero: <?= esc($transaccion['tipo_polimero']) ?></span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Precio Pactado</span>
      <div class="metric-card-value" style="color: var(--color-ink);">
        <?= format_precio($transaccion['precio_unitario_pactado']) ?> <span style="font-size: 0.9rem; font-weight: 500; color: var(--color-ink-2);">/ kg</span>
      </div>
      <span class="metric-card-sub">Subtotal: <?= format_precio($transaccion['subtotal']) ?></span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Monto Total de Operación</span>
      <div class="metric-card-value" style="color: var(--color-success-text);">
        <?= format_precio($transaccion['monto_total']) ?>
      </div>
      <span class="metric-card-sub">Flete incluido: <?= format_precio($transaccion['costo_flete']) ?></span>
    </div>
  </div>

  <!-- Desglose de partes comerciales -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
    <!-- Oferente / Vendedor -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Empresa Oferente (Vendedora)</h3>
      </div>
      <div class="card-body" style="font-size: 0.9rem; line-height: 1.6; color: var(--color-ink-2);">
        <div><strong style="color: var(--color-ink);">Razón Social:</strong> <?= esc($transaccion['vendedor_nombre']) ?></div>
        <div><strong>CUIT:</strong> <?= esc($transaccion['vendedor_cuit'] ?? 'N/D') ?></div>
        <div><strong>Ciudad:</strong> <?= esc($transaccion['vendedor_ciudad'] ?? 'No especificada') ?></div>
        <div><strong>Email de Contacto:</strong> <?= esc($transaccion['vendedor_email']) ?></div>
        <div><strong>Teléfono:</strong> <?= esc($transaccion['vendedor_telefono'] ?? 'No informado') ?></div>
      </div>
    </div>

    <!-- Comprador / Solicitante -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Empresa Solicitante (Compradora)</h3>
      </div>
      <div class="card-body" style="font-size: 0.9rem; line-height: 1.6; color: var(--color-ink-2);">
        <div><strong style="color: var(--color-ink);">Razón Social:</strong> <?= esc($transaccion['comprador_nombre']) ?></div>
        <div><strong>CUIT:</strong> <?= esc($transaccion['comprador_cuit'] ?? 'N/D') ?></div>
        <div><strong>Ciudad:</strong> <?= esc($transaccion['comprador_ciudad'] ?? 'No especificada') ?></div>
        <div><strong>Email de Contacto:</strong> <?= esc($transaccion['comprador_email']) ?></div>
        <div><strong>Teléfono:</strong> <?= esc($transaccion['comprador_telefono'] ?? 'No informado') ?></div>
      </div>
    </div>
  </div>

  <!-- Logística y Punto de Carga -->
  <div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
      <h3 class="card-title">Condiciones Logísticas y Centro de Retiro</h3>
    </div>
    <div class="card-body">
      <div class="form-row" style="margin-bottom: 1rem;">
        <div>
          <strong style="color: var(--color-ink-2); font-size: 0.85rem;">Modalidad Acordada:</strong>
          <p style="font-size: 1.05rem; font-weight: 600; color: var(--color-ink); margin: 0.2rem 0 0 0;">
            <?= label_modalidad_retiro($transaccion['modalidad_retiro']) ?>
          </p>
        </div>

        <div>
          <strong style="color: var(--color-ink-2); font-size: 0.85rem;">Fecha de Acuerdo:</strong>
          <p style="font-size: 1.05rem; color: var(--color-ink); margin: 0.2rem 0 0 0;">
            <?= !empty($transaccion['fecha_acuerdo']) ? date('d/m/Y H:i', strtotime($transaccion['fecha_acuerdo'])) : '<em>Pendiente de aceptación</em>' ?>
          </p>
        </div>
      </div>

      <?php if (!empty($transaccion['nombre_planta'])): ?>
        <div style="padding: 1rem; background-color: var(--color-paper-2); border-radius: var(--radius-sm); border: 1px solid var(--color-rule);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap;">
            <strong style="color: var(--color-ink);">Sede Fabril: <?= esc($transaccion['nombre_planta']) ?></strong>
            <span class="badge <?= !empty($transaccion['planta_posee_bascula']) ? 'badge-success' : 'badge-warning' ?>">
              <?= !empty($transaccion['planta_posee_bascula']) ? 'Báscula Homologada Disponible' : 'Sin Báscula en Planta' ?>
            </span>
          </div>
          <div style="font-size: 0.88rem; color: var(--color-ink-2); line-height: 1.5;">
            <div><strong>Dirección de carga:</strong> <?= esc($transaccion['planta_direccion']) ?> (<?= esc($transaccion['planta_localidad']) ?>, <?= esc($transaccion['planta_provincia']) ?>)</div>
            <div><strong>Capacidad admitida:</strong> <?= esc($transaccion['planta_capacidad_carga'] ?: 'Hasta 45 Ton') ?></div>
          </div>
        </div>
      <?php else: ?>
        <p style="font-size: 0.9rem; color: var(--color-ink-2); margin: 0;">
          Ubicación de referencia indicada en el lote: <strong><?= esc($transaccion['producto_ubicacion']) ?></strong>.
        </p>
      <?php endif; ?>

      <?php if (!empty($transaccion['observaciones'])): ?>
        <div style="margin-top: 1.25rem;">
          <strong style="color: var(--color-ink-2); font-size: 0.85rem;">Observaciones y Notas de la Solicitud:</strong>
          <div style="margin-top: 0.35rem; padding: 0.85rem 1rem; background-color: var(--color-paper-surface); border: 1px solid var(--color-rule); border-radius: var(--radius-sm); color: var(--color-ink); white-space: pre-line; font-size: 0.9rem;">
            <?= esc($transaccion['observaciones']) ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Canal de Comunicación B2B y Negociación Directa -->
  <div id="chat" class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
      <h3 class="card-title" style="margin: 0;">
        💬 Canal de Comunicación entre Empresas
      </h3>
      <span style="font-size: 0.85rem; color: var(--color-ink-2);">
        <?= count($mensajes ?? []) ?> mensaje(s)
      </span>
    </div>
    <div class="card-body">
      <p style="font-size: 0.88rem; color: var(--color-ink-2); margin-top: 0; margin-bottom: 1rem;">
        Utilicen este canal directo para coordinar especificaciones del polímero, logística de flete, ingreso a báscula y fecha de retiro.
      </p>

      <!-- Historial de mensajes -->
      <div id="chatContainer" class="chat-thread" data-transaccion-id="<?= esc($transaccion['id']) ?>" data-user-id="<?= esc($userId) ?>" data-base-url="<?= site_url('/') ?>" style="display: flex; flex-direction: column; gap: 0.85rem; max-height: 420px; overflow-y: auto; padding: 1rem; background-color: var(--color-paper-2); border: 1px solid var(--color-rule); border-radius: var(--radius-sm); margin-bottom: 1.25rem;">
        <?php if (!empty($mensajes)): ?>
          <?php foreach ($mensajes as $msg): ?>
            <?php 
              $esMio = ((int) $msg['emisor_id'] === $userId);
            ?>
            <div id="msg-<?= $msg['id'] ?>" data-msg-id="<?= $msg['id'] ?>" style="display: flex; flex-direction: column; align-items: <?= $esMio ? 'flex-end' : 'flex-start' ?>;">
              <div style="font-size: 0.78rem; color: var(--color-ink-2); margin-bottom: 0.2rem; padding: 0 0.25rem;">
                <strong><?= $esMio ? 'Tú (' . esc($msg['emisor_nombre']) . ')' : esc($msg['emisor_nombre']) ?></strong>
                &bull; <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                <?php if ($esMio): ?>
                  <span style="font-size: 0.75rem; color: <?= $msg['leido'] ? 'var(--color-success-text)' : 'var(--color-ink-2)' ?>;">
                    <?= $msg['leido'] ? '&check;&check; Leído' : '&check; Enviado' ?>
                  </span>
                <?php endif; ?>
              </div>
              <div style="max-width: 80%; padding: 0.75rem 1rem; border-radius: 12px; font-size: 0.92rem; line-height: 1.5; white-space: pre-line; word-break: break-word; <?= $esMio ? 'background-color: var(--color-primary); color: #ffffff; border-bottom-right-radius: 2px;' : 'background-color: var(--color-paper-surface); color: var(--color-ink); border: 1px solid var(--color-rule); border-bottom-left-radius: 2px;' ?>">
                <?= esc($msg['mensaje']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="text-align: center; padding: 2rem 1rem; color: var(--color-ink-2); font-size: 0.9rem;">
            Aún no se han enviado mensajes en esta operación. ¡Escribe el primero a continuación!
          </div>
        <?php endif; ?>
      </div>

      <!-- Formulario para enviar mensaje nuevo -->
      <?php 
        $receptorId = $esVendedor ? (int)$transaccion['comprador_id'] : (int)$transaccion['vendedor_id'];
        $receptorNombre = $esVendedor ? $transaccion['comprador_nombre'] : $transaccion['vendedor_nombre'];
      ?>
      <form id="chatForm" action="<?= site_url('mensajes/enviar') ?>" method="POST" style="margin: 0;">
        <?= csrf_field() ?>
        <input type="hidden" name="transaccion_id" value="<?= esc($transaccion['id']) ?>">
        <input type="hidden" name="producto_id" value="<?= esc($transaccion['producto_id']) ?>">
        <input type="hidden" name="receptor_id" value="<?= esc($receptorId) ?>">

        <div class="form-group" style="margin-bottom: 0.75rem;">
          <label for="mensaje" class="form-label" style="font-size: 0.88rem;">
            Enviar mensaje a <strong><?= esc($receptorNombre) ?></strong>:
          </label>
          <textarea 
            name="mensaje" 
            id="chatInput" placeholder="Escribe tu mensaje o consulta de coordinación aquí (Enter para enviar, Shift+Enter para salto de línea)..." 
            rows="3" 
            class="form-control" 
            placeholder="Escribe tu mensaje o consulta de coordinación aquí..." 
            required 
            minlength="2" 
            maxlength="2000"
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

  <div style="display: flex; gap: 0.75rem;">
    <a href="<?= site_url('solicitudes/' . ($esVendedor ? 'recibidas' : 'enviadas')) ?>" class="btn btn-secondary">
      &larr; Volver a Solicitudes
    </a>
  </div>

</div>

<script src="<?= base_url('assets/chat.js') ?>"></script>

<?= view('templates/footer') ?>
