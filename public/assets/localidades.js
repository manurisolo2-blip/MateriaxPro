/**
 * Selector progresivo de Provincia -> Ciudad / Localidad y formateo asistido.
 * La validación definitiva siempre ocurre del lado del servidor (App\Validation\EmpresaRules).
 */
document.addEventListener('DOMContentLoaded', () => {
    const provSelect  = document.getElementById('provincia');
    const ciudadInput = document.getElementById('ciudad');
    const datalist    = document.getElementById('lista-localidades');
    const listbox     = document.getElementById('ciudad-listbox');
    const toggleBtn   = document.getElementById('ciudad-toggle');
    const hintEl      = document.getElementById('ciudad-hint');

    const foldText = (str) => String(str || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/\s+/g, ' ')
        .trim();

    if (provSelect && ciudadInput && datalist && listbox) {
        const citiesByProv = new Map();
        const canonicalProv = new Map();

        Array.from(provSelect.options).forEach((opt) => {
            if (opt.value) {
                canonicalProv.set(foldText(opt.value), opt.value);
            }
        });

        datalist.querySelectorAll('option').forEach((opt) => {
            const city = (opt.value || '').trim();
            const prov = canonicalProv.get(foldText(opt.dataset.provincia || '')) || (opt.dataset.provincia || '').trim();
            if (!city || !prov || /^\d+$/.test(city)) return;
            if (!citiesByProv.has(prov)) citiesByProv.set(prov, []);
            citiesByProv.get(prov).push({ name: city, fold: foldText(city) });
        });

        citiesByProv.forEach((items) => {
            items.sort((a, b) => a.name.localeCompare(b.name, 'es', { sensitivity: 'base' }));
        });

        // Desactivar datalist nativo para evitar duplicados/fallos sin filtro por provincia
        ciudadInput.removeAttribute('list');
        ciudadInput.setAttribute('role', 'combobox');
        ciudadInput.setAttribute('aria-autocomplete', 'list');
        ciudadInput.setAttribute('aria-controls', 'ciudad-listbox');
        ciudadInput.setAttribute('aria-expanded', 'false');

        let activeIdx = -1;
        let currentMatches = [];

        const getProvList = () => citiesByProv.get(provSelect.value.trim()) || [];

        const closeListbox = () => {
            listbox.hidden = true;
            listbox.innerHTML = '';
            activeIdx = -1;
            ciudadInput.setAttribute('aria-expanded', 'false');
        };

        const setActive = (idx) => {
            const opts = listbox.querySelectorAll('.city-option[data-city]');
            opts.forEach((el) => el.classList.remove('is-active'));
            if (idx >= 0 && idx < opts.length) {
                activeIdx = idx;
                opts[idx].classList.add('is-active');
                opts[idx].scrollIntoView({ block: 'nearest' });
            } else {
                activeIdx = -1;
            }
        };

        const selectCity = (cityName) => {
            ciudadInput.value = cityName;
            ciudadInput.setCustomValidity('');
            closeListbox();
        };

        const renderListbox = (showAll = false) => {
            const list = getProvList();
            if (!list.length) {
                closeListbox();
                return;
            }

            const q = showAll ? '' : foldText(ciudadInput.value);
            if (!q) {
                currentMatches = list;
            } else {
                const starts = [];
                const contains = [];
                list.forEach((item) => {
                    if (item.fold.startsWith(q)) starts.push(item);
                    else if (item.fold.includes(q)) contains.push(item);
                });
                currentMatches = starts.concat(contains);
            }

            listbox.innerHTML = '';
            if (!currentMatches.length) {
                const empty = document.createElement('li');
                empty.className = 'city-option is-empty';
                empty.textContent = `Sin coincidencias en ${provSelect.value.trim()}`;
                listbox.appendChild(empty);
            } else {
                const frag = document.createDocumentFragment();
                currentMatches.forEach((item) => {
                    const li = document.createElement('li');
                    li.className = 'city-option';
                    li.setAttribute('role', 'option');
                    li.dataset.city = item.name;
                    li.textContent = item.name;
                    if (foldText(ciudadInput.value) === item.fold) {
                        li.classList.add('is-selected');
                    }
                    frag.appendChild(li);
                });
                listbox.appendChild(frag);
            }

            activeIdx = -1;
            listbox.hidden = false;
            ciudadInput.setAttribute('aria-expanded', 'true');
        };

        const validateCity = (autoFixCase = true) => {
            const prov = provSelect.value.trim();
            const val  = ciudadInput.value.trim();
            if (!prov || !val) {
                ciudadInput.setCustomValidity('');
                return true;
            }
            const list  = getProvList();
            const exact = list.find((item) => item.fold === foldText(val));
            if (exact) {
                if (autoFixCase) ciudadInput.value = exact.name;
                ciudadInput.setCustomValidity('');
                return true;
            }
            ciudadInput.setCustomValidity(`Elegí una localidad válida de ${prov} desde la lista.`);
            return false;
        };

        const syncProvinceState = (clearOnChange) => {
            const prov = provSelect.value.trim();
            const list = getProvList();
            closeListbox();

            if (!prov || !list.length) {
                ciudadInput.value = '';
                ciudadInput.disabled = true;
                ciudadInput.placeholder = 'Primero elegí una provincia';
                ciudadInput.setCustomValidity('');
                if (toggleBtn) toggleBtn.disabled = true;
                if (hintEl) hintEl.textContent = 'Seleccioná primero tu provincia para ver las localidades disponibles.';
                return;
            }

            ciudadInput.disabled = false;
            if (toggleBtn) toggleBtn.disabled = false;
            ciudadInput.placeholder = `Escribí o elegí localidad de ${prov}...`;
            if (hintEl) hintEl.textContent = `${list.length} localidades disponibles en ${prov} (podés escribir sin tildes).`;

            if (clearOnChange) {
                const currentFold = foldText(ciudadInput.value);
                const stillValid  = currentFold !== '' && list.some((item) => item.fold === currentFold);
                if (!stillValid) {
                    ciudadInput.value = '';
                }
                ciudadInput.setCustomValidity('');
            } else if (ciudadInput.value.trim() !== '') {
                validateCity(true);
            }
        };

        provSelect.addEventListener('change', () => syncProvinceState(true));
        ciudadInput.addEventListener('focus', () => renderListbox(ciudadInput.value.trim() === ''));
        ciudadInput.addEventListener('input', () => {
            ciudadInput.setCustomValidity('');
            renderListbox(false);
        });
        ciudadInput.addEventListener('blur', () => {
            setTimeout(() => {
                if (!listbox.contains(document.activeElement)) {
                    validateCity(true);
                    closeListbox();
                }
            }, 120);
        });

        ciudadInput.addEventListener('keydown', (e) => {
            if (listbox.hidden && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                renderListbox(ciudadInput.value.trim() === '');
            }
            const opts = listbox.querySelectorAll('.city-option[data-city]');
            if (e.key === 'ArrowDown' && opts.length) {
                e.preventDefault();
                setActive(activeIdx + 1 < opts.length ? activeIdx + 1 : 0);
            } else if (e.key === 'ArrowUp' && opts.length) {
                e.preventDefault();
                setActive(activeIdx > 0 ? activeIdx - 1 : opts.length - 1);
            } else if (e.key === 'Enter' && !listbox.hidden) {
                if (activeIdx >= 0 && opts[activeIdx]) {
                    e.preventDefault();
                    selectCity(opts[activeIdx].dataset.city);
                } else if (currentMatches.length === 1) {
                    e.preventDefault();
                    selectCity(currentMatches[0].name);
                }
            } else if (e.key === 'Escape') {
                closeListbox();
            }
        });

        if (toggleBtn) {
            toggleBtn.addEventListener('mousedown', (e) => e.preventDefault());
            toggleBtn.addEventListener('click', () => {
                if (ciudadInput.disabled) return;
                if (listbox.hidden) {
                    ciudadInput.focus();
                    renderListbox(true);
                } else {
                    closeListbox();
                }
            });
        }

        listbox.addEventListener('pointerdown', (e) => {
            const option = e.target.closest('.city-option[data-city]');
            if (!option) return;
            e.preventDefault();
            selectCity(option.dataset.city);
        });

        document.addEventListener('pointerdown', (e) => {
            if (!e.target.closest('[data-city-combobox]')) {
                closeListbox();
            }
        });

        const form = ciudadInput.closest('form');
        if (form) {
            form.addEventListener('submit', () => validateCity(true));
        }

        syncProvinceState(false);
    }

    // Formateo visual de CUIT al salir del campo (XX-XXXXXXXX-X)
    const cuitInput = document.getElementById('cuit');
    if (cuitInput) {
        cuitInput.addEventListener('blur', () => {
            const d = cuitInput.value.replace(/\D/g, '');
            if (d.length === 11) {
                cuitInput.value = `${d.slice(0, 2)}-${d.slice(2, 10)}-${d.slice(10)}`;
            }
        });
    }

    // Chequeo inmediato de coincidencia de contraseñas
    const passInput    = document.getElementById('password');
    const confirmInput = document.getElementById('pass_confirm');
    if (passInput && confirmInput) {
        const syncPass = () => {
            if (confirmInput.value && confirmInput.value !== passInput.value) {
                confirmInput.setCustomValidity('Las contraseñas no coinciden.');
            } else {
                confirmInput.setCustomValidity('');
            }
        };
        passInput.addEventListener('input', syncPass);
        confirmInput.addEventListener('input', syncPass);
    }
});
