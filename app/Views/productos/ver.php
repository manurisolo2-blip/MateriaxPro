<?= view('templates/header', ['pageTitle' => esc($producto['nombre']) . ' | MateriaX']) ?>

<!-- Migas de pan -->
<nav aria-label="Migas de pan" class="breadcrumb" style="max-width: 850px; margin: 0 auto 1rem;">
  <span class="breadcrumb-item"><a href="<?= site_url('/') ?>">Inicio</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item"><a href="<?= site_url('productos') ?>">Mercado</a></span>
  <span class="breadcrumb-separator" aria-hidden="true">&rsaquo;</span>
  <span class="breadcrumb-item" aria-current="page">Lote #<?= esc($producto['id']) ?></span>
</nav>

<div style="max-width: 850px; margin: 0 auto 2rem;">
  <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 style="font-size: 2rem; font-weight: 700; color: var(--color-ink); margin: 0 0 0.4rem 0;">
        <?= esc($producto['nombre']) ?>
      </h1>
      <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <span class="badge badge-polimero <?= badge_polimero_class($producto['tipo_polimero']) ?>"><?= esc($producto['tipo_polimero']) ?></span>
        <span class="badge <?= badge_estado_class($producto['estado']) ?>"><?= esc($producto['estado']) ?></span>
        <span style="color: var(--color-ink-2); font-size: 0.85rem;">Publicado el <?= date('d/m/Y H:i', strtotime($producto['created_at'])) ?></span>
      </div>
    </div>

    <?php 
      $esPropio = ((int)($producto['user_id'] ?? 0) === (int)session()->get('user_id'));
      $esAdmin  = (session()->get('rol') === 'admin');
      $isLogged = session()->get('isLoggedIn');
    ?>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
      <?php if ($esPropio || $esAdmin): ?>
        <a href="<?= site_url('productos/editar/' . $producto['id']) ?>" class="btn btn-secondary btn-sm" title="Editar ficha de lote">
          Editar Lote
        </a>
        <a href="<?= site_url('productos/confirmar-eliminar/' . $producto['id']) ?>" class="btn btn-danger btn-sm" title="Eliminar publicación">
          Dar de baja
        </a>
      <?php elseif ($isLogged && $producto['estado'] === 'Disponible'): ?>
        <a href="<?= site_url('solicitudes/crear/' . $producto['id']) ?>" class="btn btn-primary" title="Enviar solicitud formal de compra">
          &plus; Solicitar este Lote / Ofertar
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Datos del lote -->
  <div class="grid-3" style="margin-top: 0; margin-bottom: 1.5rem;">
    <div class="metric-card">
      <span class="metric-card-label">Volumen Total</span>
      <div class="metric-card-value">
        <?= format_kg($producto['cantidad_kg']) ?>
      </div>
      <span class="metric-card-sub">Masa pesada en báscula</span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Precio Unitario</span>
      <div class="metric-card-value" style="color: var(--color-success-text);">
        <?= format_precio($producto['precio_unitario']) ?> <span style="font-size: 0.9rem; font-weight: 500; color: var(--color-ink-2);">/ kg</span>
      </div>
      <span class="metric-card-sub">Sin flete ni IVA</span>
    </div>

    <div class="metric-card">
      <span class="metric-card-label">Valor Estimado Lote</span>
      <div class="metric-card-value" style="color: var(--color-ink);">
        <?= format_precio((float)$producto['cantidad_kg'] * (float)$producto['precio_unitario']) ?>
      </div>
      <span class="metric-card-sub">Total lote en oferta</span>
    </div>
  </div>

  <!-- Ficha técnica -->
  <div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
      <h3 class="card-title">Especificaciones Técnicas del Material</h3>
    </div>
    <div class="card-body">
      <div style="margin-bottom: 1.25rem;">
        <strong style="color: var(--color-ink-2); font-size: 0.9rem;">Ubicación Visible:</strong>
        <p style="font-size: 1.05rem; color: var(--color-ink); margin-top: 0.2rem;"><?= esc($producto['ubicacion']) ?></p>
      </div>

      <?php if (!empty($producto['nombre_planta'])): ?>
        <div style="margin-bottom: 1.25rem; padding: 1rem; background-color: var(--color-paper-2); border-radius: var(--radius-sm); border: 1px solid var(--color-rule);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap; gap: 0.5rem;">
            <strong style="color: var(--color-ink); font-size: 0.95rem;">
              Sede Fabril de Carga: <?= esc($producto['nombre_planta']) ?>
            </strong>
            <span class="badge <?= !empty($producto['planta_posee_bascula']) ? 'badge-success' : 'badge-warning' ?>">
              <?= !empty($producto['planta_posee_bascula']) ? 'Báscula Homologada' : 'Sin Báscula en Planta' ?>
            </span>
          </div>
          <div style="font-size: 0.88rem; color: var(--color-ink-2); line-height: 1.5;">
            <div><strong>Dirección de carga:</strong> <?= esc($producto['planta_direccion']) ?> (<?= esc($producto['planta_localidad']) ?>, <?= esc($producto['planta_provincia']) ?>)</div>
          </div>
        </div>
      <?php endif; ?>

      <div>
        <strong style="color: var(--color-ink-2); font-size: 0.9rem;">Descripción y Control de Calidad:</strong>
        <div style="margin-top: 0.4rem; padding: 1rem; background-color: var(--color-paper-surface); border-radius: var(--radius-sm); border: 1px solid var(--color-rule); color: var(--color-ink); white-space: pre-line; line-height: 1.6;">
          <?= !empty($producto['descripcion']) ? esc($producto['descripcion']) : '<em>Sin especificaciones adicionales registradas para este lote.</em>' ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Datos del vendedor -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Datos de Contacto Institucional</h3>
    </div>
    <div class="card-body">
      <div class="form-row">
        <div>
          <span style="font-size: 0.85rem; color: var(--color-ink-2);">Empresa Oferente:</span>
          <p style="font-size: 1.05rem; font-weight: 700; color: var(--color-ink);"><?= esc($producto['empresa_nombre'] ?? 'Empresa Registrada') ?></p>
        </div>
        <div>
          <span style="font-size: 0.85rem; color: var(--color-ink-2);">Correo Electrónico:</span>
          <p style="font-size: 1.05rem; color: var(--color-ink);"><?= esc($producto['empresa_email'] ?? 'No especificado') ?></p>
        </div>
        <div>
          <span style="font-size: 0.85rem; color: var(--color-ink-2);">Teléfono:</span>
          <p style="font-size: 1.05rem; color: var(--color-ink);"><?= esc($producto['empresa_telefono'] ?? 'No especificado') ?></p>
        </div>
        <div>
          <span style="font-size: 0.85rem; color: var(--color-ink-2);">CUIT:</span>
          <p style="font-size: 1.05rem; color: var(--color-ink);"><?= esc($producto['empresa_cuit'] ?? 'Sin CUIT') ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Consultas directas al oferente del lote (Comprador) -->
  <?php if ($isLogged && !$esPropio): ?>
    <div id="consultas" class="card" style="margin-top: 1.5rem;">
      <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <h3 class="card-title" style="margin: 0;">💬 Consultar a <?= esc($producto['empresa_nombre'] ?? 'la Empresa Oferente') ?></h3>
        <a href="<?= site_url('mensajes/chat/producto/' . $producto['id'] . '/' . $producto['user_id']) ?>" class="btn btn-secondary btn-sm">
          Abrir Chat Completo &rarr;
        </a>
      </div>
      <div class="card-body">
        <p style="font-size: 0.88rem; color: var(--color-ink-2); margin-top: 0; margin-bottom: 1rem;">
          ¿Tienes dudas sobre la pureza del polímero, molienda, embalaje o logística antes de ofertar? Escríbele directamente.
        </p>

        <?php if (!empty($consultas)): ?>
          <div class="chat-thread" style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 280px; overflow-y: auto; padding: 0.85rem; background-color: var(--color-paper-2); border: 1px solid var(--color-rule); border-radius: var(--radius-sm); margin-bottom: 1rem;">
            <?php foreach ($consultas as $msg): ?>
              <?php $esMio = ((int)$msg['emisor_id'] === $userId); ?>
              <div style="display: flex; flex-direction: column; align-items: <?= $esMio ? 'flex-end' : 'flex-start' ?>;">
                <div style="font-size: 0.75rem; color: var(--color-ink-2); margin-bottom: 0.15rem;">
                  <strong><?= $esMio ? 'Tú' : esc($msg['emisor_nombre']) ?></strong> &bull; <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                </div>
                <div style="max-width: 85%; padding: 0.6rem 0.85rem; border-radius: 10px; font-size: 0.88rem; <?= $esMio ? 'background-color: var(--color-primary); color: #fff;' : 'background-color: var(--color-paper-surface); color: var(--color-ink); border: 1px solid var(--color-rule);' ?>">
                  <?= esc($msg['mensaje']) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form action="<?= site_url('mensajes/enviar') ?>" method="POST" style="margin: 0;">
          <?= csrf_field() ?>
          <input type="hidden" name="producto_id" value="<?= esc($producto['id']) ?>">
          <input type="hidden" name="receptor_id" value="<?= esc($producto['user_id']) ?>">

          <div class="form-group" style="margin-bottom: 0.75rem;">
            <textarea 
              name="mensaje" 
              rows="2" 
              class="form-control" 
              placeholder="Escribe tu consulta para el oferente aquí..." 
              required 
              minlength="2" 
              maxlength="2000"
              style="resize: vertical;"
            ></textarea>
          </div>
          <div style="display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary btn-sm">
              Enviar Consulta al Vendedor &rarr;
            </button>
          </div>
        </form>
      </div>
    </div>
  <?php elseif ($isLogged && $esPropio): ?>
    <!-- Panel del Vendedor: Consultas recibidas de interesados sobre este lote -->
    <div id="consultas" class="card" style="margin-top: 1.5rem;">
      <div class="card-header">
        <h3 class="card-title">💬 Consultas de Interesados Recibidas</h3>
      </div>
      <div class="card-body">
        <?php if (!empty($consultasRecibidas)): ?>
          <p style="font-size: 0.88rem; color: var(--color-ink-2); margin-top: 0; margin-bottom: 1rem;">
            Las siguientes empresas han enviado consultas sobre este lote. Haz clic en "Abrir Conversación" para responderles:
          </p>
          <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <?php 
              $agrupadas = [];
              foreach ($consultasRecibidas as $cr) {
                  if (!isset($agrupadas[$cr['emisor_id']])) {
                      $agrupadas[$cr['emisor_id']] = $cr;
                  }
              }
            ?>
            <?php foreach ($agrupadas as $cr): ?>
              <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; background-color: var(--color-paper-2); border: 1px solid var(--color-rule); border-radius: var(--radius-sm); flex-wrap: wrap; gap: 0.5rem;">
                <div>
                  <strong style="color: var(--color-ink); font-size: 0.95rem;"><?= esc($cr['emisor_nombre']) ?></strong>
                  <div style="font-size: 0.85rem; color: var(--color-ink-2); margin-top: 0.2rem;">
                    "<?= esc(mb_strimwidth($cr['mensaje'], 0, 90, '...')) ?>"
                  </div>
                  <span style="font-size: 0.75rem; color: var(--color-ink-2);"><?= date('d/m/Y H:i', strtotime($cr['created_at'])) ?></span>
                </div>
                <a href="<?= site_url('mensajes/chat/producto/' . $producto['id'] . '/' . $cr['emisor_id']) ?>" class="btn btn-primary btn-sm">
                  Abrir Conversación &rarr;
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p style="font-size: 0.88rem; color: var(--color-ink-2); margin: 0;">
            No has recibido consultas de otras empresas sobre este lote por el momento.
          </p>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<?= view('templates/footer') ?>
