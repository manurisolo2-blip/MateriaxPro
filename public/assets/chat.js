/**
 * MateriaX Pro — Motor de Chat y Negociación en Tiempo Real (B2B Live Messaging)
 * Sincronización instantánea de mensajes y actualización fluida sin recargar la página.
 */
document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.getElementById('chatContainer');
    const chatForm      = document.getElementById('chatForm');
    const mensajeInput  = document.getElementById('chatInput');
    const chatStatus    = document.getElementById('chatStatus');

    if (!chatContainer || !chatForm || !mensajeInput) {
        return;
    }

    const transaccionId = chatContainer.dataset.transaccionId || '';
    const productoId    = chatContainer.dataset.productoId || '';
    const currentUserId = parseInt(chatContainer.dataset.userId || '0', 10);
    const baseUrl       = chatContainer.dataset.baseUrl || '';

    // Función para obtener el ID del mensaje más reciente en el DOM
    const getUltimoId = () => {
        const messages = chatContainer.querySelectorAll('[data-msg-id]');
        if (messages.length === 0) return 0;
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

    // Scroll automático al fondo
    const scrollToBottom = (smooth = true) => {
        chatContainer.scrollTo({
            top: chatContainer.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto'
        });
    };

    // Scroll inicial inmediato
    scrollToBottom(false);

    // Escapar texto HTML para evitar XSS
    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    // Renderizar una burbuja de mensaje en el contenedor
    const appendMessage = (msg, isPending = false) => {
        const isOwn = msg.es_propio || msg.emisor_id === currentUserId;
        const msgDiv = document.createElement('div');
        msgDiv.id = `msg-${msg.id || 'temp-' + Date.now()}`;
        if (msg.id) {
            msgDiv.dataset.msgId = msg.id;
        }

        msgDiv.style.display = 'flex';
        msgDiv.style.flexDirection = 'column';
        msgDiv.style.alignItems = isOwn ? 'flex-end' : 'flex-start';
        msgDiv.style.marginBottom = '1rem';
        msgDiv.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
        msgDiv.style.opacity = isPending ? '0.7' : '1';

        const bubbleBg     = isOwn ? '#15803d' : '#f8fafc';
        const textColor    = isOwn ? '#ffffff' : '#0f172a';
        const borderColor  = isOwn ? 'transparent' : '#e2e8f0';
        const metaColor    = isOwn ? 'rgba(255,255,255,0.75)' : '#64748b';
        const senderLabel  = isOwn ? 'Tú' : (msg.emisor_nombre || 'Contraparte');

        msgDiv.innerHTML = `
            <div style="font-size: 0.75rem; font-weight: 600; color: ${isOwn ? '#15803d' : '#475569'}; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.05em;">
                ${escapeHtml(senderLabel)}
            </div>
            <div style="max-width: 80%; background: ${bubbleBg}; color: ${textColor}; padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid ${borderColor}; box-shadow: 0 1px 3px rgba(0,0,0,0.05); line-height: 1.45; word-break: break-word; font-size: 0.925rem;">
                ${escapeHtml(msg.mensaje).replace(/\n/g, '<br>')}
                <div style="font-size: 0.7rem; color: ${metaColor}; text-align: right; margin-top: 0.35rem;">
                    ${msg.created_at || 'Justo ahora'} ${isPending ? '⏳' : '✓'}
                </div>
            </div>
        `;

        chatContainer.appendChild(msgDiv);
        scrollToBottom(true);
        return msgDiv;
    };

    // Actualizar badge de mensajes no leídos en la cabecera
    const updateHeaderBadge = (totalNoLeidos) => {
        const badge = document.querySelector('.badge-mensajes-live');
        if (badge) {
            if (totalNoLeidos > 0) {
                badge.textContent = totalNoLeidos;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    };

    // Manejo de envío de mensaje por AJAX (Sin recargar página)
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const texto = mensajeInput.value.trim();
        if (texto.length < 2) {
            mensajeInput.focus();
            return;
        }

        // Limpiar input inmediatamente para máxima fluidez
        mensajeInput.value = '';
        mensajeInput.focus();

        // Burbuja optimista temporal
        const tempMsg = {
            id: null,
            mensaje: texto,
            emisor_id: currentUserId,
            emisor_nombre: 'Tú',
            created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            es_propio: true
        };
        const tempNode = appendMessage(tempMsg, true);

        const formData = new FormData(chatForm);
        formData.set('mensaje', texto);

        try {
            const sendUrl = chatForm.action;
            const res = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await res.json();
            if (res.ok && data.status === 'success' && data.data) {
                // Confirmar mensaje con ID oficial
                tempNode.dataset.msgId = data.data.id;
                tempNode.id = `msg-${data.data.id}`;
                tempNode.style.opacity = '1';
                const meta = tempNode.querySelector('div div:last-child');
                if (meta) {
                    meta.innerHTML = `${data.data.created_at} ✓✓`;
                }
                if (data.data.id > ultimoId) {
                    ultimoId = data.data.id;
                }
            } else {
                tempNode.style.background = '#fef2f2';
                tempNode.style.border = '1px solid #f87171';
                const meta = tempNode.querySelector('div div:last-child');
                if (meta) {
                    meta.innerHTML = `Error: ${data.message || 'No enviado'} ⚠️`;
                }
            }
        } catch (err) {
            console.error('Error enviando mensaje:', err);
            tempNode.style.opacity = '1';
            const meta = tempNode.querySelector('div div:last-child');
            if (meta) {
                meta.innerHTML = `Error de conexión ⚠️`;
            }
        }
    });

    // Envío fluido al presionar Enter (Shift+Enter para salto de línea)
    mensajeInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    });

    // Polling en tiempo real para recibir mensajes entrantes de la contraparte
    let isPolling = false;
    const pollNuevos = async () => {
        if (isPolling) return;
        isPolling = true;

        try {
            let url = `${baseUrl}mensajes/nuevos?ultimo_id=${ultimoId}`;
            if (transaccionId) url += `&transaccion_id=${encodeURIComponent(transaccionId)}`;
            if (productoId)    url += `&producto_id=${encodeURIComponent(productoId)}`;

            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (res.ok) {
                const data = await res.json();
                if (data.status === 'success' && Array.isArray(data.nuevos)) {
                    data.nuevos.forEach(nuevo => {
                        // Evitar duplicar si ya existe en el DOM
                        if (!document.getElementById(`msg-${nuevo.id}`)) {
                            appendMessage(nuevo, false);
                            if (nuevo.id > ultimoId) {
                                ultimoId = nuevo.id;
                            }
                        }
                    });

                    if (typeof data.total_no_leidos !== 'undefined') {
                        updateHeaderBadge(data.total_no_leidos);
                    }
                }
            }
        } catch (err) {
            // Silencioso ante pérdidas transitorias de red
        } finally {
            isPolling = false;
        }
    };

    // Polling cada 1.5 segundos para sincronización en tiempo real
    setInterval(pollNuevos, 1500);
});
