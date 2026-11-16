<?= view('templates/header', ['pageTitle' => 'Bandeja de Mensajes | MateriaX']) ?>

<?php
$convs = $conversaciones ?? [];
$totalNoLeidos = $totalNoLeidos ?? array_sum(array_map(fn($c) => (int) ($c['no_leidos'] ?? 0), $convs));
?>

<div id="inboxContainer" class="container-lg stack-md"
     data-base-url="<?= site_url('/') ?>"
     data-send-url="<?= site_url('mensajes/enviar') ?>">

    <div class="page-header flex-between">
        <div>
            <h1 class="page-title">Bandeja de Mensajes</h1>
            <p class="page-sub">Conversaciones activas por lotes y operaciones comerciales.</p>
        </div>
        <a href="<?= site_url('productos') ?>" class="btn btn-secondary">Ir al Mercado</a>
    </div>

    <div class="grid-3">
        <div class="metric-card">
            <span class="metric-card-label">Conversaciones</span>
            <div id="metricTotalConversaciones" class="metric-card-value text-mono"><?= count($convs) ?></div>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Mensajes sin leer</span>
            <div id="metricTotalNoLeidos" class="metric-card-value text-mono"><?= (int) $totalNoLeidos ?></div>
        </div>
        <div class="metric-card">
            <span class="metric-card-label">Estado de Canal</span>
            <div class="metric-card-value">En Vivo</div>
        </div>
    </div>

    <div id="inboxList" class="stack-md">
        <?php if (empty($convs)): ?>
            <div id="emptyInboxPlaceholder" class="card text-center text-muted">
                No tiene conversaciones activas aún. Inicie una consulta desde cualquier lote del mercado.
            </div>
        <?php else: ?>
            <?php foreach ($convs as $conv): ?>
                <?php
                $tid = $conv['transaccion_id'] ?? null;
                $pid = $conv['producto_id'] ?? null;
                $rid = $conv['otra_empresa_id'] ?? 0;
                $openUrl = $tid
                    ? site_url('mensajes/chat/transaccion/' . $tid . '/' . $rid)
                    : site_url('mensajes/chat/producto/' . $pid . '/' . $rid);
                $convKey = $conv['conv_key'] ?? (($tid ? 'tx_' . $tid : 'prod_' . $pid) . '_' . $rid);
                $unread = (int) ($conv['no_leidos'] ?? 0);
                ?>
                <article class="card inbox-conv-card stack-sm" data-conv-key="<?= esc($convKey) ?>">
                    <div class="flex-between">
                        <div class="flex-row">
                            <strong><?= esc($conv['otra_empresa_nombre'] ?? 'Empresa') ?></strong>
                            <span class="badge badge-info inbox-total-count"><?= (int) ($conv['total_mensajes'] ?? 0) ?> msg</span>
                            <span class="badge badge-warning inbox-unread-badge"<?= $unread > 0 ? '' : ' style="display:none"' ?>><?= $unread ?> sin leer</span>
                        </div>
                        <span class="text-muted inbox-last-time"><?= !empty($conv['ultima_fecha']) ? date('d/m H:i', strtotime($conv['ultima_fecha'])) : '' ?></span>
                    </div>

                    <p class="text-sec">
                        <?= esc($conv['contexto_titulo'] ?? ($tid ? 'Operación #' . $tid : 'Lote #' . $pid)) ?>
                    </p>

                    <div class="inbox-recent-messages inbox-last-msg stack-sm">
                        <?php if (!empty($conv['ultimos_mensajes'])): ?>
                            <?php foreach ($conv['ultimos_mensajes'] as $m): ?>
                                <?php $own = ((int) ($m['emisor_id'] ?? 0) === (int) ($userId ?? 0)); ?>
                                <div class="inbox-msg-line<?= $own ? ' is-own' : '' ?>">
                                    <strong><?= esc($own ? 'Usted' : ($conv['otra_empresa_nombre'] ?? 'Empresa')) ?>:</strong>
                                    <?= esc($m['mensaje'] ?? '') ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="inbox-msg-line"><?= esc($conv['ultimo_mensaje'] ?? '') ?></div>
                        <?php endif; ?>
                    </div>

                    <form class="quick-reply-form flex-row" action="<?= site_url('mensajes/enviar') ?>" method="POST">
                        <?= csrf_field() ?>
                        <?php if ($tid): ?><input type="hidden" name="transaccion_id" value="<?= esc($tid) ?>"><?php endif; ?>
                        <?php if ($pid): ?><input type="hidden" name="producto_id" value="<?= esc($pid) ?>"><?php endif; ?>
                        <input type="hidden" name="receptor_id" value="<?= esc($rid) ?>">
                        <input type="text" name="mensaje" class="form-control quick-reply-input" placeholder="Respuesta rápida..." required maxlength="2000">
                        <button type="submit" class="btn btn-secondary btn-sm">Enviar</button>
                        <span class="quick-reply-status text-muted"></span>
                        <a href="<?= $openUrl ?>" class="btn btn-primary btn-sm inbox-open-btn">Abrir Sala</a>
                    </form>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="<?= base_url('assets/chat.js') ?>?v=2.2"></script>
<?= view('templates/footer') ?>
