<?= view('templates/header', ['pageTitle' => $pageTitle ?? 'Panel de Empresa | MateriaX']) ?>

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap;">
      <span class="badge badge-polimero" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">
        Panel Corporativo
      </span>
      <span class="badge badge-success">
        ● Cuenta Habilitada y Auditada
      </span>
    </div>
    <h1 style="font-size: 2.2rem; font-weight: 800; color: #ffffff; margin: 0;">
      <?= esc($user['nombre']) ?>
    </h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem; margin-top: 0.35rem;">
      <strong>CUIT:</strong> <?= esc($user['cuit'] ?? 'N/D') ?> &nbsp;|&nbsp; 
      <strong>Rubro:</strong> <?= esc($user['rubro'] ?? 'Industrial') ?> &nbsp;|&nbsp; 
      <strong>Radicación:</strong> <?= esc($user['ciudad'] ?? 'Córdoba') ?>, <?= esc($user['provincia'] ?? 'Argentina') ?>
    </p>
  </div>

  <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
    <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary">
      ➕ Publicar Nuevo Lote
    </a>
    <a href="<?= site_url('productos') ?>" class="btn btn-secondary">
      🌐 Explorar Mercado
    </a>
    <a href="<?= site_url('perfil') ?>" class="btn btn-secondary">
      👤 Datos de Empresa
    </a>
  </div>
</div>

<!-- Tarjetas de Métricas de la Empresa -->
<div class="grid-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
  <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Mis Lotes Publicados</span>
    <div style="font-size: 2rem; font-weight: 800; color: #14b8a6; margin-top: 0.25rem;">
      <?= $totalLotes ?>
    </div>
    <span style="font-size: 0.8rem; color: var(--text-secondary);"><?= $lotesDisponibles ?> disponibles en la red</span>
  </div>

  <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Kilos en Oferta</span>
    <div style="font-size: 2rem; font-weight: 800; color: #38bdf8; margin-top: 0.25rem;">
      <?= number_format($totalKilos, 0, ',', '.') ?> <span style="font-size: 1rem; font-weight: 600; color: var(--text-secondary);">kg</span>
    </div>
    <span style="font-size: 0.8rem; color: var(--text-secondary);">Materia prima circular</span>
  </div>

  <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Valor en Oferta (Est.)</span>
    <div style="font-size: 2rem; font-weight: 800; color: #4ade80; margin-top: 0.25rem;">
      $<?= number_format($valorEstimado, 0, ',', '.') ?>
    </div>
    <span style="font-size: 0.8rem; color: var(--text-secondary);">Valorización total de inventario</span>
  </div>

  <div class="card" style="margin-bottom: 0; padding: 1.25rem;">
    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Estado de Cuenta</span>
    <div style="font-size: 1.25rem; font-weight: 700; color: #4ade80; margin-top: 0.5rem;">
      ● Homologada
    </div>
    <span style="font-size: 0.8rem; color: var(--text-secondary);">Revisión fiscal aprobada</span>
  </div>
</div>

<!-- Sección Principal: Gestión de Mis Lotes -->
<div class="card" style="margin-bottom: 2.5rem;">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
    <div>
      <h3 class="card-title" style="margin: 0;">📦 Mis Publicaciones en MateriaX</h3>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem; margin-bottom: 0;">
        Lotes registrados bajo la titularidad exclusiva de tu empresa. Puedes editarlos o darlos de baja cuando lo desees.
      </p>
    </div>
    <div>
      <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary btn-sm">
        ➕ Publicar Nuevo Lote
      </a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Lote / Material</th>
          <th>Polímero</th>
          <th>Cantidad</th>
          <th>Precio / Kg</th>
          <th>Ubicación</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th style="text-align: right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($misProductos) && count($misProductos) > 0): ?>
          <?php foreach ($misProductos as $p): ?>
            <tr>
              <td>
                <a href="<?= site_url('productos/ver/' . $p['id']) ?>" style="font-weight: 700; color: #ffffff;">
                  <?= esc($p['nombre']) ?>
                </a>
                <div style="font-size: 0.78rem; color: var(--text-muted);">
                  ID #<?= esc($p['id']) ?>
                </div>
              </td>
              <td>
                <span class="badge badge-polimero"><?= esc($p['tipo_polimero']) ?></span>
              </td>
              <td><strong><?= number_format((float)$p['cantidad_kg'], 0, ',', '.') ?></strong> kg</td>
              <td>$<?= number_format((float)$p['precio_unitario'], 2, ',', '.') ?></td>
              <td><?= esc($p['ubicacion']) ?></td>
              <td>
                <?php 
                  $badgeClass = 'badge-disponible';
                  if ($p['estado'] === 'Reservado') $badgeClass = 'badge-reservado';
                  if ($p['estado'] === 'Vendido') $badgeClass = 'badge-vendido';
                ?>
                <span class="badge <?= $badgeClass ?>"><?= esc($p['estado']) ?></span>
              </td>
              <td style="color: var(--text-secondary); font-size: 0.85rem;">
                <?= date('d/m/Y', strtotime($p['created_at'])) ?>
              </td>
              <td style="text-align: right; white-space: nowrap;">
                <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                  <a href="<?= site_url('productos/ver/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Ver ficha pública del lote">
                    👁 Ver
                  </a>
                  <a href="<?= site_url('productos/editar/' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Editar lote propio">
                    ✏ Editar
                  </a>
                  <a href="<?= site_url('productos/confirmar-eliminar/' . $p['id']) ?>" class="btn btn-danger btn-sm" title="Eliminar este lote">
                    🗑
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-secondary);">
              <p style="font-size: 1.1rem; color: #ffffff; font-weight: 600; margin-bottom: 0.5rem;">
                Aún no has publicado ningún lote de excedente industrial
              </p>
              <p style="font-size: 0.92rem; margin-bottom: 1.25rem;">
                Comienza a circular tus mermas, descartes o granzas plásticas para conectar con compradores industriales en toda la región.
              </p>
              <a href="<?= site_url('productos/crear') ?>" class="btn btn-primary">
                ➕ Publicar Primer Lote de Material
              </a>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Sección Secundaria: Oportunidades del Mercado General -->
<?php if (!empty($lotesMercado) && count($lotesMercado) > 0): ?>
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
    <div>
      <h3 style="font-size: 1.25rem; font-weight: 700; color: #ffffff; margin: 0;">
        🌐 Oportunidades Recientes en la Red
      </h3>
      <p style="color: var(--text-secondary); font-size: 0.88rem; margin: 0.2rem 0 0 0;">
        Materiales industriales publicados por otras empresas registradas en MateriaX.
      </p>
    </div>
    <div>
      <a href="<?= site_url('productos') ?>" class="btn btn-secondary btn-sm">
        Ver Todo el Inventario →
      </a>
    </div>
  </div>

  <div class="grid-2" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <?php foreach ($lotesMercado as $lm): ?>
      <div class="card" style="margin-bottom: 0; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
            <span class="badge badge-polimero"><?= esc($lm['tipo_polimero']) ?></span>
            <span class="badge badge-disponible">Disponible</span>
          </div>
          <h4 style="font-size: 1.05rem; font-weight: 700; color: #ffffff; margin: 0.25rem 0 0.5rem 0;">
            <a href="<?= site_url('productos/ver/' . $lm['id']) ?>" style="text-decoration: none; color: #ffffff;">
              <?= esc($lm['nombre']) ?>
            </a>
          </h4>
          <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.75rem;">
            Ofertado por: <strong style="color: #e2e8f0;"><?= esc($lm['empresa_nombre'] ?? 'Empresa Registrada') ?></strong>
            <br>
            Ubicación: <?= esc($lm['ubicacion']) ?>
          </p>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 0.75rem; margin-top: 0.5rem;">
          <div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Volumen / Precio</div>
            <div style="font-size: 0.95rem; font-weight: 700; color: #ffffff;">
              <?= number_format((float)$lm['cantidad_kg'], 0, ',', '.') ?> kg &nbsp;|&nbsp; 
              <span style="color: #4ade80;">$<?= number_format((float)$lm['precio_unitario'], 2, ',', '.') ?>/kg</span>
            </div>
          </div>
          <div>
            <a href="<?= site_url('productos/ver/' . $lm['id']) ?>" class="btn btn-secondary btn-sm">
              👁 Ver Ficha
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?= view('templates/footer') ?>
