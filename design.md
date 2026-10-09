# Design — MateriaX Pro

A locked design system for this app. Every page redesign reads this file before emitting code. Do not regenerate per page — extend or amend this file when the system needs to grow.

## Genre
modern-minimal

## Tone
technical

## Audience & Core Job
Empresas de rotomoldeo, plantas de extrusión/inyección, talleres de impresión 3D y recicladores técnicos que operan como una red directa de intercambio B2B para comercializar y circular excedentes de polímeros (scrap micronizado de rotomoldeo, bobinas/sobras de filamento técnico, resinas, matrices y modelos 3D). Acción rectora de la página: **Sign Up** (Registrar Empresa / Sumarse a la red circular).

## Constraints
- **Zero JavaScript**: Prohibición estricta de archivos `.js`, etiquetas `<script>`, manejadores de eventos inline (`onclick`, `onsubmit`, etc.) y pseudo-protocolos `javascript:`.
- **Accesibilidad**: WCAG 2.2 AA, contraste visual ≥ 4.5:1 (texto normal) y ≥ 3:1 (controles/focos), saltos accesibles skip-link y navegación por teclado nativa.
- **CSRF & Seguridad**: Tokens CSRF en cada formulario, validación server-side estricta en CodeIgniter 4.

## Macrostructure Family
- **Marketing / Landing** (`welcome_message.php`): **Workbench / Feed-Led Hero** — Foco directo en conversión hacia **Sign Up**, visualización táctil de lotes en circulación (filamentos 3D, scrap rotomoldeo, matrices), trazabilidad técnica y métricas auditadas sin ornamentos vacíos.
- **App / Catálogo** (`productos/*`): **Workbench Split-View** — Panel de filtros técnicos facetados por tipo de polímero (PE rotomoldeo, PP, filamentos PLA/ABS/PETG/Nylon, modelos 3D), listado tabular denso con especificaciones (MFI, densidad, color, micrones) y fichas de lote sin scroll innecesario.
- **Paneles & Administración** (`panel/*`, `perfil/*`, `admin/*`): **Utilitarian Dashboard** — Tableros de alta densidad de información, métricas en formato tabular, tablas de auditoría limpias y estados de verificación de empresa.

## Theme & OKLCH Palette
- `--color-paper`: `oklch(98.8% 0.006 195)` (Lienzo técnico frío)
- `--color-paper-2`: `oklch(96.5% 0.010 195)` (Superficies de soporte y barras)
- `--color-paper-surface`: `oklch(100% 0 0)` (Tarjetas y contenedores de datos)
- `--color-ink`: `oklch(18% 0.02 240)` (Tinta primaria, máxima legibilidad)
- `--color-ink-2`: `oklch(42% 0.02 240)` (Texto secundario técnico)
- `--color-ink-muted`: `oklch(60% 0.015 240)` (Metadatos, etiquetas y unidades)
- `--color-rule`: `oklch(89% 0.01 200)` (Filetes y bordes estructurales)
- `--color-rule-subtle`: `oklch(94% 0.008 200)` (Líneas divisorias suaves)
- `--color-accent`: `oklch(51.8% 0.112 188.4)` (Verde azulado industrial #0f766e)
- `--color-accent-hover`: `oklch(45% 0.115 188.4)` (Interacción activa)
- `--color-accent-ink`: `oklch(98% 0 0)` (Contraste blanco sobre acento)
- `--color-signal-cyan`: `oklch(56% 0.13 220)` (Detalles técnicos y filamentos especiales)
- `--color-focus`: `oklch(52% 0.14 188)` (Anillo `:focus-visible` de 2px)
- `--color-success`: `oklch(52% 0.13 145)` (Estados auditados y verificados)
- `--color-warning`: `oklch(64% 0.14 75)` (Lotes en revisión / pendientes)
- `--color-danger`: `oklch(50% 0.18 25)` (Alertas y bajas)

## Typography
- **Display**: `'Red Hat Display', sans-serif`, peso 700 / 800, estilo normal (no itálicas en encabezados).
- **Body**: `'Red Hat Text', system-ui, sans-serif`, peso 400 / 500 / 600, `line-height: 1.6`.
- **Mono**: `'JetBrains Mono', monospace`, peso 500 / 600, tabular-nums para especificaciones técnicas, CUITs, lotes (#ROT-204, #FIL-88) y kilos.
- **Display Tracking**: `-0.025em` en display grande, `-0.015em` en subtítulos.
- **Type Scale Anchor**: `clamp(2.2rem, 4.2vw + 0.5rem, 3.6rem)` para Hero H1.

## Spacing & Geometry
- Escala 4-pt semántica:
  - `--space-3xs`: 0.25rem (4px)
  - `--space-2xs`: 0.5rem (8px)
  - `--space-xs`: 0.75rem (12px)
  - `--space-sm`: 1rem (16px)
  - `--space-md`: 1.5rem (24px)
  - `--space-lg`: 2rem (32px)
  - `--space-xl`: 3rem (48px)
  - `--space-2xl`: 4.5rem (72px)
  - `--space-3xl`: 6rem (96px)
- Radios estructurados:
  - `--radius-xs`: 3px (etiquetas técnicas)
  - `--radius-sm`: 5px (entradas de datos, inputs)
  - `--radius-md`: 8px (botones, tarjetas técnicas)
  - `--radius-lg`: 12px (módulos contenedores)

## Motion Stance (Motion-Cut)
- Cero librerías JavaScript de animación.
- Transiciones CSS puras en `transform` y `opacity` (≤ 180ms) con curva `--ease-out: cubic-bezier(0.16, 1, 0.3, 1)`.
- Respeto total a `@media (prefers-reduced-motion: reduce)`.

## CTA Voice
- **Primary CTA**: Fondo `--color-accent`, texto blanco, radio `--radius-md`, sin gradientes ni sombras AI. Vocabulario imperativo claro: *"Registrar Empresa en la Red"*, *"Publicar Sobras de Material"*, *"Acceder a Lotes"*.
- **Secondary CTA**: Fondo superficie, borde `1px solid var(--color-rule)`, texto `--color-ink`, hover con sutil cambio de fondo y borde.

## Exports

### tokens.css
Ver archivo `tokens.css` en la raíz del proyecto y vinculado en las vistas.
