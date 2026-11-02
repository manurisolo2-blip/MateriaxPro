/**
 * MateriaX Pro — Motor de Chat y Negociación en Tiempo Real (B2B Live Messaging v2.2)
 * Envío optimista instantáneo (0ms), sincronización entre pestañas (BroadcastChannel)
 * y polling ligero sin bloqueos ni recargas de página.
 */
document.addEventListener('DOMContentLoaded', () => {
    const chatContainer  = document.getElementById('chatContainer');
    const chatForm       = document.getElementById('chatForm');
    const mensajeInput   = document.getElementById('chatInput');
    const inboxContainer = document.getElementById('inboxContainer');

    // Normalizar URL al mismo origen actual para evitar fallos CORS entre localhost / IP LAN / HTTPS
    const toSameOriginUrl = (rawUrl) => {
        if (!rawUrl) return '';
        try {
            const parsed = new URL(rawUrl, window.location.origin);
            return parsed.pathname + parsed.search;
        } catch (e) {
            return rawUrl;
        }
    };

    // Escapar texto HTML para prevenir XSS sin doble escape
    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    };

    // Actualizar insignia de mensajes no leídos en la barra de navegación superior
    const updateHeaderBadge = (totalNoLeidos) => {
        const badges = document.querySelectorAll('.badge-mensajes-live');
        badges.forEach(badge => {
            if (totalNoLeidos > 0) {
                badge.textContent = totalNoLeidos;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        });
    };

    // Canal de comunicación instantánea entre pestañas del mismo navegador (latencia < 1ms)
    let broadcastChannel = null;
    try {
        if ('BroadcastChannel' in window) {
            broadcastChannel = new BroadcastChannel('materiax_b2b_chat');
        }
    } catch (e) {
        broadcastChannel = null;
    }

    const notifyOtherTabs = (payload) => {
        try {
            if (broadcastChannel) {
                broadcastChannel.postMessage(payload);
            }
            localStorage.setItem('materiax_chat_sync_event', JSON.stringify({ ...payload, ts: Date.now() }));
        } catch (e) {
            // Ignorar si el navegador restringe storage
        }
    };

    // =========================================================================
    // 1. MÓDULO DE CONVERSACIÓN EN VIVO (Chat dedicado, Solicitudes y Lotes)
    // =========================================================================
    if (chatContainer && chatForm && mensajeInput) {
        const transaccionId = chatContainer.dataset.transaccionId || '';
        const productoId    = chatContainer.dataset.productoId || '';
        const receptorId    = chatContainer.dataset.receptorId || '';
        const currentUserId = parseInt(chatContainer.dataset.userId || '0', 10);
        const baseUrl       = toSameOriginUrl(chatContainer.dataset.baseUrl || '/');

        const getUltimoId = () => {
            const messages = chatContainer.querySelectorAll('[data-msg-id]');
            let maxId = 0;
            messages.forEach(el => {
                const id = parseInt(el.dataset.msgId, 10);
                if (!isNaN(id) && id > maxId) {
                    maxId = id;
                }
            });
            return maxId;
        };

        let ultimoId = getUltimoId();

        const updateMessageCountUI = () => {
            const countEl = document.getElementById('chatMsgCount');
            if (countEl) {
                const total = chatContainer.querySelectorAll('.chat-bubble-row').length;
                countEl.textContent = `${total} mensaje(s)`;
            }
        };

        const removeEmptyPlaceholder = () => {
            const emptyEl = document.getElementById('emptyChatPlaceholder');
            if (emptyEl) {
                emptyEl.remove();
            }
        };

        const scrollToBottom = (smooth = true) => {
            try {
                chatContainer.scrollTo({
                    top: chatContainer.scrollHeight + 200,
                    behavior: smooth ? 'smooth' : 'auto'
                });
            } catch (e) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        };

        // Posicionar al final al cargar la vista
        scrollToBottom(false);

        // Construir e insertar burbuja de mensaje con diseño coherente al sistema
        const appendMessage = (msg, isPending = false) => {
            removeEmptyPlaceholder();

            const isOwn = Boolean(msg.es_propio || parseInt(msg.emisor_id, 10) === currentUserId);
            const msgDiv = document.createElement('div');
            msgDiv.className = 'chat-bubble-row';
            msgDiv.id = msg.id ? `msg-${msg.id}` : `msg-temp-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;

            if (msg.id) {
                msgDiv.dataset.msgId = String(msg.id);
            }
            if (isPending) {
                msgDiv.dataset.pending = '1';
                msgDiv.dataset.pendingText = String(msg.mensaje).trim();
            }

            msgDiv.style.display = 'flex';
            msgDiv.style.flexDirection = 'column';
            msgDiv.style.alignItems = isOwn ? 'flex-end' : 'flex-start';
            msgDiv.style.opacity = isPending ? '0.78' : '1';
            msgDiv.style.transition = 'opacity 0.15s ease';

            const senderLabel = isOwn ? 'Tú' : (msg.emisor_nombre || 'Contraparte');
            const bubbleStyle = isOwn
                ? 'background-color: var(--color-primary); color: #ffffff; border-bottom-right-radius: 2px;'
                : 'background-color: var(--color-paper-surface); color: var(--color-ink); border: 1px solid var(--color-rule); border-bottom-left-radius: 2px;';
            const metaColor = isOwn ? 'rgba(255,255,255,0.82)' : 'var(--color-ink-2)';

            let statusTick = '';
            if (isOwn) {
                if (isPending) {
                    statusTick = '<span class="msg-status-tick" style="margin-left: 0.25rem;">Enviando...</span>';
                } else if (parseInt(msg.leido || 0, 10) === 1) {
                    statusTick = '<span class="msg-status-tick" style="margin-left: 0.25rem;">&check;&check; Leído</span>';
                } else {
                    statusTick = '<span class="msg-status-tick" style="margin-left: 0.25rem;">&check; Enviado</span>';
                }
            }

            msgDiv.innerHTML = `
                <div style="font-size: 0.75rem; color: var(--color-ink-2); margin-bottom: 0.2rem; padding: 0 0.25rem; text-transform: uppercase; font-weight: 600;">
                    ${escapeHtml(senderLabel)}
                </div>
                <div class="chat-bubble-box" style="max-width: 80%; padding: 0.75rem 1rem; border-radius: 12px; font-size: 0.92rem; line-height: 1.5; word-break: break-word; ${bubbleStyle}">
                    <div class="chat-bubble-text">${escapeHtml(msg.mensaje).replace(/\n/g, '<br>')}</div>
                    <div class="chat-bubble-meta" style="font-size: 0.7rem; color: ${metaColor}; text-align: right; margin-top: 0.35rem;">
                        <span class="msg-time">${escapeHtml(msg.created_at || 'Ahora')}</span>
                        ${statusTick}
                    </div>
                </div>
            `;

            chatContainer.appendChild(msgDiv);
            updateMessageCountUI();
            scrollToBottom(true);
            return msgDiv;
        };

        // Confirmar burbuja pendiente con los datos definitivos del servidor
        const confirmPendingNode = (node, serverMsg) => {
            const realId = parseInt(serverMsg.id, 10);
            const existing = document.getElementById(`msg-${realId}`);
            if (existing && existing !== node) {
                node.remove();
                updateMessageCountUI();
                return existing;
            }

            node.id = `msg-${realId}`;
            node.dataset.msgId = String(realId);
            delete node.dataset.pending;
            delete node.dataset.pendingText;
            node.style.opacity = '1';

            const timeEl = node.querySelector('.msg-time');
            if (timeEl && serverMsg.created_at) {
                timeEl.textContent = serverMsg.created_at;
            }
            const tickEl = node.querySelector('.msg-status-tick');
            if (tickEl) {
                tickEl.innerHTML = parseInt(serverMsg.leido || 0, 10) === 1 ? '&check;&check; Leído' : '&check; Enviado';
            }

            if (realId > ultimoId) {
                ultimoId = realId;
            }
            return node;
        };

        // Actualizar tildes de lectura en mensajes propios cuando la contraparte los lee
        const updateReadReceipts = (maxLeidoId) => {
            if (!maxLeidoId || maxLeidoId <= 0) return;
            const rows = chatContainer.querySelectorAll('[data-msg-id]');
            rows.forEach(row => {
                const id = parseInt(row.dataset.msgId, 10);
                if (!isNaN(id) && id <= maxLeidoId) {
                    const tick = row.querySelector('.msg-status-tick');
                    if (tick && !tick.innerHTML.includes('Leído')) {
                        tick.innerHTML = '&check;&check; Leído';
                    }
                }
            });
        };

        // Función central de envío instantáneo
        const sendMessage = async () => {
            const texto = mensajeInput.value.trim();
            if (texto.length < 1) {
                mensajeInput.focus();
                return;
            }

            // Capturar datos del formulario antes de limpiar el campo
            const formData = new FormData(chatForm);
            formData.set('mensaje', texto);

            // Limpiar input al instante (0ms) para permitir escribir el siguiente mensaje sin esperar
            mensajeInput.value = '';
            mensajeInput.focus();

            const nowStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const tempNode = appendMessage({
                id: null,
                mensaje: texto,
                emisor_id: currentUserId,
                emisor_nombre: 'Tú',
                created_at: nowStr,
                es_propio: true,
                leido: 0
            }, true);

            try {
                const sendUrl = toSameOriginUrl(chatForm.action);
                const res = await fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await res.json();
                if (res.ok && data.status === 'success' && data.data) {
                    confirmPendingNode(tempNode, data.data);
                    notifyOtherTabs({
                        type: 'new_message',
                        transaccionId,
                        productoId,
                        msgId: data.data.id
                    });
                } else {
                    tempNode.style.opacity = '1';
                    const box = tempNode.querySelector('.chat-bubble-box');
                    if (box) {
                        box.style.backgroundColor = '#dc2626';
                    }
                    const tick = tempNode.querySelector('.msg-status-tick');
                    if (tick) {
                        tick.textContent = `Error: ${data.message || 'No enviado'}`;
                    }
                }
            } catch (err) {
                tempNode.style.opacity = '1';
                const box = tempNode.querySelector('.chat-bubble-box');
                if (box) {
                    box.style.backgroundColor = '#dc2626';
                }
                const tick = tempNode.querySelector('.msg-status-tick');
                if (tick) {
                    tick.textContent = 'Error de red al enviar';
                }
            }
        };

        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            sendMessage();
        });

        mensajeInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Polling ultrarrápido de mensajes entrantes y estado de lectura
        let isPolling = false;
        const pollNuevos = async () => {
            if (isPolling) return;
            isPolling = true;

            try {
                const cleanBase = baseUrl.endsWith('/') ? baseUrl : (baseUrl + '/');
                let url = `${cleanBase}mensajes/nuevos?ultimo_id=${ultimoId}&_t=${Date.now()}`;
                if (transaccionId) {
                    url += `&transaccion_id=${encodeURIComponent(transaccionId)}`;
                } else if (productoId) {
                    url += `&producto_id=${encodeURIComponent(productoId)}`;
                    if (receptorId) {
                        url += `&otra_empresa_id=${encodeURIComponent(receptorId)}`;
                    }
                }

                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.status === 'success') {
                        if (Array.isArray(data.nuevos) && data.nuevos.length > 0) {
                            data.nuevos.forEach(nuevo => {
                                const msgId = parseInt(nuevo.id, 10);
                                if (document.getElementById(`msg-${msgId}`)) {
                                    if (msgId > ultimoId) ultimoId = msgId;
                                    return;
                                }

                                // Si es un mensaje propio que aún estaba como burbuja pendiente, vincularlo sin duplicar
                                if (nuevo.es_propio || parseInt(nuevo.emisor_id, 10) === currentUserId) {
                                    const pendingNodes = chatContainer.querySelectorAll('[data-pending="1"]');
                                    for (const pNode of pendingNodes) {
                                        if (pNode.dataset.pendingText === String(nuevo.mensaje).trim()) {
                                            confirmPendingNode(pNode, nuevo);
                                            return;
                                        }
                                    }
                                }

                                appendMessage(nuevo, false);
                                if (msgId > ultimoId) {
                                    ultimoId = msgId;
                                }
                            });
                        }

                        if (data.max_leido_id) {
                            updateReadReceipts(parseInt(data.max_leido_id, 10));
                        }

                        if (typeof data.total_no_leidos !== 'undefined') {
                            updateHeaderBadge(data.total_no_leidos);
                        }
                    }
                }
            } catch (err) {
                // Silencioso ante microcortes de red
            } finally {
                isPolling = false;
            }
        };

        // Polling cada 800ms para respuesta en tiempo real (< 1 segundo)
        setInterval(pollNuevos, 800);

        // Disparo inmediato al recibir aviso por BroadcastChannel o foco de ventana
        if (broadcastChannel) {
            broadcastChannel.addEventListener('message', () => {
                pollNuevos();
            });
        }
        window.addEventListener('storage', (e) => {
            if (e.key === 'materiax_chat_sync_event') {
                pollNuevos();
            }
        });
        window.addEventListener('focus', pollNuevos);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) pollNuevos();
        });
    }

    // =========================================================================
    // 2. MÓDULO DE BANDEJA DE ENTRADA EN VIVO (/mensajes) + RESPUESTA RÁPIDA
    // =========================================================================
    if (inboxContainer) {
        const baseUrl   = toSameOriginUrl(inboxContainer.dataset.baseUrl || '/');
        const sendUrl   = toSameOriginUrl(inboxContainer.dataset.sendUrl || `${baseUrl}mensajes/enviar`);
        const inboxList = document.getElementById('inboxList');

        const renderRecentMessagesHtml = (conv) => {
            if (Array.isArray(conv.mensajes_recientes) && conv.mensajes_recientes.length > 0) {
                return conv.mensajes_recientes.map(rm => {
                    const rowBg = rm.es_mio
                        ? 'background-color: rgba(25, 95, 65, 0.08);'
                        : 'background-color: var(--color-paper-surface); border: 1px solid var(--color-rule);';
                    const nameColor = rm.es_mio ? 'var(--color-primary)' : 'var(--color-ink)';
                    const label = rm.es_mio ? 'Tú:' : `${escapeHtml(rm.emisor_nombre || conv.otra_empresa_nombre)}:`;
                    return `
                        <div style="font-size: 0.88rem; line-height: 1.4; display: flex; justify-content: space-between; gap: 0.75rem; padding: 0.25rem 0.45rem; border-radius: 6px; ${rowBg}">
                            <div style="word-break: break-word;">
                                <strong style="color: ${nameColor}; font-size: 0.8rem;">${label}</strong>
                                <span style="color: var(--color-ink);">${escapeHtml(rm.mensaje)}</span>
                            </div>
                            <span style="font-size: 0.72rem; color: var(--color-ink-2); white-space: nowrap; flex-shrink: 0;">
                                ${escapeHtml(rm.fecha || '')}
                            </span>
                        </div>
                    `;
                }).join('');
            }
            const prefix = conv.es_ultimo_mio ? 'Tú: ' : `${escapeHtml(conv.otra_empresa_nombre)}: `;
            return `<div style="font-size: 0.88rem; color: var(--color-ink);"><strong style="color: var(--color-ink-2); font-size: 0.8rem;">${prefix}</strong>${escapeHtml(conv.ultimo_mensaje)}</div>`;
        };

        // Delegación de eventos para formularios de respuesta rápida dentro de /mensajes
        inboxContainer.addEventListener('submit', async (e) => {
            const form = e.target.closest('.quick-reply-form');
            if (!form) return;
            e.preventDefault();

            const input  = form.querySelector('.quick-reply-input');
            const btn    = form.querySelector('button[type="submit"]');
            const status = form.querySelector('.quick-reply-status');
            if (!input) return;

            const texto = input.value.trim();
            if (texto.length < 1) {
                input.focus();
                return;
            }

            const formData = new FormData(form);
            formData.set('mensaje', texto);

            // Actualizar vista previa de la tarjeta de conversación al instante (0ms)
            const card = form.closest('.inbox-conv-card');
            if (card) {
                const previewEl = card.querySelector('.inbox-recent-messages') || card.querySelector('.inbox-last-msg');
                if (previewEl) {
                    const nowShort = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const rowDiv = document.createElement('div');
                    rowDiv.style.cssText = 'font-size: 0.88rem; line-height: 1.4; display: flex; justify-content: space-between; gap: 0.75rem; padding: 0.25rem 0.45rem; border-radius: 6px; background-color: rgba(25, 95, 65, 0.08);';
                    rowDiv.innerHTML = `
                        <div style="word-break: break-word;">
                            <strong style="color: var(--color-primary); font-size: 0.8rem;">Tú:</strong>
                            <span style="color: var(--color-ink);">${escapeHtml(texto)}</span>
                        </div>
                        <span style="font-size: 0.72rem; color: var(--color-ink-2); white-space: nowrap; flex-shrink: 0;">${escapeHtml(nowShort)}</span>
                    `;
                    previewEl.appendChild(rowDiv);
                    previewEl.scrollTop = previewEl.scrollHeight;
                }
                const timeEl = card.querySelector('.inbox-last-time');
                if (timeEl) {
                    timeEl.textContent = 'Ahora mismo';
                }
            }

            input.value = '';
            input.focus();
            if (btn) btn.disabled = true;
            if (status) {
                status.textContent = 'Enviando...';
                status.style.color = 'var(--color-ink-2)';
            }

            try {
                const res = await fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok && data.status === 'success') {
                    if (status) {
                        status.innerHTML = '&check; Enviado al instante';
                        status.style.color = 'var(--color-success-text)';
                        setTimeout(() => {
                            if (status) status.textContent = '';
                        }, 2500);
                    }
                    notifyOtherTabs({ type: 'new_message', ts: Date.now() });
                } else if (status) {
                    status.textContent = data.message || 'Error al enviar';
                    status.style.color = '#dc2626';
                }
            } catch (err) {
                if (status) {
                    status.textContent = 'Error de conexión';
                    status.style.color = '#dc2626';
                }
            } finally {
                if (btn) btn.disabled = false;
            }
        });

        // Sincronización automática de la bandeja de conversaciones sin recargar
        let isPollingInbox = false;
        const pollInbox = async () => {
            if (isPollingInbox) return;
            // No re-renderizar si el usuario está escribiendo activamente en un input de respuesta rápida
            const activeEl = document.activeElement;
            if (activeEl && activeEl.classList && activeEl.classList.contains('quick-reply-input') && activeEl.value.trim().length > 0) {
                return;
            }

            isPollingInbox = true;
            try {
                const cleanBase = baseUrl.endsWith('/') ? baseUrl : (baseUrl + '/');
                const res = await fetch(`${cleanBase}mensajes/bandeja?_t=${Date.now()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.status === 'success' && Array.isArray(data.conversaciones)) {
                        updateHeaderBadge(data.total_no_leidos || 0);

                        const totalHilosEl    = document.getElementById('metricTotalConversaciones');
                        const totalNoLeidosEl = document.getElementById('metricTotalNoLeidos');
                        if (totalHilosEl) totalHilosEl.textContent = String(data.total_hilos || 0);
                        if (totalNoLeidosEl) {
                            totalNoLeidosEl.textContent = String(data.total_no_leidos || 0);
                            totalNoLeidosEl.style.color = (data.total_no_leidos > 0) ? 'var(--color-primary)' : 'var(--color-ink-2)';
                        }

                        // Actualizar cada tarjeta existente o insertar las nuevas manteniendo el orden cronológico
                        const reversedConvs = [...data.conversaciones].reverse();
                        reversedConvs.forEach(conv => {
                            const safeLink = toSameOriginUrl(conv.link_destino);
                            const existingCard = document.querySelector(`[data-conv-key="${conv.clave}"]`);
                            if (existingCard) {
                                const previewEl = existingCard.querySelector('.inbox-recent-messages') || existingCard.querySelector('.inbox-last-msg');
                                if (previewEl) {
                                    previewEl.innerHTML = renderRecentMessagesHtml(conv);
                                    previewEl.scrollTop = previewEl.scrollHeight;
                                }
                                const totalCountEl = existingCard.querySelector('.inbox-total-count');
                                if (totalCountEl && conv.total_mensajes) {
                                    totalCountEl.innerHTML = `&bull; ${conv.total_mensajes} mensaje(s)`;
                                }
                                const timeEl = existingCard.querySelector('.inbox-last-time');
                                if (timeEl && conv.ultima_fecha) {
                                    timeEl.textContent = conv.ultima_fecha;
                                }
                                const openBtn = existingCard.querySelector('.inbox-open-btn');
                                if (openBtn && safeLink) {
                                    openBtn.href = safeLink;
                                    openBtn.className = `btn ${conv.no_leidos > 0 ? 'btn-primary' : 'btn-secondary'} btn-sm inbox-open-btn`;
                                }
                                const unreadBadge = existingCard.querySelector('.inbox-unread-badge');
                                if (unreadBadge) {
                                    if (conv.no_leidos > 0) {
                                        unreadBadge.textContent = `${conv.no_leidos} nuevo(s)`;
                                        unreadBadge.style.display = 'inline-block';
                                        existingCard.style.borderLeft = '4px solid var(--color-primary)';
                                    } else {
                                        unreadBadge.style.display = 'none';
                                        existingCard.style.borderLeft = '';
                                    }
                                }
                            } else if (inboxList) {
                                // Si apareció una conversación completamente nueva que no estaba en el DOM, insertarla completa arriba
                                const emptyInbox = document.getElementById('emptyInboxPlaceholder');
                                if (emptyInbox) emptyInbox.remove();

                                const newCard = document.createElement('div');
                                newCard.className = 'card inbox-conv-card';
                                newCard.dataset.convKey = conv.clave;
                                newCard.style.cssText = `padding: 1.25rem 1.5rem; transition: transform 0.15s ease; ${conv.no_leidos > 0 ? 'border-left: 4px solid var(--color-primary); background-color: var(--color-paper-surface);' : ''}`;
                                const trxInput = conv.transaccion_id ? `<input type="hidden" name="transaccion_id" value="${escapeHtml(conv.transaccion_id)}">` : '';
                                const trxBadge = conv.codigo_operacion ? `<span class="badge badge-info" style="font-size: 0.78rem;">${escapeHtml(conv.codigo_operacion)}</span>` : '';
                                const polBadge = conv.tipo_polimero ? `<span class="badge badge-secondary" style="font-size: 0.72rem; margin-left: 0.35rem;">${escapeHtml(conv.tipo_polimero)}</span>` : '';
                                newCard.innerHTML = `
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                                        <div style="flex: 1; min-width: 260px;">
                                            <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap;">
                                                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-ink); margin: 0;">${escapeHtml(conv.otra_empresa_nombre)}</h3>
                                                ${trxBadge}
                                                <span class="badge badge-success inbox-unread-badge" style="font-size: 0.78rem; ${conv.no_leidos > 0 ? '' : 'display: none;'}">${conv.no_leidos} nuevo(s)</span>
                                                <span class="inbox-total-count" style="font-size: 0.78rem; color: var(--color-ink-2);">&bull; ${conv.total_mensajes || 1} mensaje(s)</span>
                                            </div>
                                            <div style="font-size: 0.88rem; color: var(--color-ink-2); margin-bottom: 0.65rem;">
                                                Lote vinculado: <strong>${escapeHtml(conv.producto_nombre)}</strong>${polBadge}
                                            </div>
                                            <div class="inbox-recent-messages inbox-last-msg" style="display: flex; flex-direction: column; gap: 0.4rem; background-color: var(--color-paper-2); padding: 0.75rem 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--color-rule); max-width: 680px; margin-bottom: 0.75rem; max-height: 185px; overflow-y: auto;">
                                                ${renderRecentMessagesHtml(conv)}
                                            </div>
                                            <form class="quick-reply-form" action="${escapeHtml(sendUrl)}" method="POST" style="display: flex; gap: 0.5rem; align-items: center; max-width: 680px; margin: 0;">
                                                ${trxInput}
                                                <input type="hidden" name="producto_id" value="${escapeHtml(conv.producto_id)}">
                                                <input type="hidden" name="receptor_id" value="${escapeHtml(conv.otra_empresa_id)}">
                                                <input type="text" name="mensaje" class="form-control quick-reply-input" placeholder="Respuesta rápida a ${escapeHtml(conv.otra_empresa_nombre)} (Enter para enviar)..." required minlength="1" maxlength="2000" style="flex: 1; padding: 0.45rem 0.75rem; font-size: 0.86rem;">
                                                <button type="submit" class="btn btn-secondary btn-sm" style="white-space: nowrap;">Enviar</button>
                                                <span class="quick-reply-status" style="font-size: 0.78rem; font-weight: 600; white-space: nowrap;"></span>
                                            </form>
                                        </div>
                                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem;">
                                            <span class="inbox-last-time" style="font-size: 0.8rem; color: var(--color-ink-2);">${escapeHtml(conv.ultima_fecha)}</span>
                                            <a href="${escapeHtml(safeLink)}" class="btn ${conv.no_leidos > 0 ? 'btn-primary' : 'btn-secondary'} btn-sm inbox-open-btn">Abrir Conversación &rarr;</a>
                                        </div>
                                    </div>
                                `;
                                inboxList.prepend(newCard);
                            }
                        });
                    }
                }
            } catch (err) {
                // Silencioso
            } finally {
                isPollingInbox = false;
            }
        };

        setInterval(pollInbox, 1200);
        if (broadcastChannel) {
            broadcastChannel.addEventListener('message', pollInbox);
        }
        window.addEventListener('storage', (e) => {
            if (e.key === 'materiax_chat_sync_event') pollInbox();
        });
        window.addEventListener('focus', pollInbox);
    }
});
