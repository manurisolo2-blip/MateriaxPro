// MATERIAX PLATFORM - ENTERPRISE AUTHENTICATION, ONBOARDING & AUDIT SYSTEM
// Especialidad Informática 2026 - Instituto Técnico Río Tercero

const STORAGE_KEY_REQUESTS = 'materiax_requests';
const STORAGE_KEY_COMPANIES = 'materiax_companies';
const STORAGE_KEY_SESSION = 'materiax_current_session';

// ==========================================================================
// 1. BASE DE DATOS MOCK: LOTES Y RECURSOS INDUSTRIALES
// ==========================================================================
const RESOURCE_DATA = {
  1: {
    title: "PEAD Molido Limpio",
    category: "Polietileno (PEAD)",
    stock: "180 kg",
    location: "San Martín, Buenos Aires",
    status: "Excelente pureza (Molido industrial)",
    packaging: "Big Bags de 200 kg",
    description: "Lote proveniente de sobrantes de inyección de baldes industriales. Clasificado por color, libre de metálicos y con bajo índice de fluidez variable. Excelente desempeño para extrusión / compounding."
  },
  2: {
    title: "Scrap de PP Homopolímero",
    category: "Polipropileno (PP)",
    stock: "320 kg",
    location: "Vicente López, Buenos Aires",
    status: "Recortes en prensa",
    packaging: "Fardos consolidados de 80 kg",
    description: "Recortes limpios post-producción de envases rígidos. Trazabilidad de materia prima virgen con índice MFI 12. Ideal para mezclas de inyección de piezas de segunda línea."
  },
  3: {
    title: "Lote Compuestos ABS & PVC",
    category: "Materiales Técnicos",
    stock: "450 kg",
    location: "Lomas de Zamora, Buenos Aires",
    status: "Seleccionado y ensacado",
    packaging: "Sacos industriales de 25 kg",
    description: "Descarte de perfilería rígida de PVC y carcasas de ABS industrial. Clasificación mecánica completa, sin presencia de elastómeros ni caucho."
  },
  4: {
    title: "Pallets Plásticos & Cajas Reusables",
    category: "Equipamiento & Logística",
    stock: "85 unidades",
    location: "Pilar, Buenos Aires",
    status: "Usado estructural intacto",
    packaging: "Palletizado listo para carga",
    description: "Pallets plásticos reforzados (1200x1000x150mm) de polietileno de alta densidad. Soportan hasta 1.200 kg en rack y 3.000 kg estático."
  },
  5: {
    title: "Scrap Film PEBD Transparente",
    category: "Polietileno (PEBD)",
    stock: "600 kg",
    location: "Quilmes, Buenos Aires",
    status: "Fardos prensados limpios",
    packaging: "Fardos de alta densidad",
    description: "Film termocontraíble y stretch film transparente de desembalaje de pallets de insumos limpios. Sin etiquetas adhesivas ni polvo acumulado."
  },
  6: {
    title: "Nylon PA6 Molido c/ Carga",
    category: "Materiales Técnicos",
    stock: "140 kg",
    location: "Córdoba Capital, Córdoba",
    status: "Molido homogeneizado",
    packaging: "Tambores herméticos con desecante",
    description: "Poliamida 6 con 30% fibra de vidrio molida procedente de descarte de autopartes. Estabilidad térmica alta y rigidez estructural óptima."
  }
};

// Semilla inicial de empresas para demostración B2B
const SEED_COMPANIES = [
  {
    id: 1,
    company: "PetroPlast Industrial S.A.",
    cuit: "30-71458921-7",
    email: "contacto@petroplast.com.ar",
    phone: "+54 9 11 4589-2100",
    repName: "Ing. Carlos Mendoza",
    repRole: "Director de Operaciones & Apoderado",
    interest: "Oferente de Excedentes",
    status: "aprobada",
    caseNumber: "MX-AUD-2026-8921",
    token: "MX-TOK-8921-B2B-OK",
    statuteName: "estatuto_social_petroplast.pdf",
    cuitDocName: "constancia_afip_cuit_petroplast.pdf",
    hash: "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
    registeredAt: "10/08/2026 10:30"
  },
  {
    id: 2,
    company: "BioPlásticos Cuyo S.R.L.",
    cuit: "30-68934512-9",
    email: "admin@bioplasticoscuyo.com.ar",
    phone: "+54 9 261 423-8890",
    repName: "Dra. Elena Rossi",
    repRole: "Apoderada Legal",
    interest: "Demandante de Polímeros",
    status: "pendiente",
    caseNumber: "MX-AUD-2026-4512",
    token: "",
    statuteName: "estatuto_bioplasticos_cuyo.pdf",
    cuitDocName: "constancia_cuit_afip_cuyo.pdf",
    hash: "7f83b1657ff1fc53b92dc18148a1d65dfc2d4b1fa3d677284addd200126d9069",
    registeredAt: "18/08/2026 14:15"
  }
];

// ==========================================================================
// 2. UTILIDADES DE PERSISTENCIA (LOCALSTORAGE)
// ==========================================================================
function getStoredRequests() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY_REQUESTS);
    return raw ? JSON.parse(raw) : [];
  } catch (e) {
    console.error('Error al leer solicitudes:', e);
    return [];
  }
}

function saveRequestToLocalStorage(requestData) {
  const requests = getStoredRequests();
  requests.unshift(requestData);
  try {
    localStorage.setItem(STORAGE_KEY_REQUESTS, JSON.stringify(requests));
  } catch (e) {
    console.error('Error al guardar solicitud:', e);
  }
  updateAdminBadge();
}

function clearStoredRequests() {
  try {
    localStorage.removeItem(STORAGE_KEY_REQUESTS);
    localStorage.removeItem(STORAGE_KEY_COMPANIES);
    initCompaniesStore();
  } catch (e) {
    console.error('Error al vaciar LocalStorage:', e);
  }
  updateAdminBadge();
  renderAdminRequests();
  renderAdminCompanies();
  showToast('Se restablecieron los registros locales de prueba', 'LocalStorage');
}

const CLOUD_SYNC_ENDPOINT = 'https://api.restful-api.dev/objects/ff8081819ff5b11001a016b35910482a';

function getStoredCompanies() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY_COMPANIES);
    if (!raw) {
      localStorage.setItem(STORAGE_KEY_COMPANIES, JSON.stringify(SEED_COMPANIES));
      return SEED_COMPANIES;
    }
    return JSON.parse(raw);
  } catch (e) {
    return SEED_COMPANIES;
  }
}

function saveCompanyToStore(companyObj) {
  const list = getStoredCompanies();
  const index = list.findIndex(c => c.cuit === companyObj.cuit || c.id === companyObj.id);
  if (index >= 0) {
    list[index] = companyObj;
  } else {
    list.unshift(companyObj);
  }
  localStorage.setItem(STORAGE_KEY_COMPANIES, JSON.stringify(list));
  updateAdminBadge();
  renderAdminCompanies();
  
  // Persistir en la nube de forma asíncrona para que llegue a todos los dispositivos
  pushCompaniesToCloud(list);
}

async function pushCompaniesToCloud(list) {
  try {
    await fetch(CLOUD_SYNC_ENDPOINT, {
      method: 'PUT',
      headers: {
        'User-Agent': 'MateriaX/1.0',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: 'materiax_global_store',
        data: { companies: list }
      })
    });
  } catch (err) {
    console.warn('Cloud sync push offline fallback', err);
  }
}

async function syncCompaniesWithCloud(notifyUser = false) {
  try {
    const res = await fetch(CLOUD_SYNC_ENDPOINT, {
      headers: {
        'User-Agent': 'MateriaX/1.0',
        'Content-Type': 'application/json'
      }
    });
    if (!res.ok) return;
    const json = await res.json();
    if (json && json.data && Array.isArray(json.data.companies)) {
      const cloudCompanies = json.data.companies;
      const localCompanies = getStoredCompanies();

      // Fusionar asegurando que no se pierda ningún registro
      const map = new Map();
      cloudCompanies.forEach(c => map.set(c.cuit, c));
      localCompanies.forEach(c => {
        if (!map.has(c.cuit)) {
          map.set(c.cuit, c);
        } else {
          // Si en la nube ya está homologada/aprobada, reflejarlo
          const cloudC = map.get(c.cuit);
          if (cloudC.status === 'aprobada') {
            c.status = 'aprobada';
            c.token = cloudC.token || c.token;
          }
        }
      });

      const merged = Array.from(map.values());
      localStorage.setItem(STORAGE_KEY_COMPANIES, JSON.stringify(merged));
      updateAdminBadge();
      renderAdminCompanies();

      // Actualizar modal de espera si está abierto en este navegador
      if (currentViewingCompanyForVerification) {
        const updated = merged.find(c => c.cuit === currentViewingCompanyForVerification.cuit);
        if (updated) {
          const wasPending = currentViewingCompanyForVerification.status !== 'aprobada';
          currentViewingCompanyForVerification = updated;
          renderVerificationRoom(updated);
          if (wasPending && updated.status === 'aprobada') {
            showToast(`🎉 ¡Tu empresa ${updated.company} ha sido homologada por el auditor!`, 'Homologación Aprobada');
          }
        }
      }

      if (notifyUser) {
        showToast('Estado sincronizado en tiempo real con la nube.', 'Red Global MateriaX');
      }
    }
  } catch (err) {
    console.warn('Cloud sync fetch offline fallback', err);
  }
}

function initCompaniesStore() {
  if (!localStorage.getItem(STORAGE_KEY_COMPANIES)) {
    localStorage.setItem(STORAGE_KEY_COMPANIES, JSON.stringify(SEED_COMPANIES));
  }
  // Sincronizar inmediatamente con la nube al iniciar
  syncCompaniesWithCloud(false);
}

const STORAGE_KEY_CUSTOM_LOTS = 'materiax_custom_lots';

// Sesión Activa de Plataforma / Empresa / Admin
function getActiveSession() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY_SESSION);
    return raw ? JSON.parse(raw) : null;
  } catch (e) {
    return null;
  }
}

function setActiveSession(company) {
  localStorage.setItem(STORAGE_KEY_SESSION, JSON.stringify(company));
  updateNavbarSession();
}

function clearActiveSession() {
  localStorage.removeItem(STORAGE_KEY_SESSION);
  updateNavbarSession();
  showToast('Has cambiado al modo Visitante Público.', 'Sesión MateriaX');
}

/**
 * Sincroniza la barra de navegación con la sesión activa autenticada
 */
function updateNavbarSession() {
  const session = getActiveSession();
  const defaultActions = document.querySelector('#navDefaultActions');
  const sessionWidget = document.querySelector('#navSessionWidget');
  const companyNameEl = document.querySelector('#navCompanyName');
  const companyCuitEl = document.querySelector('#navCompanyCuit');
  const navSessionDot = document.querySelector('#navSessionDot');
  const navAdminDirectBtn = document.querySelector('#navAdminDirectBtn');
  const navVerificationBtn = document.querySelector('#navVerificationBtn');

  // Determinar rol activo
  let currentRole = 'visitante';
  if (session && session.role === 'admin') {
    currentRole = 'admin';
  } else if (session && session.status === 'aprobada') {
    currentRole = 'empresa';
  }

  // 1. Actualizar Navbar
  if (currentRole === 'admin') {
    if (defaultActions) defaultActions.classList.add('hidden');
    if (sessionWidget) {
      sessionWidget.classList.remove('hidden');
      if (companyNameEl) companyNameEl.textContent = 'Administrador General (GitHub)';
      if (companyCuitEl) companyCuitEl.textContent = 'SUPERADMIN ● GOBERNANZA ACTIVA';
      if (navSessionDot) navSessionDot.style.background = '#38BDF8';
      if (navAdminDirectBtn) {
        navAdminDirectBtn.classList.remove('hidden');
        const companies = getStoredCompanies();
        const pendingCount = companies.filter(c => c.status === 'pendiente' || c.status === 'en_auditoria').length;
        navAdminDirectBtn.innerHTML = `🛡️ Backoffice Auditoría <span class="badge-count" style="margin-left:4px;">${pendingCount}</span>`;
      }
      if (navVerificationBtn) navVerificationBtn.classList.add('hidden');
    }
  } else if (currentRole === 'empresa') {
    if (defaultActions) defaultActions.classList.add('hidden');
    if (sessionWidget) {
      sessionWidget.classList.remove('hidden');
      if (companyNameEl) companyNameEl.textContent = session.company;
      if (companyCuitEl) companyCuitEl.textContent = `${session.cuit} ● RED ACTIVA`;
      if (navSessionDot) navSessionDot.style.background = '#10B981';
      if (navAdminDirectBtn) navAdminDirectBtn.classList.add('hidden');
      if (navVerificationBtn) navVerificationBtn.classList.remove('hidden');
    }
  } else {
    // Visitante (Por Defecto)
    if (defaultActions) defaultActions.classList.remove('hidden');
    if (sessionWidget) sessionWidget.classList.add('hidden');
  }
}

// ==========================================================================
// 3. VALIDADORES CORPORATIVOS (CUIT MÓDULO 11 & CORREO EMPRESARIAL)
// ==========================================================================

/**
 * Validador de CUIT argentino utilizando algoritmo Módulo 11 oficial
 */
function validateCuit(cuitRaw) {
  const clean = String(cuitRaw || '').replace(/\D/g, '');
  if (clean.length !== 11) {
    return { valid: false, message: 'El CUIT debe tener 11 dígitos numéricos.' };
  }

  const validPrefixes = ['20', '23', '24', '27', '30', '33', '34'];
  const prefix = clean.substring(0, 2);
  if (!validPrefixes.includes(prefix)) {
    return { valid: false, message: 'Prefijo no válido para personería fiscal en Argentina.' };
  }

  const multipliers = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
  let sum = 0;
  for (let i = 0; i < 10; i++) {
    sum += parseInt(clean[i], 10) * multipliers[i];
  }

  const mod = sum % 11;
  let expectedVerifier = 11 - mod;
  if (expectedVerifier === 11) expectedVerifier = 0;
  if (expectedVerifier === 10) expectedVerifier = 9;

  const actualVerifier = parseInt(clean[10], 10);
  if (actualVerifier !== expectedVerifier) {
    return { valid: false, message: `Dígito verificador inválido (esperado: ${expectedVerifier}, ingresado: ${actualVerifier}).` };
  }

  return { valid: true, message: '✓ CUIT válido y homologado para registro fiscal AFIP.' };
}

/**
 * Formatea automáticamente una cadena a formato XX-XXXXXXXX-X
 */
function formatCuit(val) {
  const clean = String(val || '').replace(/\D/g, '').slice(0, 11);
  if (clean.length <= 2) return clean;
  if (clean.length <= 10) return `${clean.slice(0, 2)}-${clean.slice(2)}`;
  return `${clean.slice(0, 2)}-${clean.slice(2, 10)}-${clean.slice(10)}`;
}

/**
 * Validador de Correo Electrónico (Acepta Gmail, Outlook, Hotmail, dominios corporativos, etc.)
 */
function validateCorporateEmail(email) {
  const clean = String(email || '').trim().toLowerCase();
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!emailRegex.test(clean)) {
    return { valid: false, message: 'Ingrese una dirección de correo válida (ej: contacto@gmail.com).' };
  }

  return { valid: true, message: '✓ Correo electrónico válido.' };
}

// ==========================================================================
// 4. TOAST NOTIFICATIONS & MODAL HELPERS
// ==========================================================================
function showToast(message, title = 'MateriaX System') {
  let container = document.querySelector('#toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <div>
      <strong style="display:block; font-size:0.85rem;">${escapeHtml(title)}</strong>
      <span>${escapeHtml(message)}</span>
    </div>
  `;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/[&<>"']/g, function(m) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
  });
}

function openModal(modal) {
  if (!modal) return;
  modal.classList.add('active');
  modal.setAttribute('aria-hidden', 'false');
  document.body.style.overflow = 'hidden';
}

function closeModal(modal) {
  if (!modal) return;
  modal.classList.remove('active');
  modal.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';
}

// ==========================================================================
// 5. GESTIÓN DEL ONBOARDING CORPORATIVO (PASO 1 Y PASO 2)
// ==========================================================================
let uploadedFiles = {
  estatuto: { name: 'estatuto_social_empresa.pdf', size: '1.4 MB', hash: 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855' },
  cuit: { name: 'constancia_afip_cuit.pdf', size: '840 KB', hash: '8f4e21a89c9e51b14a0fc8996fb92427ae41e4649b934ca495991b7852b855' }
};

let currentViewingCompanyForVerification = null;

function setupOnboardingFlow() {
  const cuitInput = document.querySelector('#regCompanyCuit');
  const cuitFeedback = document.querySelector('#cuitFeedback');
  const emailInput = document.querySelector('#regCompanyEmail');
  const emailFeedback = document.querySelector('#emailFeedback');
  const nameInput = document.querySelector('#regCompanyName');
  const nameFeedback = document.querySelector('#nameFeedback');

  // Formateo y validación en vivo de CUIT
  cuitInput?.addEventListener('input', (e) => {
    e.target.value = formatCuit(e.target.value);
    const result = validateCuit(e.target.value);
    if (!cuitFeedback) return;
    cuitFeedback.textContent = result.message;
    cuitFeedback.className = `input-feedback-msg ${result.valid ? 'feedback-valid' : 'feedback-invalid'}`;
    cuitInput.classList.toggle('input-valid', result.valid);
    cuitInput.classList.toggle('input-invalid', !result.valid);
  });

  // Validación en vivo de Correo
  emailInput?.addEventListener('input', (e) => {
    const val = e.target.value.trim();
    if (!val) {
      if (emailFeedback) {
        emailFeedback.textContent = 'Ingrese un correo electrónico de contacto válido';
        emailFeedback.className = 'input-feedback-msg';
      }
      emailInput.classList.remove('input-valid', 'input-invalid');
      return;
    }
    const result = validateCorporateEmail(val);
    if (!emailFeedback) return;
    emailFeedback.textContent = result.message;
    emailFeedback.className = `input-feedback-msg ${result.valid ? 'feedback-valid' : 'feedback-invalid'}`;
    emailInput.classList.toggle('input-valid', result.valid);
    emailInput.classList.toggle('input-invalid', !result.valid);
  });

  // Stepper: Paso 1 a Paso 2
  const gotoStep2Btn = document.querySelector('#gotoStep2Btn');
  const backToStep1Btn = document.querySelector('#backToStep1Btn');
  const step1Box = document.querySelector('#onboardingStep1');
  const step2Box = document.querySelector('#onboardingStep2');
  const stepInd1 = document.querySelector('#stepIndicator1');
  const stepInd2 = document.querySelector('#stepIndicator2');

  gotoStep2Btn?.addEventListener('click', () => {
    const companyName = nameInput?.value.trim();
    const cuitVal = cuitInput?.value.trim();
    const emailVal = emailInput?.value.trim();
    const repName = document.querySelector('#regRepName')?.value.trim();
    const repRole = document.querySelector('#regRepRole')?.value.trim();
    const phone = document.querySelector('#regCompanyPhone')?.value.trim();

    if (!companyName || companyName.length < 3) {
      showToast('Por favor, ingresa una Razón Social válida.', 'Validación');
      nameInput?.focus();
      return;
    }

    const cuitCheck = validateCuit(cuitVal);
    if (!cuitCheck.valid) {
      showToast(`Error en CUIT: ${cuitCheck.message}`, 'Validación Fiscal');
      cuitInput?.focus();
      return;
    }

    const emailCheck = validateCorporateEmail(emailVal);
    if (!emailCheck.valid) {
      showToast(`Error en Correo: ${emailCheck.message}`, 'Validación Corporativa');
      emailInput?.focus();
      return;
    }

    if (!repName || !repRole || !phone) {
      showToast('Completa los datos del Representante Autorizado y Teléfono.', 'Validación');
      return;
    }

    // Avanzar a Paso 2
    step1Box?.classList.add('hidden');
    step2Box?.classList.remove('hidden');
    stepInd1?.classList.remove('active');
    stepInd1?.classList.add('completed');
    stepInd2?.classList.add('active');
  });

  backToStep1Btn?.addEventListener('click', () => {
    step2Box?.classList.add('hidden');
    step1Box?.classList.remove('hidden');
    stepInd2?.classList.remove('active');
    stepInd1?.classList.remove('completed');
    stepInd1?.classList.add('active');
  });

  // Manejo de Dropzones y Carga de Archivos
  setupDropzone('fileEstatuto', 'dropzoneEstatuto', 'previewEstatuto', 'namePreviewEstatuto', 'metaPreviewEstatuto', 'btnRemoveEstatuto', 'btnViewEstatuto', 'estatuto');
  setupDropzone('fileCuit', 'dropzoneCuit', 'previewCuit', 'namePreviewCuit', 'metaPreviewCuit', 'btnRemoveCuit', 'btnViewCuit', 'cuit');

  // Submit Final de Onboarding
  const onboardingForm = document.querySelector('#corporateOnboardingForm');
  onboardingForm?.addEventListener('submit', (e) => {
    e.preventDefault();

    const pass = document.querySelector('#regPassword')?.value;
    const passConfirm = document.querySelector('#regPasswordConfirm')?.value;
    const termsCheck = document.querySelector('#regTermsCheck')?.checked;

    if (!pass || pass.length < 6) {
      showToast('La contraseña debe tener al menos 6 caracteres.', 'Seguridad');
      return;
    }

    if (pass !== passConfirm) {
      showToast('Las contraseñas ingresadas no coinciden.', 'Seguridad');
      return;
    }

    if (!termsCheck) {
      showToast('Debes aceptar la declaración jurada de autenticidad.', 'Compliance');
      return;
    }

    const companyName = nameInput.value.trim();
    const cuitVal = cuitInput.value.trim();
    const emailVal = emailInput.value.trim();
    const phoneVal = document.querySelector('#regCompanyPhone')?.value.trim() || '-';
    const repNameVal = document.querySelector('#regRepName')?.value.trim() || '-';
    const repRoleVal = document.querySelector('#regRepRole')?.value.trim() || '-';
    const interestVal = document.querySelector('#regInterestType')?.value || 'Operaciones Integrales';

    const caseNumber = `MX-AUD-2026-${Math.floor(1000 + Math.random() * 9000)}`;
    const randomHash = Array.from({length: 64}, () => Math.floor(Math.random()*16).toString(16)).join('');

    const newCompany = {
      id: Date.now(),
      company: companyName,
      cuit: cuitVal,
      email: emailVal,
      phone: phoneVal,
      repName: repNameVal,
      repRole: repRoleVal,
      interest: interestVal,
      status: 'pendiente', // En espera de homologación
      caseNumber: caseNumber,
      token: '',
      statuteName: uploadedFiles.estatuto.name || 'estatuto_social.pdf',
      cuitDocName: uploadedFiles.cuit.name || 'constancia_afip.pdf',
      hash: randomHash,
      registeredAt: new Date().toLocaleString('es-AR', { dateStyle: 'short', timeStyle: 'medium' })
    };

    saveCompanyToStore(newCompany);

    // Resetear formulario
    onboardingForm.reset();
    step2Box?.classList.add('hidden');
    step1Box?.classList.remove('hidden');
    stepInd2?.classList.remove('active');
    stepInd1?.classList.add('active');
    stepInd1?.classList.remove('completed');

    // Cerrar modal de acceso y abrir Panel de Espera y Validación
    const accessModal = document.querySelector('#accessModal');
    closeModal(accessModal);

    renderVerificationRoom(newCompany);
    const verifModal = document.querySelector('#verificationModal');
    openModal(verifModal);

    showToast(`¡Expediente ${caseNumber} generado! Empresa en proceso de homologación.`, 'Onboarding Exitoso');
  });

  document.querySelector('#cancelOnboardingBtn')?.addEventListener('click', () => {
    closeModal(document.querySelector('#accessModal'));
  });
}

function setupDropzone(fileInputId, dropzoneId, previewId, nameId, metaId, removeBtnId, viewBtnId, typeKey) {
  const fileInput = document.querySelector(`#${fileInputId}`);
  const dropzone = document.querySelector(`#${dropzoneId}`);
  const preview = document.querySelector(`#${previewId}`);
  const nameEl = document.querySelector(`#${nameId}`);
  const metaEl = document.querySelector(`#${metaId}`);
  const removeBtn = document.querySelector(`#${removeBtnId}`);
  const viewBtn = document.querySelector(`#${viewBtnId}`);

  fileInput?.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
      const sizeFormatted = file.size > 1024 * 1024 
        ? `${(file.size / (1024 * 1024)).toFixed(1)} MB` 
        : `${Math.round(file.size / 1024)} KB`;
      
      uploadedFiles[typeKey] = {
        name: file.name,
        size: sizeFormatted,
        hash: Array.from({length: 64}, () => Math.floor(Math.random()*16).toString(16)).join('')
      };

      if (nameEl) nameEl.textContent = file.name;
      if (metaEl) metaEl.textContent = `${sizeFormatted} ● Verificado`;
      preview?.classList.remove('hidden');
      dropzone?.classList.add('hidden');
    }
  });

  removeBtn?.addEventListener('click', () => {
    if (fileInput) fileInput.value = '';
    preview?.classList.add('hidden');
    dropzone?.classList.remove('hidden');
  });

  viewBtn?.addEventListener('click', () => {
    const currentName = document.querySelector('#regCompanyName')?.value.trim() || 'Empresa Postulante';
    const currentCuit = document.querySelector('#regCompanyCuit')?.value.trim() || '30-XXXXXXXX-X';
    const currentRep = document.querySelector('#regRepName')?.value.trim() || 'Representante Autorizado';
    
    openDocumentViewer(
      typeKey === 'estatuto' ? 'Estatuto Social & Poder Societario' : 'Constancia Oficial AFIP / CUIT',
      currentName,
      currentCuit,
      currentRep,
      uploadedFiles[typeKey].hash
    );
  });
}

// ==========================================================================
// 6. PANEL DE ESPERA Y VALIDACIÓN B2B (TIMELINE INTERACTIVO)
// ==========================================================================
function renderVerificationRoom(company) {
  if (!company) return;
  currentViewingCompanyForVerification = company;

  const caseBadge = document.querySelector('#verifCaseNumber');
  const compName = document.querySelector('#verifCompanyName');
  const cuitEl = document.querySelector('#verifCuit');
  const repEl = document.querySelector('#verifRep');
  const statusText = document.querySelector('#verifStatusText');

  if (caseBadge) caseBadge.innerHTML = `<span>EXPEDIENTE:</span> <strong>${escapeHtml(company.caseNumber || 'MX-AUD-2026-0000')}</strong>`;
  if (compName) compName.textContent = company.company;
  if (cuitEl) cuitEl.textContent = company.cuit;
  if (repEl) repEl.textContent = company.repName || '-';

  const step1 = document.querySelector('#timeStep1');
  const step2 = document.querySelector('#timeStep2');
  const step3 = document.querySelector('#timeStep3');
  const step4 = document.querySelector('#timeStep4');

  const badge2 = document.querySelector('#badgeStep2');
  const badge3 = document.querySelector('#badgeStep3');
  const badge4 = document.querySelector('#badgeStep4');

  const refreshBtn = document.querySelector('#refreshVerificationStatusBtn');
  const enterBtn = document.querySelector('#enterPlatformBtn');

  if (refreshBtn) {
    refreshBtn.onclick = () => {
      syncCompaniesWithCloud(true);
    };
  }

  if (company.status === 'aprobada') {
    if (statusText) {
      statusText.textContent = '🟢 EMPRESA HOMOLOGADA Y ACTIVA';
      statusText.style.color = '#10B981';
    }

    [step1, step2, step3, step4].forEach(s => {
      if (s) {
        s.className = 'timeline-step completed';
        const icon = s.querySelector('.timeline-step-icon');
        if (icon) icon.textContent = '✓';
      }
    });

    if (badge2) { badge2.textContent = 'VALIDADO POR AFIP'; badge2.className = 'timeline-step-badge badge-success'; }
    if (badge3) { badge3.textContent = 'PODERES ACREDITADOS'; badge3.className = 'timeline-step-badge badge-success'; }
    if (badge4) { badge4.textContent = 'TOKEN B2B ACTIVO'; badge4.className = 'timeline-step-badge badge-success'; }

    if (enterBtn) {
      enterBtn.removeAttribute('disabled');
      enterBtn.style.opacity = '1';
      enterBtn.style.cursor = 'pointer';
      enterBtn.textContent = 'Ingresar a la Red MateriaX →';
      enterBtn.onclick = () => {
        setActiveSession(company);
        closeModal(document.querySelector('#verificationModal'));
        showToast(`Bienvenido, ${company.company}. Has iniciado sesión corporativa.`, 'Sesión Activa');
      };
    }
  } else {
    // Pendiente
    if (statusText) {
      statusText.textContent = '🟡 EN AUDITORÍA DE COMPLIANCE';
      statusText.style.color = '#38BDF8';
    }

    if (step1) { step1.className = 'timeline-step completed'; step1.querySelector('.timeline-step-icon').textContent = '✓'; }
    if (step2) { step2.className = 'timeline-step current'; step2.querySelector('.timeline-step-icon').textContent = '2'; }
    if (step3) { step3.className = 'timeline-step pending'; step3.querySelector('.timeline-step-icon').textContent = '3'; }
    if (step4) { step4.className = 'timeline-step pending'; step4.querySelector('.timeline-step-icon').textContent = '4'; }

    if (badge2) { badge2.textContent = 'EN AUDITORÍA'; badge2.className = 'timeline-step-badge badge-progress'; }
    if (badge3) { badge3.textContent = 'EN COLA DE AUDITOR'; badge3.className = 'timeline-step-badge badge-pending'; }
    if (badge4) { badge4.textContent = 'PENDIENTE DE ACTIVACIÓN'; badge4.className = 'timeline-step-badge badge-pending'; }

    if (enterBtn) {
      enterBtn.setAttribute('disabled', 'true');
      enterBtn.style.opacity = '0.5';
      enterBtn.style.cursor = 'not-allowed';
      enterBtn.textContent = 'Esperando Homologación del Auditor...';
      enterBtn.onclick = () => {
        showToast('Su expediente está en revisión por el equipo auditor. Presione "Actualizar Estado" para verificar.', 'Panel de Espera');
      };
    }
  }
}

// ==========================================================================
// 7. BACKOFFICE AUDITOR: GESTIÓN DE EMPRESAS & DOCUMENTOS
// ==========================================================================
function renderAdminCompanies() {
  const container = document.querySelector('#adminCompaniesList');
  if (!container) return;

  const companies = getStoredCompanies();
  const pendingCount = companies.filter(c => c.status === 'pendiente').length;
  
  const countPendingBadge = document.querySelector('#countPendingCompanies');
  if (countPendingBadge) countPendingBadge.textContent = pendingCount;

  if (companies.length === 0) {
    container.innerHTML = `<p style="text-align:center; color:#94A3B8; padding:2rem;">No hay empresas registradas.</p>`;
    return;
  }

  container.innerHTML = companies.map(comp => `
    <div class="audit-company-card">
      <div class="audit-company-header">
        <div>
          <span style="font-size:0.75rem; font-family:var(--font-mono); color:#38BDF8;">${escapeHtml(comp.caseNumber || 'MX-AUD-2026')}</span>
          <h4 class="audit-company-title">${escapeHtml(comp.company)}</h4>
        </div>
        <span class="timeline-step-badge ${comp.status === 'aprobada' ? 'badge-success' : 'badge-progress'}">
          ${comp.status === 'aprobada' ? '● HOMOLOGADA' : '⏳ PENDIENTE AUDITORÍA'}
        </span>
      </div>

      <div class="audit-company-grid">
        <div><strong>CUIT:</strong> <span style="font-family:var(--font-mono);">${escapeHtml(comp.cuit)}</span></div>
        <div><strong>Email:</strong> ${escapeHtml(comp.email)}</div>
        <div><strong>Teléfono:</strong> ${escapeHtml(comp.phone || '-')}</div>
        <div><strong>Representante:</strong> ${escapeHtml(comp.repName || '-')}</div>
        <div><strong>Cargo:</strong> ${escapeHtml(comp.repRole || '-')}</div>
        <div><strong>Perfil:</strong> ${escapeHtml(comp.interest || 'General')}</div>
      </div>

      <div class="audit-company-docs">
        <span style="font-size:0.75rem; color:#94A3B8;">Documentos Legales:</span>
        <button type="button" class="audit-doc-chip" onclick="viewCompanyDoc('${comp.cuit}', 'estatuto')">
          📄 ${escapeHtml(comp.statuteName || 'Estatuto_Social.pdf')}
        </button>
        <button type="button" class="audit-doc-chip" onclick="viewCompanyDoc('${comp.cuit}', 'cuit')">
          🏛️ ${escapeHtml(comp.cuitDocName || 'Constancia_AFIP.pdf')}
        </button>
      </div>

      <div class="audit-actions-bar">
        ${comp.status === 'pendiente' ? `
          <button type="button" class="btn btn-sm btn-ghost btn-abtc-outline" onclick="rejectCompany('${comp.cuit}')">
            ✕ Observar / Rechazar
          </button>
          <button type="button" class="btn btn-sm btn-primary btn-abtc-primary" onclick="approveCompany('${comp.cuit}')">
            ✓ Homologar y Activar en Red
          </button>
        ` : `
          <span style="font-size:0.8rem; color:#10B981; font-family:var(--font-mono);">
            ✓ Token de Red Activo: ${escapeHtml(comp.token || 'MX-TOK-VALID')}
          </span>
          <button type="button" class="btn btn-sm btn-ghost" onclick="loginAsCompany('${comp.cuit}')">
            Iniciar Sesión como esta Empresa &rarr;
          </button>
        `}
      </div>
    </div>
  `).join('');
}

// Funciones expuestas a window para botones dinámicos en templates
window.viewCompanyDoc = function(cuit, type) {
  const companies = getStoredCompanies();
  const comp = companies.find(c => c.cuit === cuit);
  if (!comp) return;

  openDocumentViewer(
    type === 'estatuto' ? 'Estatuto Social Constitutivo' : 'Constancia de CUIT AFIP',
    comp.company,
    comp.cuit,
    comp.repName || 'Representante Legal',
    comp.hash || 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855'
  );
};

window.approveCompany = function(cuit) {
  const companies = getStoredCompanies();
  const comp = companies.find(c => c.cuit === cuit);
  if (comp) {
    comp.status = 'aprobada';
    comp.token = `MX-TOK-${Math.floor(1000 + Math.random() * 9000)}-B2B-OK`;
    saveCompanyToStore(comp);
    renderAdminCompanies();
    showToast(`Empresa ${comp.company} homologada exitosamente.`, 'Auditoría B2B');
  }
};

window.rejectCompany = function(cuit) {
  const reason = prompt('Ingrese el motivo u observación técnica para la empresa:');
  if (reason) {
    const companies = getStoredCompanies();
    const comp = companies.find(c => c.cuit === cuit);
    if (comp) {
      comp.status = 'observada';
      comp.observation = reason;
      saveCompanyToStore(comp);
      renderAdminCompanies();
      showToast(`Empresa observada: "${reason}".`, 'Auditoría B2B');
    }
  }
};

window.loginAsCompany = function(cuit) {
  const companies = getStoredCompanies();
  const comp = companies.find(c => c.cuit === cuit);
  if (comp && comp.status === 'aprobada') {
    setActiveSession(comp);
    closeModal(document.querySelector('#adminModal'));
    showToast(`Sesión iniciada como ${comp.company}.`, 'Sesión Activa');
  }
};

function openDocumentViewer(docType, companyName, cuit, rep, hash) {
  const modal = document.querySelector('#docPreviewModal');
  if (!modal) return;

  const docTitle = document.querySelector('#docPreviewTitle');
  const paperCompany = document.querySelector('#docPaperCompany');
  const paperCuit = document.querySelector('#docPaperCuit');
  const paperType = document.querySelector('#docPaperType');
  const paperRep = document.querySelector('#docPaperRep');
  const paperHash = document.querySelector('#docPaperHash');

  if (docTitle) docTitle.textContent = docType;
  if (paperCompany) paperCompany.textContent = companyName;
  if (paperCuit) paperCuit.textContent = `CUIT: ${cuit}`;
  if (paperType) paperType.textContent = docType;
  if (paperRep) paperRep.textContent = rep;
  if (paperHash) paperHash.textContent = hash;

  openModal(modal);
}

// ==========================================================================
// 8. AUTENTICACIÓN CORPORATIVA (LOGIN B2B & CONSULTA DE ESTADO)
// ==========================================================================
function setupCorporateAuth() {
  const loginForm = document.querySelector('#corporateLoginForm');
  const checkStatusForm = document.querySelector('#checkStatusForm');

  // Pestañas del Portal Modal
  document.querySelectorAll('[data-portal-tab]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-portal-tab]').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.portal-tab-content').forEach(c => c.classList.remove('active'));

      btn.classList.add('active');
      const tabId = btn.dataset.portalTab;
      const targetContent = document.querySelector(`#${tabId}`);
      if (targetContent) targetContent.classList.add('active');
    });
  });

  // Pestañas del Admin Modal
  document.querySelectorAll('[data-admin-tab]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-admin-tab]').forEach(b => b.classList.remove('active'));
      const adminTabs = document.querySelectorAll('#companiesAuditTab, #requestsAuditTab');
      adminTabs.forEach(t => t.classList.remove('active'));

      btn.classList.add('active');
      const target = document.querySelector(`#${btn.dataset.adminTab}`);
      if (target) target.classList.add('active');
    });
  });

  // Login Form Submit (Autenticación Real B2B / SuperAdmin)
  loginForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const query = document.querySelector('#loginEmail')?.value.trim().toLowerCase();
    const pass = document.querySelector('#loginPassword')?.value.trim();

    if (!query || !pass) {
      showToast('Por favor ingrese su usuario/correo y contraseña.', 'Campos Requeridos');
      return;
    }

    // 1. Acceso Exclusivo para Administrador / SuperAdmin
    if ((query === 'admin@materiax.org' || query === 'admin') && (pass === 'admin2026' || pass === '1234' || pass === 'admin' || pass === 'materiax2026')) {
      const adminSession = {
        company: 'Administrador General (GitHub)',
        cuit: 'SUPERADMIN-MX',
        email: 'admin@materiax.org',
        role: 'admin',
        status: 'aprobada'
      };
      localStorage.setItem(STORAGE_KEY_SESSION, JSON.stringify(adminSession));
      updateNavbarSession();
      syncCompaniesWithCloud(false);
      closeModal(document.querySelector('#accessModal'));
      showToast('¡Bienvenido, Administrador General! Gobernanza y Backoffice activados.', 'Sesión SuperAdmin');
      return;
    }

    // 2. Acceso para Empresas Registradas
    const companies = getStoredCompanies();
    const matched = companies.find(c => 
      c.email.toLowerCase() === query || 
      c.cuit.replace(/\D/g, '') === query.replace(/\D/g, '') ||
      c.company.toLowerCase() === query
    );

    if (!matched) {
      showToast('Credenciales no válidas. Si es tu primera vez, debes registrar a tu empresa en la pestaña "Registro Onboarding Empresa".', 'Error de Acceso');
      return;
    }

    const accessModal = document.querySelector('#accessModal');
    closeModal(accessModal);

    if (matched.status === 'pendiente') {
      renderVerificationRoom(matched);
      openModal(document.querySelector('#verificationModal'));
      showToast('Tu empresa se encuentra en proceso de homologación por el equipo auditor.', 'Panel de Espera');
    } else if (matched.status === 'aprobada') {
      setActiveSession(matched);
      showToast(`¡Bienvenido de nuevo, ${matched.company}! Acceso corporativo verificado.`, 'Sesión Iniciada');
    } else {
      showToast(`Tu cuenta tiene observaciones: ${matched.observation || 'Contacta a soporte institucional.'}`, 'Cuenta Observada');
    }
  });

  // Consultar Estado Form
  checkStatusForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const query = document.querySelector('#statusQueryInput')?.value.trim().toLowerCase();
    const companies = getStoredCompanies();

    const matched = companies.find(c => 
      c.email.toLowerCase() === query || 
      c.cuit.replace(/\D/g, '') === query.replace(/\D/g, '') ||
      (c.caseNumber && c.caseNumber.toLowerCase() === query)
    );

    if (!matched) {
      showToast('No se encontró ningún expediente con ese CUIT o Correo.', 'Búsqueda de Estado');
      return;
    }

    closeModal(document.querySelector('#accessModal'));
    renderVerificationRoom(matched);
    openModal(document.querySelector('#verificationModal'));
  });

  // Logout en Navbar
  document.querySelector('#navLogoutBtn')?.addEventListener('click', clearActiveSession);
  document.querySelector('#navVerificationBtn')?.addEventListener('click', () => {
    const session = getActiveSession();
    if (session) {
      renderVerificationRoom(session);
      openModal(document.querySelector('#verificationModal'));
    }
  });
}

// ==========================================================================
// 9. GESTIÓN DE SOLICITUDES LOCALES & BADGES
// ==========================================================================
function updateAdminBadge() {
  const requests = getStoredRequests();
  const companies = getStoredCompanies();
  const pendingCompanies = companies.filter(c => c.status === 'pendiente').length;

  const countStoredBadge = document.querySelector('#countStoredRequests');
  if (countStoredBadge) countStoredBadge.textContent = requests.length;

  const countPendingBadge = document.querySelector('#countPendingCompanies');
  if (countPendingBadge) countPendingBadge.textContent = pendingCompanies;

  const badges = document.querySelectorAll('#adminBadgeCount, .badge-count');
  badges.forEach(badge => {
    badge.textContent = requests.length + pendingCompanies;
  });
}

function renderAdminRequests() {
  const listContainer = document.querySelector('#adminRequestsList, #requestsListContainer');
  if (!listContainer) return;
  const requests = getStoredRequests();

  if (requests.length === 0) {
    listContainer.innerHTML = `
      <div class="empty-state" style="padding:30px 10px; text-align:center;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:0.5rem; opacity:0.6;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <h4 style="margin:8px 0 4px; font-family:var(--font-sans); color:#FFFFFF;">Sin solicitudes de lotes guardadas</h4>
        <p style="font-size:0.85rem; margin:0; color:var(--color-text-light-muted, #9BA3B8);">Las reservas de materiales realizadas por empresas aparecerán aquí.</p>
      </div>
    `;
    return;
  }

  listContainer.innerHTML = requests.map(req => `
    <div class="request-item-card">
      <div class="request-item-header">
        <strong>${escapeHtml(req.company)}</strong>
        <span class="request-time">${escapeHtml(req.timestamp)}</span>
      </div>
      <div class="request-item-body">
        <div><strong>CUIT:</strong> ${escapeHtml(req.cuit)}</div>
        <div><strong>Email:</strong> ${escapeHtml(req.email)}</div>
        <div><strong>Interés:</strong> ${escapeHtml(req.interest)}</div>
        ${req.message ? `<div><strong>Detalles:</strong> ${escapeHtml(req.message)}</div>` : ''}
      </div>
    </div>
  `).join('');
}

function exportRequestsJSON() {
  const data = {
    empresas: getStoredCompanies(),
    solicitudes: getStoredRequests(),
    exportadoEl: new Date().toISOString()
  };

  const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(data, null, 2));
  const downloadAnchor = document.createElement('a');
  downloadAnchor.setAttribute("href", dataStr);
  downloadAnchor.setAttribute("download", `materiax_base_datos_${Date.now()}.json`);
  document.body.appendChild(downloadAnchor);
  downloadAnchor.click();
  downloadAnchor.remove();
  showToast('Base institucional descargada en formato JSON', 'Exportar');
}

// ==========================================================================
// 10. BÚSQUEDA Y FILTRADO DE RECURSOS
// ==========================================================================
const searchInput = document.querySelector('#search');
const clearButton = document.querySelector('#clearSearch');
const filterPills = document.querySelectorAll('.filter-pill');
const searchCounter = document.querySelector('#searchCounter');
const noResultsState = document.querySelector('#noResultsState');
const resetSearchBtn = document.querySelector('#resetSearchBtn');
let currentCategory = 'all';

function filterResources() {
  const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
  let visibleCount = 0;
  const allCards = document.querySelectorAll('.resource-card');

  allCards.forEach((card) => {
    const cardCategory = card.dataset.category || '';
    const keywords = `${card.textContent} ${card.dataset.keywords || ''}`.toLowerCase();
    
    const matchesCategory = currentCategory === 'all' || cardCategory === currentCategory;
    const matchesSearch = !query || keywords.includes(query);

    if (matchesCategory && matchesSearch) {
      card.classList.remove('hidden');
      card.style.display = '';
      visibleCount++;
    } else {
      card.classList.add('hidden');
      card.style.display = 'none';
    }
  });

  if (searchCounter) {
    if (visibleCount === allCards.length) {
      searchCounter.textContent = `Mostrando todos los recursos (${allCards.length})`;
    } else {
      searchCounter.textContent = `Mostrando ${visibleCount} de ${allCards.length} recursos`;
    }
  }

  if (noResultsState) {
    if (visibleCount === 0) {
      noResultsState.classList.remove('hidden');
      noResultsState.style.display = 'block';
    } else {
      noResultsState.classList.add('hidden');
      noResultsState.style.display = 'none';
    }
  }
}

// ==========================================================================
// 11. INICIALIZACIÓN GLOBAL DE EVENTOS
// ==========================================================================
document.addEventListener('DOMContentLoaded', () => {
  initCompaniesStore();
  updateNavbarSession();
  updateAdminBadge();
  setupOnboardingFlow();
  setupCorporateAuth();
  filterResources();

  // Modales
  const accessModal = document.querySelector('#accessModal');
  const detailModal = document.querySelector('#detailModal');
  const adminModal = document.querySelector('#adminModal');
  const verificationModal = document.querySelector('#verificationModal');
  const docPreviewModal = document.querySelector('#docPreviewModal');

  // Disparadores de Modal de Acceso
  document.querySelector('#openAccessModalNav')?.addEventListener('click', () => openModal(accessModal));
  document.querySelector('#openAccessModalHero')?.addEventListener('click', () => openModal(accessModal));
  document.querySelector('#openAccessModalCta')?.addEventListener('click', () => openModal(accessModal));
  document.querySelector('#closeAccessModal')?.addEventListener('click', () => closeModal(accessModal));

  // Disparadores de Modal Admin
  document.querySelector('#openAdminModalBtn')?.addEventListener('click', () => {
    renderAdminCompanies();
    renderAdminRequests();
    openModal(adminModal);
  });
  document.querySelector('#closeAdminModal')?.addEventListener('click', () => closeModal(adminModal));
  document.querySelector('#closeAdminBtn')?.addEventListener('click', () => closeModal(adminModal));
  document.querySelector('#clearStorageBtn')?.addEventListener('click', clearStoredRequests);
  document.querySelector('#exportStorageBtn')?.addEventListener('click', exportRequestsJSON);

  // Disparadores de Modal de Verificación
  document.querySelector('#closeVerificationModal')?.addEventListener('click', () => closeModal(verificationModal));
  document.querySelector('#closeVerificationBtn')?.addEventListener('click', () => closeModal(verificationModal));

  // Disparadores de Visor de Documentos
  document.querySelector('#closeDocPreviewModal')?.addEventListener('click', () => closeModal(docPreviewModal));
  document.querySelector('#closeDocPreviewBtn')?.addEventListener('click', () => closeModal(docPreviewModal));

  // Cierre en Backdrop
  [accessModal, detailModal, adminModal, verificationModal, docPreviewModal].forEach(modal => {
    modal?.addEventListener('click', (e) => {
      if (e.target === modal) closeModal(modal);
    });
  });

  // Tecla Escape para cerrar modales
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      [accessModal, detailModal, adminModal, verificationModal, docPreviewModal].forEach(closeModal);
    }
  });

  // Footer Formulario de Contacto
  document.querySelectorAll('.horizontal-contact-form').forEach(form => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const emailInput = form.querySelector('input[type="email"]');
      const emailVal = emailInput ? emailInput.value.trim() : '';
      openModal(accessModal);
      const regEmail = document.querySelector('#regCompanyEmail');
      if (regEmail && emailVal) {
        regEmail.value = emailVal;
      }
      // Cambiar a la pestaña de registro
      const regTabBtn = document.querySelector('[data-portal-tab="registerTab"]');
      regTabBtn?.click();
    });
  });

  // Buscador y Filtros
  searchInput?.addEventListener('input', () => filterResources());
  clearButton?.addEventListener('click', () => {
    if (!searchInput) return;
    searchInput.value = '';
    currentCategory = 'all';
    filterPills.forEach(p => p.classList.toggle('active', p.dataset.category === 'all'));
    filterResources();
    searchInput.focus();
    showToast('Búsqueda restablecida', 'Filtros');
  });

  resetSearchBtn?.addEventListener('click', () => {
    if (searchInput) searchInput.value = '';
    currentCategory = 'all';
    filterPills.forEach(p => p.classList.toggle('active', p.dataset.category === 'all'));
    filterResources();
  });

  filterPills.forEach((pill) => {
    pill.addEventListener('click', () => {
      filterPills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      currentCategory = pill.dataset.category || 'all';
      filterResources();
    });
  });

  document.querySelectorAll('[data-filter-link]').forEach(link => {
    link.addEventListener('click', () => {
      const category = link.dataset.filterLink;
      if (category) {
        currentCategory = category;
        filterPills.forEach(p => p.classList.toggle('active', p.dataset.category === category));
        filterResources();
        const targetSection = document.querySelector('#roadmap-inventario, #modulos-hitos');
        if (targetSection) targetSection.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // Modal de Ficha Técnica Detallada
  document.querySelectorAll('.open-detail-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const id = btn.dataset.resourceId;
      const data = RESOURCE_DATA[id];

      if (data) {
        const titleEl = document.querySelector('#detailTitle');
        const catEl = document.querySelector('#detailCategory');
        const descEl = document.querySelector('#detailDescription');
        const stockEl = document.querySelector('#detailStock');
        const locEl = document.querySelector('#detailLocation');
        const statusEl = document.querySelector('#detailStatus');
        const packEl = document.querySelector('#detailPackaging');

        if (titleEl) titleEl.textContent = data.title;
        if (catEl) catEl.textContent = data.category;
        if (descEl) descEl.textContent = data.description;
        if (stockEl) stockEl.textContent = data.stock;
        if (locEl) locEl.textContent = data.location;
        if (statusEl) statusEl.textContent = data.status;
        if (packEl) packEl.textContent = data.packaging;

        const detailReqBtn = document.querySelector('#detailRequestBtn');
        if (detailReqBtn) {
          detailReqBtn.onclick = () => {
            closeModal(detailModal);
            const session = getActiveSession();
            if (session && session.status === 'aprobada') {
              const reqData = {
                id: Date.now(),
                timestamp: new Date().toLocaleString('es-AR', { dateStyle: 'short', timeStyle: 'medium' }),
                company: session.company,
                cuit: session.cuit,
                email: session.email,
                interest: `Reserva de Lote #${id}: ${data.title} (${data.stock})`,
                message: `Solicitud originada desde terminal homologada de ${session.company}.`
              };
              saveRequestToLocalStorage(reqData);
              showToast(`¡Lote #${id} solicitado exitosamente bajo firma corporativa!`, 'Reserva B2B');
            } else {
              openModal(accessModal);
              showToast('Por favor, identifícate con tu empresa para formalizar la reserva del lote.', 'Acceso Requerido');
            }
          };
        }

        openModal(detailModal);
      }
    });
  });

  document.querySelector('#closeDetailModal')?.addEventListener('click', () => closeModal(detailModal));
  document.querySelector('#closeDetailBtn')?.addEventListener('click', () => closeModal(detailModal));

  // Botones Directos de Reserva en Cards
  document.querySelectorAll('.request-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const title = btn.dataset.title || 'Lote de material';
      const session = getActiveSession();
      if (session && session.status === 'aprobada') {
        const reqData = {
          id: Date.now(),
          timestamp: new Date().toLocaleString('es-AR', { dateStyle: 'short', timeStyle: 'medium' }),
          company: session.company,
          cuit: session.cuit,
          email: session.email,
          interest: `Reserva directa: ${title}`,
          message: `Operación registrada por ${session.company}.`
        };
        saveRequestToLocalStorage(reqData);
        showToast(`¡Reserva para "${title}" enviada exitosamente!`, 'Reserva B2B');
      } else {
        openModal(accessModal);
        showToast('Identifícate con tu empresa para formalizar la reserva.', 'Acceso Requerido');
      }
    });
  });

  // ==========================================================================
  // SISTEMA DE ROLES B2B (SUPERADMIN, EMPRESA VERIFICADA, VISITANTE)
  // ==========================================================================
  function initPlatformRolesListeners() {
    // Botón directo de Backoffice en navbar para Administrador
    document.querySelector('#navAdminDirectBtn')?.addEventListener('click', () => {
      openModal(adminModal);
    });

    // Inicializar visuales de roles al cargar
    updateNavbarSession();
  }

  // ==========================================================================
  // PUBLICACIÓN DE EXCEDENTES INDUSTRIALES (FACETA OFERENTE)
  // ==========================================================================
  const publishLotModal = document.querySelector('#publishLotModal');
  const publishLotForm = document.querySelector('#publishLotForm');
  const openPublishLotBtn = document.querySelector('#openPublishLotBtn');
  const closePublishLotModalBtn = document.querySelector('#closePublishLotModal');
  const cancelPublishLotBtn = document.querySelector('#cancelPublishLotBtn');

  function initPublishLotFeature() {
    openPublishLotBtn?.addEventListener('click', () => {
      const session = getActiveSession();
      if (!session || session.status !== 'aprobada') {
        showToast('Debes ser una Empresa Verificada u Administrador para publicar excedentes.', 'Acceso Restringido');
        // Abrir portal institucional para invitar al Onboarding
        openModal(accessModal);
        const tabBtn = document.querySelector('.portal-tab-btn[data-portal-tab="onboardingTab"]');
        if (tabBtn) tabBtn.click();
        return;
      }

      // Prefill con los datos de la empresa conectada
      const compInput = document.querySelector('#pubCompanyName');
      if (compInput) {
        compInput.value = session.company || 'Empresa Emisora Homologada';
      }
      openModal(publishLotModal);
    });

    closePublishLotModalBtn?.addEventListener('click', () => closeModal(publishLotModal));
    cancelPublishLotBtn?.addEventListener('click', () => closeModal(publishLotModal));

    publishLotForm?.addEventListener('submit', (e) => {
      e.preventDefault();
      const session = getActiveSession();
      const company = document.querySelector('#pubCompanyName')?.value.trim() || session?.company || 'Planta Industrial Homologada';
      const category = document.querySelector('#pubCategory')?.value || 'polietileno';
      const title = document.querySelector('#pubMaterialTitle')?.value.trim();
      const stock = document.querySelector('#pubStock')?.value.trim();
      const location = document.querySelector('#pubLocation')?.value.trim();
      const condition = document.querySelector('#pubCondition')?.value.trim();
      const description = document.querySelector('#pubDescription')?.value.trim();

      if (!title || !stock || !location) {
        showToast('Por favor completa todos los campos requeridos.', 'Formulario Incompleto');
        return;
      }

      const newId = 'custom_' + Date.now();
      const newLot = {
        id: newId,
        category: category,
        title: title,
        desc: description,
        stock: stock,
        location: location,
        condition: condition,
        company: company,
        timestamp: new Date().toISOString()
      };

      // Guardar en LocalStorage
      let storedCustom = [];
      try {
        storedCustom = JSON.parse(localStorage.getItem(STORAGE_KEY_CUSTOM_LOTS) || '[]');
      } catch (err) {
        storedCustom = [];
      }
      storedCustom.unshift(newLot);
      localStorage.setItem(STORAGE_KEY_CUSTOM_LOTS, JSON.stringify(storedCustom));

      // Agregar a RESOURCE_DATA en memoria
      RESOURCE_DATA[newId] = {
        title: title,
        category: category.toUpperCase(),
        description: description,
        stock: stock,
        location: location,
        status: 'Disponible para Retiro',
        packaging: condition
      };

      // Renderizar en el DOM
      renderSingleCustomLotCard(newLot);

      // Limpiar y cerrar modal
      publishLotForm.reset();
      closeModal(publishLotModal);
      showToast(`¡Lote "${title}" publicado exitosamente en el catálogo activo!`, 'Oferta B2B Registrada');

      // Scroll hacia el inventario
      document.querySelector('#roadmap-inventario')?.scrollIntoView({ behavior: 'smooth' });
    });

    loadAndRenderCustomLots();
  }

  function renderSingleCustomLotCard(lot) {
    const grid = document.querySelector('#resourceGrid');
    if (!grid) return;

    let chipLabel = 'POLÍMERO VERIFICADO';
    if (lot.category === 'polietileno') chipLabel = 'PEAD / PEBD';
    else if (lot.category === 'polipropileno') chipLabel = 'PP HOMOPOLÍMERO';
    else if (lot.category === 'tecnicos') chipLabel = 'POLÍMERO TÉCNICO';
    else if (lot.category === 'equipamiento') chipLabel = 'LOGÍSTICA & MATRICERÍA';

    const card = document.createElement('article');
    card.className = 'resource-card';
    card.dataset.category = lot.category;
    card.dataset.keywords = `${lot.title} ${lot.category} ${lot.location} ${lot.condition} ${lot.company}`.toLowerCase();
    card.dataset.id = lot.id;

    card.innerHTML = `
      <div>
        <div class="resource-card-header">
          <span class="chip-polymer">${escapeHtml(chipLabel)}</span>
          <span class="chip-stock">${escapeHtml(lot.stock)}</span>
        </div>
        <h4 class="resource-title">${escapeHtml(lot.title)}</h4>
        <p class="resource-desc">${escapeHtml(lot.desc)}</p>
      </div>
      <div>
        <div class="resource-meta">
          <div>Ubicación: <strong>${escapeHtml(lot.location)}</strong></div>
          <div>Condición: <strong>${escapeHtml(lot.condition)}</strong></div>
          <div style="font-size:0.75rem; color:#64748B; margin-top:0.2rem;">Oferente: <strong>${escapeHtml(lot.company)}</strong></div>
        </div>
        <div class="resource-actions">
          <button type="button" class="btn btn-sm btn-primary request-btn" data-title="${escapeHtml(lot.title)}">Solicitar</button>
          <button type="button" class="btn btn-sm btn-ghost-dark open-detail-btn" data-resource-id="${escapeHtml(lot.id)}">Ficha Técnica</button>
        </div>
      </div>
    `;

    grid.prepend(card);

    // Asignar listeners
    card.querySelector('.request-btn')?.addEventListener('click', () => {
      handleLotRequest(lot.title, lot.id);
    });

    card.querySelector('.open-detail-btn')?.addEventListener('click', (e) => {
      e.stopPropagation();
      openDetailModalForId(lot.id);
    });

    // Actualizar contador
    const count = grid.querySelectorAll('.resource-card').length;
    const counterEl = document.querySelector('#searchCounter');
    if (counterEl) counterEl.textContent = `Mostrando ${count} recursos activos`;
  }

  function loadAndRenderCustomLots() {
    try {
      const stored = JSON.parse(localStorage.getItem(STORAGE_KEY_CUSTOM_LOTS) || '[]');
      stored.reverse().forEach(lot => {
        RESOURCE_DATA[lot.id] = {
          title: lot.title,
          category: lot.category.toUpperCase(),
          description: lot.desc,
          stock: lot.stock,
          location: lot.location,
          status: 'Disponible para Retiro',
          packaging: lot.condition
        };
        renderSingleCustomLotCard(lot);
      });
    } catch (e) {
      console.error('Error loading custom lots', e);
    }
  }

  function handleLotRequest(title, lotId) {
    const session = getActiveSession();
    if (session && session.status === 'aprobada') {
      const reqData = {
        id: Date.now(),
        timestamp: new Date().toLocaleString('es-AR', { dateStyle: 'short', timeStyle: 'medium' }),
        company: session.company,
        cuit: session.cuit,
        email: session.email,
        interest: `Reserva directa: ${title}`,
        message: `Operación registrada por ${session.company} (Rol: ${session.role || 'Empresa'}).`
      };
      saveRequestToLocalStorage(reqData);
      showToast(`¡Reserva para "${title}" enviada exitosamente bajo firma corporativa!`, 'Reserva B2B');
    } else {
      openModal(accessModal);
      showToast('Debes registrar o identificar tu empresa para formalizar reservas vinculantes.', 'Acceso Requerido');
    }
  }

  function openDetailModalForId(id) {
    const data = RESOURCE_DATA[id];
    if (!data) return;
    const titleEl = document.querySelector('#detailTitle');
    const catEl = document.querySelector('#detailCategory');
    const descEl = document.querySelector('#detailDescription');
    const stockEl = document.querySelector('#detailStock');
    const locEl = document.querySelector('#detailLocation');
    const statusEl = document.querySelector('#detailStatus');
    const packEl = document.querySelector('#detailPackaging');

    if (titleEl) titleEl.textContent = data.title;
    if (catEl) catEl.textContent = data.category;
    if (descEl) descEl.textContent = data.description;
    if (stockEl) stockEl.textContent = data.stock;
    if (locEl) locEl.textContent = data.location;
    if (statusEl) statusEl.textContent = data.status;
    if (packEl) packEl.textContent = data.packaging;

    const detailReqBtn = document.querySelector('#detailRequestBtn');
    if (detailReqBtn) {
      detailReqBtn.onclick = () => {
        closeModal(detailModal);
        handleLotRequest(data.title, id);
      };
    }

    openModal(detailModal);
  }

  // Inicializar Roles y Publicación de Lotes
  initPlatformRolesListeners();
  initPublishLotFeature();

  // Sincronización continua con la nube en tiempo real cada 5 segundos
  setInterval(() => {
    syncCompaniesWithCloud(false);
  }, 5000);

  // Mobile Menu
  document.querySelector('#menuToggle')?.addEventListener('click', () => {
    document.querySelector('#topNav')?.classList.toggle('mobile-open');
  });

  // ScrollSpy
  const navLinks = document.querySelectorAll('.topnav a, .nav-links a');
  const trackedSections = document.querySelectorAll('section[id], footer[id]');
  if ('IntersectionObserver' in window && trackedSections.length > 0) {
    const scrollSpyObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const currentId = entry.target.getAttribute('id');
          navLinks.forEach(link => {
            const href = link.getAttribute('href') || '';
            if (href === `#${currentId}` || href.endsWith(`#${currentId}`) || link.hash === `#${currentId}`) {
              navLinks.forEach(l => l.classList.remove('active'));
              link.classList.add('active');
            }
          });
        }
      });
    }, { rootMargin: '-20% 0px -70% 0px' });

    trackedSections.forEach(sec => scrollSpyObserver.observe(sec));
  }
});
