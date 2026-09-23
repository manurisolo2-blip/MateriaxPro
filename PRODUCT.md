# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users
Cualquier persona, empresa o industria vinculada a la reutilización, reciclaje o transformación de materiales plásticos (scrap, mermas, granzas y descartes industriales) que necesita publicar o adquirir excedentes plásticos de manera directa.

## Product Purpose
MateriaX Pro es un marketplace y red de intercambio para comercializar y circular excedentes de polímeros y plásticos reciclados, facilitando el encuentro entre oferentes y compradores con transparencia en volúmenes, precios y especificaciones del material.

## Positioning
Marketplace accesible y directo para la comercialización de plásticos reciclados y mermas industriales, diseñado para operar sin fricciones burocráticas y conectar la oferta y demanda de materiales secundarios con máxima velocidad.

## Operating Context
- Uso en plantas industriales, talleres de moldeo/extrusión, acopios y oficinas comerciales.
- Acceso tanto desde puestos de escritorio como desde dispositivos móviles en pie de planta.

## Capabilities and Constraints
- **Restricción Cero JavaScript**: Toda la interfaz de usuario se construye y opera estrictamente mediante HTML5 semántico, CSS moderno y envíos HTTP estándar con protección CSRF nativa.
- **Pila Tecnológica**: PHP 8.2+ con CodeIgniter 4, persistencia en MySQL (desarrollo local) y SQLite autosanable (despliegues serverless en Vercel).
- **Libertad Estética**: Libertad de diseño en paleta de colores, tipografías y composición visual para potenciar la modernidad, jerarquía y claridad de la interfaz.

## Brand Commitments
- Nombre: **MateriaX Pro** (MateriaX).
- Isotipo oficial disponible en `public/assets/logos/isotipo-black.png`.

## Evidence on Hand
- Módulo funcional CRUD de productos/lotes con clasificación por tipo de polímero (PE, PP, PET, PVC, PS, otros).
- Panel de gestión para usuarios registrados con resumen de inventario y publicaciones.
- Suite de pruebas unitarias automatizadas en PHPUnit que validan la ausencia total de scripts y la seguridad de las rutas.

## Product Principles
1. **Publicación y Búsqueda Sin Fricción**: Facilitar la carga y consulta de lotes de material sin pasos superfluos.
2. **Eficiencia y Carga Ultrarrápida**: Navegación fluida y ligera al apoyarse 100% en renderizado del lado del servidor y CSS optimizado.
3. **Transparencia en el Material**: Toda publicación destaca el tipo de resina plástica, cantidad en kilogramos, precio unitario y ubicación de retiro.
4. **Robustez Multiplataforma**: Despliegue garantizado tanto en entornos locales Apache/XAMPP como en arquitectura serverless.

## Accessibility & Inclusion
- Estructura semántica estándar para lectores de pantalla, campos de formulario con etiquetas explícitas y navegación por teclado sin trampas de foco.
