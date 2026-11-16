/**
 * MateriaX Pro — Motor de Chat y Negociación B2B en Tiempo Real (v2.2)
 */
document.addEventListener('DOMContentLoaded', () => {
    const chatContainer  = document.getElementById('chatContainer');
    const chatForm       = document.getElementById('chatForm');
    const mensajeInput   = document.getElementById('chatInput');
    const inboxContainer = document.getElementById('inboxContainer');

    const toSameOriginUrl = (raw) => {
        if (!raw) return '';
        try { const u = new URL(raw, window.location.origin); return u.pathname + u.search; } catch { return raw; }
    };
    const escapeHtml = (t) => {
        const d = document.createElement('div');
        d.textContent = t == null ? '' : String(t);
        return d.innerHTML;
    };
    const ajaxHeaders = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

    const updateHeaderBadge = (total) => {
        document.querySelectorAll('.badge-mensajes-live').forEach((b) => {
            b.style.display = total > 0 ? 'inline-block' : 'none';
            if (total > 0) b.textContent = total;
        });
    };

    let bc = null;
    try { if ('BroadcastChannel' in window) bc = new BroadcastChannel('materiax_b2b_chat'); } catch {}
    const notifyTabs = (payload) => {
        try {
            if (bc) bc.postMessage(payload);
            localStorage.setItem('materiax_chat_sync_event', JSON.stringify({ ...payload, ts: Date.now() }));
        } catch {}
    };
    const subscribeSync = (fn) => {
        if (bc) bc.addEventListener('message', fn);
        window.addEventListener('storage', (e) => { if (e.key === 'materiax_chat_sync_event') fn(); });
        window.addEventListener('focus', fn);
    };

    // 1. Conversación en vivo
    if (chatContainer && chatForm && mensajeInput) {
        const { transaccionId = '', productoId = '', receptorId = '', userId = '0', baseUrl = '/' } = chatContainer.dataset;
        const currentUserId = parseInt(userId, 10);
        const normBase      = toSameOriginUrl(baseUrl);
        const cleanBase     = normBase.endsWith('/') ? normBase : `${normBase}/`;

        let ultimoId = Array.from(chatContainer.querySelectorAll('[data-msg-id]'))
            .reduce((m, el) => Math.max(m, parseInt(el.dataset.msgId, 10) || 0), 0);

        const updateCount = () => {
            const el = document.getElementById('chatMsgCount');
            if (el) el.textContent = `${chatContainer.querySelectorAll('.chat-bubble-row').length} mensaje(s)`;
        };
        const scrollToBottom = (smooth = true) => {
            try { chatContainer.scrollTo({ top: chatContainer.scrollHeight + 200, behavior: smooth ? 'smooth' : 'auto' }); }
            catch { chatContainer.scrollTop = chatContainer.scrollHeight; }
        };
        scrollToBottom(false);

        const tickHtml = (leido, pending) => pending
            ? '<span class="msg-status-tick">Enviando...</span>'
            : `<span class="msg-status-tick">${parseInt(leido || 0, 10) === 1 ? '&check;&check; Leído' : '&check; Enviado'}</span>`;

        const appendMessage = (msg, isPending = false) => {
            document.getElementById('emptyChatPlaceholder')?.remove();
            const isOwn = Boolean(msg.es_propio || parseInt(msg.emisor_id, 10) === currentUserId);
            const row   = document.createElement('div');
            row.className = `chat-bubble-row${isOwn ? ' is-own' : ''}`;
            row.id        = msg.id ? `msg-${msg.id}` : `msg-temp-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
            if (msg.id) row.dataset.msgId = String(msg.id);
            if (isPending) {
                row.dataset.pending     = '1';
                row.dataset.pendingText = String(msg.mensaje).trim();
                row.style.opacity       = '0.78';
            }

            row.innerHTML = `
                <div class="chat-bubble-sender">${escapeHtml(isOwn ? 'Tú' : (msg.emisor_nombre || 'Contraparte'))}</div>
                <div class="chat-bubble-box">
                    <div class="chat-bubble-text">${escapeHtml(msg.mensaje).replace(/\n/g, '<br>')}</div>
                    <div class="chat-bubble-meta">
                        <span class="msg-time">${escapeHtml(msg.created_at || 'Ahora')}</span>
                        ${isOwn ? tickHtml(msg.leido, isPending) : ''}
                    </div>
                </div>`;
            chatContainer.appendChild(row);
            updateCount();
            scrollToBottom(true);
            return row;
        };

        const confirmPending = (node, serverMsg) => {
            const realId   = parseInt(serverMsg.id, 10);
            const existing = document.getElementById(`msg-${realId}`);
            if (existing && existing !== node) { node.remove(); updateCount(); return existing; }

            node.id            = `msg-${realId}`;
            node.dataset.msgId = String(realId);
            delete node.dataset.pending;
            delete node.dataset.pendingText;
            node.style.opacity = '1';

            const timeEl = node.querySelector('.msg-time');
            if (timeEl && serverMsg.created_at) timeEl.textContent = serverMsg.created_at;
            const tickEl = node.querySelector('.msg-status-tick');
            if (tickEl) tickEl.innerHTML = parseInt(serverMsg.leido || 0, 10) === 1 ? '&check;&check; Leído' : '&check; Enviado';
            ultimoId = Math.max(ultimoId, realId);
            return node;
        };

        const markErrorNode = (node, text) => {
            node.style.opacity = '1';
            node.querySelector('.chat-bubble-box')?.classList.add('is-error');
            const tick = node.querySelector('.msg-status-tick');
            if (tick) tick.textContent = text;
        };

        const sendMessage = async () => {
            const texto = mensajeInput.value.trim();
            if (!texto) { mensajeInput.focus(); return; }

            const formData = new FormData(chatForm);
            formData.set('mensaje', texto);
            mensajeInput.value = '';
            mensajeInput.focus();

            const now  = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const temp = appendMessage({ id: null, mensaje: texto, emisor_id: currentUserId, emisor_nombre: 'Tú', created_at: now, es_propio: true, leido: 0 }, true);

            try {
                const res  = await fetch(toSameOriginUrl(chatForm.action), { method: 'POST', headers: ajaxHeaders, body: formData, credentials: 'same-origin' });
                const data = await res.json();
                if (res.ok && data.status === 'success' && data.data) {
                    confirmPending(temp, data.data);
                    notifyTabs({ type: 'new_message', transaccionId, productoId, msgId: data.data.id });
                } else {
                    markErrorNode(temp, `Error: ${data.message || 'No enviado'}`);
                }
            } catch {
                markErrorNode(temp, 'Error de red al enviar');
            }
        };

        chatForm.addEventListener('submit', (e) => { e.preventDefault(); sendMessage(); });
        mensajeInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });

        let isPolling = false;
        const pollNuevos = async () => {
            if (isPolling) return;
            isPolling = true;
            try {
                let url = `${cleanBase}mensajes/nuevos?ultimo_id=${ultimoId}&_t=${Date.now()}`;
                if (transaccionId) url += `&transaccion_id=${encodeURIComponent(transaccionId)}`;
                else if (productoId) {
                    url += `&producto_id=${encodeURIComponent(productoId)}`;
                    if (receptorId) url += `&otra_empresa_id=${encodeURIComponent(receptorId)}`;
                }

                const res = await fetch(url, { headers: ajaxHeaders, credentials: 'same-origin', cache: 'no-store' });
                if (!res.ok) return;
                const data = await res.json();
                if (data.status !== 'success') return;

                (data.nuevos || []).forEach((nuevo) => {
                    const msgId = parseInt(nuevo.id, 10);
                    if (document.getElementById(`msg-${msgId}`)) { ultimoId = Math.max(ultimoId, msgId); return; }
                    if (nuevo.es_propio || parseInt(nuevo.emisor_id, 10) === currentUserId) {
                        const match = Array.from(chatContainer.querySelectorAll('[data-pending="1"]'))
                            .find((p) => p.dataset.pendingText === String(nuevo.mensaje).trim());
                        if (match) { confirmPending(match, nuevo); return; }
                    }
                    appendMessage(nuevo, false);
                    ultimoId = Math.max(ultimoId, msgId);
                });

                const maxLeido = parseInt(data.max_leido_id || 0, 10);
                if (maxLeido > 0) {
                    chatContainer.querySelectorAll('[data-msg-id]').forEach((row) => {
                        const id = parseInt(row.dataset.msgId, 10);
                        const tick = row.querySelector('.msg-status-tick');
                        if (!isNaN(id) && id <= maxLeido && tick && !tick.innerHTML.includes('Leído')) {
                            tick.innerHTML = '&check;&check; Leído';
                        }
                    });
                }
                if (typeof data.total_no_leidos !== 'undefined') updateHeaderBadge(data.total_no_leidos);
            } catch {} finally { isPolling = false; }
        };

        setInterval(pollNuevos, 800);
        subscribeSync(pollNuevos);
        document.addEventListener('visibilitychange', () => { if (!document.hidden) pollNuevos(); });
    }

    // 2. Bandeja de entrada en vivo (/mensajes)
    if (inboxContainer) {
        const baseUrl   = toSameOriginUrl(inboxContainer.dataset.baseUrl || '/');
        const cleanBase = baseUrl.endsWith('/') ? baseUrl : `${baseUrl}/`;
        const sendUrl   = toSameOriginUrl(inboxContainer.dataset.sendUrl || `${cleanBase}mensajes/enviar`);
        const inboxList = document.getElementById('inboxList');

        const lineHtml = (label, msg, fecha, isOwn) => `
            <div class="inbox-msg-line${isOwn ? ' is-own' : ''}">
                <div style="word-break:break-word;">
                    <strong style="color:${isOwn ? 'var(--color-primary)' : 'var(--color-ink)'};font-size:0.8rem;">${escapeHtml(label)}</strong>
                    <span>${escapeHtml(msg)}</span>
                </div>
                <span style="font-size:0.72rem;color:var(--color-ink-2);white-space:nowrap;flex-shrink:0;">${escapeHtml(fecha || '')}</span>
            </div>`;

        const renderRecentHtml = (conv) => {
            if (Array.isArray(conv.mensajes_recientes) && conv.mensajes_recientes.length) {
                return conv.mensajes_recientes.map((rm) => lineHtml(
                    rm.es_mio ? 'Tú:' : `${rm.emisor_nombre || conv.otra_empresa_nombre}:`,
                    rm.mensaje, rm.fecha, rm.es_mio
                )).join('');
            }
            return lineHtml(conv.es_ultimo_mio ? 'Tú:' : `${conv.otra_empresa_nombre}:`, conv.ultimo_mensaje, '', conv.es_ultimo_mio);
        };

        inboxContainer.addEventListener('submit', async (e) => {
            const form = e.target.closest('.quick-reply-form');
            if (!form) return;
            e.preventDefault();

            const input  = form.querySelector('.quick-reply-input');
            const btn    = form.querySelector('button[type="submit"]');
            const status = form.querySelector('.quick-reply-status');
            const texto  = input?.value.trim() || '';
            if (!texto) { input?.focus(); return; }

            const formData = new FormData(form);
            formData.set('mensaje', texto);

            const card = form.closest('.inbox-conv-card');
            const prev = card?.querySelector('.inbox-recent-messages, .inbox-last-msg');
            if (prev) {
                const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                prev.insertAdjacentHTML('beforeend', lineHtml('Tú:', texto, now, true));
                prev.scrollTop = prev.scrollHeight;
            }
            const timeEl = card?.querySelector('.inbox-last-time');
            if (timeEl) timeEl.textContent = 'Ahora mismo';

            input.value = '';
            input.focus();
            if (btn) btn.disabled = true;
            if (status) { status.textContent = 'Enviando...'; status.style.color = 'var(--color-ink-2)'; }

            try {
                const res  = await fetch(sendUrl, { method: 'POST', headers: ajaxHeaders, body: formData, credentials: 'same-origin' });
                const data = await res.json();
                if (res.ok && data.status === 'success') {
                    if (status) {
                        status.innerHTML = '&check; Enviado al instante';
                        status.style.color = 'var(--color-success-text)';
                        setTimeout(() => { if (status) status.textContent = ''; }, 2500);
                    }
                    notifyTabs({ type: 'new_message', ts: Date.now() });
                } else if (status) {
                    status.textContent = data.message || 'Error al enviar';
                    status.style.color = 'var(--color-danger)';
                }
            } catch {
                if (status) { status.textContent = 'Error de conexión'; status.style.color = 'var(--color-danger)'; }
            } finally {
                if (btn) btn.disabled = false;
            }
        });

        let isPollingInbox = false;
        const pollInbox = async () => {
            const active = document.activeElement;
            if (isPollingInbox || (active?.classList?.contains('quick-reply-input') && active.value.trim())) return;
            isPollingInbox = true;
            try {
                const res = await fetch(`${cleanBase}mensajes/bandeja?_t=${Date.now()}`, { headers: ajaxHeaders, credentials: 'same-origin', cache: 'no-store' });
                if (!res.ok) return;
                const data = await res.json();
                if (data.status !== 'success' || !Array.isArray(data.conversaciones)) return;

                updateHeaderBadge(data.total_no_leidos || 0);
                const hilosEl = document.getElementById('metricTotalConversaciones');
                const unreadEl = document.getElementById('metricTotalNoLeidos');
                if (hilosEl) hilosEl.textContent = String(data.total_hilos || 0);
                if (unreadEl) {
                    unreadEl.textContent = String(data.total_no_leidos || 0);
                    unreadEl.style.color = data.total_no_leidos > 0 ? 'var(--color-primary)' : 'var(--color-ink-2)';
                }

                [...data.conversaciones].reverse().forEach((conv) => {
                    const safeLink = toSameOriginUrl(conv.link_destino);
                    const card     = document.querySelector(`[data-conv-key="${conv.clave}"]`);
                    if (card) {
                        const prev = card.querySelector('.inbox-recent-messages, .inbox-last-msg');
                        if (prev) { prev.innerHTML = renderRecentHtml(conv); prev.scrollTop = prev.scrollHeight; }
                        const cnt  = card.querySelector('.inbox-total-count');
                        if (cnt && conv.total_mensajes) cnt.innerHTML = `&bull; ${conv.total_mensajes} mensaje(s)`;
                        const time = card.querySelector('.inbox-last-time');
                        if (time && conv.ultima_fecha) time.textContent = conv.ultima_fecha;
                        const btn  = card.querySelector('.inbox-open-btn');
                        if (btn && safeLink) {
                            btn.href      = safeLink;
                            btn.className = `btn ${conv.no_leidos > 0 ? 'btn-primary' : 'btn-secondary'} btn-sm inbox-open-btn`;
                        }
                        const badge = card.querySelector('.inbox-unread-badge');
                        if (badge) {
                            badge.style.display = conv.no_leidos > 0 ? 'inline-block' : 'none';
                            if (conv.no_leidos > 0) badge.textContent = `${conv.no_leidos} nuevo(s)`;
                            card.classList.toggle('has-unread', conv.no_leidos > 0);
                        }
                    } else if (inboxList) {
                        document.getElementById('emptyInboxPlaceholder')?.remove();
                        const newCard = document.createElement('div');
                        newCard.className = `card inbox-conv-card${conv.no_leidos > 0 ? ' has-unread' : ''}`;
                        newCard.dataset.convKey = conv.clave;
                        const trxIn  = conv.transaccion_id ? `<input type="hidden" name="transaccion_id" value="${escapeHtml(conv.transaccion_id)}">` : '';
                        const trxTag = conv.codigo_operacion ? `<span class="badge badge-info">${escapeHtml(conv.codigo_operacion)}</span>` : '';
                        const polTag = conv.tipo_polimero ? `<span class="badge badge-secondary" style="margin-left:0.35rem;">${escapeHtml(conv.tipo_polimero)}</span>` : '';
                        newCard.innerHTML = `
                            <div class="flex-between" style="align-items:flex-start;">
                                <div style="flex:1;min-width:260px;">
                                    <div class="flex-row" style="margin-bottom:0.35rem;">
                                        <h3 style="font-size:1.1rem;">${escapeHtml(conv.otra_empresa_nombre)}</h3>${trxTag}
                                        <span class="badge badge-success inbox-unread-badge" style="display:${conv.no_leidos > 0 ? 'inline-block' : 'none'};">${conv.no_leidos} nuevo(s)</span>
                                        <span class="inbox-total-count text-muted">&bull; ${conv.total_mensajes || 1} mensaje(s)</span>
                                    </div>
                                    <div class="text-sec" style="margin-bottom:0.65rem;">Lote vinculado: <strong>${escapeHtml(conv.producto_nombre)}</strong>${polTag}</div>
                                    <div class="inbox-recent-messages inbox-last-msg">${renderRecentHtml(conv)}</div>
                                    <form class="quick-reply-form flex-row" action="${escapeHtml(sendUrl)}" method="POST" style="max-width:680px;">
                                        ${trxIn}<input type="hidden" name="producto_id" value="${escapeHtml(conv.producto_id)}">
                                        <input type="hidden" name="receptor_id" value="${escapeHtml(conv.otra_empresa_id)}">
                                        <input type="text" name="mensaje" class="form-control quick-reply-input" placeholder="Respuesta rápida a ${escapeHtml(conv.otra_empresa_nombre)} (Enter para enviar)..." required minlength="1" maxlength="2000" style="flex:1;padding:0.45rem 0.75rem;">
                                        <button type="submit" class="btn btn-secondary btn-sm">Enviar</button>
                                        <span class="quick-reply-status" style="font-size:0.78rem;font-weight:600;"></span>
                                    </form>
                                </div>
                                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.75rem;">
                                    <span class="inbox-last-time text-muted">${escapeHtml(conv.ultima_fecha)}</span>
                                    <a href="${escapeHtml(safeLink)}" class="btn ${conv.no_leidos > 0 ? 'btn-primary' : 'btn-secondary'} btn-sm inbox-open-btn">Abrir Conversación &rarr;</a>
                                </div>
                            </div>`;
                        inboxList.prepend(newCard);
                    }
                });
            } catch {} finally { isPollingInbox = false; }
        };

        setInterval(pollInbox, 1200);
        subscribeSync(pollInbox);
    }
});
