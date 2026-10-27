# MateriaX — Red Industrial de Reutilización Circular

**Instituto Técnico Río Tercero — Curso 6° B**  
**Proyecto Página Web — Hito 1: Primeros Pasos**  
**Espacios Curriculares:** Bases de Datos · Laboratorio de Aplicaciones II · Laboratorio de Programación  
**Docentes:** Vanesa Stucher · Francisco Rissone · Simón Zanetti  

---

## 📌 Descripción del Proyecto
**MateriaX** es una plataforma corporativa desarrollada en **PHP (CodeIgniter 4)**, **HTML** y **CSS puro**, diseñada para conectar empresas e industrias verificadas que necesitan publicar, solicitar y reutilizar excedentes de polímeros industriales (Polietileno PE, Polipropileno PP, PVC, ABS, Nylon PA, PET), promoviendo la economía circular y la eficiencia de recursos.

El proyecto está estructurado estrictamente bajo el patrón arquitectónico **Modelo-Vista-Controlador (MVC)**, utilizando MySQL en XAMPP.

---

## 🚀 Requerimientos del Hito 1 Cumplidos

### 1. Diagrama Entidad-Relación (DER)
- Ubicación: [`docs/DER.md`](docs/DER.md)
- Archivo vectorial editable en **Draw.io**: [`docs/DER_drawio.xml`](docs/DER_drawio.xml)
- Define entidades (`USUARIO`, `PRODUCTO`), atributos, claves primarias/foráneas y cardinalidad (1:N).

### 2. Modelo Relacional
- Ubicación: [`docs/MODELO_RELACIONAL.md`](docs/MODELO_RELACIONAL.md)
- Tablas resultantes con claves primarias, foráneas y normalización formal (1FN, 2FN, 3FN).
- Script SQL ejecutable: [`database.sql`](database.sql).

### 3. Repositorio y Estructura MVC en CodeIgniter 4
- **Controladores (`app/Controllers/`):**
  - `Home.php`: Landing page informativa y pública de MateriaX.
  - `Auth.php`: Control de Login, Registro con hash seguro y Logout.
  - `Productos.php`: CRUD completo para la entidad secundaria de polímeros industriales.
- **Modelos (`app/Models/`):**
  - `UserModel.php`: Modelo de la tabla `usuarios` con validaciones de email único y claves.
  - `ProductoModel.php`: Modelo de la tabla `productos` con validaciones y relaciones con el usuario.
- **Vistas (`app/Views/`):**
  - `templates/header.php` y `templates/footer.php`: Layout institucional común.
  - `welcome_message.php`: Página de inicio pública.
  - `auth/login.php`: Formulario de acceso.
  - `auth/register.php`: Formulario de registro empresarial.
  - `productos/index.php`: Listado de lotes con filtros y acciones.
  - `productos/crear.php`: Alta de lotes.
  - `productos/editar.php`: Edición de lotes.
  - `productos/ver.php`: Ficha técnica de detalle.
- **Filtros (`app/Filters/AuthFilter.php`):**
  - Middleware de protección de rutas: Bloquea el acceso a `/productos*` para visitantes y redirige a `/login`.

### 4. Módulo de Autenticación (Login & Register)
- Formularios de registro con confirmación de clave y validación de correo único.
- Cifrado criptográfico de contraseñas con `password_hash()` (algoritmo bcrypt).
- Gestión de sesiones activas (`session()->set()`, `session()->destroy()`).
- Mensajes flash de notificación de éxito y error.

### 5. Primer Módulo Funcional (CRUD de Entidad Secundaria)
- **Entidad:** `productos` (Lotes de excedentes poliméricos).
- **Protección:** Visible **únicamente para usuarios con sesión activa**.
- **Operaciones:**
  - 📋 **Listar:** Visualización del inventario con buscador por nombre y filtro por tipo de polímero.
  - ➕ **Crear:** Formulario completo para registrar un lote.
  - 👁 **Ver Detalle:** Ficha técnica con cálculo de valor estimado del lote y datos de la empresa oferente.
  - ✏ **Editar:** Modificación de volúmenes, precios y especificaciones.
  - 🗑 **Eliminar:** Baja del lote con pantalla de confirmación server-side en PHP puro (100% libre de JavaScript).

### 6. Arquitectura 100% PHP y CSS Puro (Cero JavaScript)
- El sistema opera sin ningún archivo `.js`, sin etiquetas `<script>` y sin manejadores de eventos en el cliente (`onsubmit`, `onclick`).
- La interactividad, validaciones, confirmación de borrado y filtrado de catálogo se resuelven exclusivamente del lado del servidor mediante el patrón MVC de **CodeIgniter 4**.
- Suite de pruebas automatizadas (`tests/unit/ZeroJavaScriptTest.php`) con 95+ aserciones que certifican la ausencia total de JavaScript en vistas, rutas y código fuente.

---

## 🌿 Requerimientos del Hito 2 Cumplidos (Ampliación Funcional)

### 1. Gestión de Sedes y Plantas Industriales (`/plantas`)
- **Entidad:** `plantas_industriales`.
- Permite a cada empresa registrar sus predios fabriles, centros de molienda y acopios logísticos.
- Registro de atributos operativos: disponibilidad de báscula fiscal de camiones, capacidad máxima de carga (Ton), teléfono directo y contacto de despacho.
- Vinculación directa con cada lote de material (`productos.planta_id`) para establecer el punto físico de retiro.
- Controles de aislamiento estricto: una empresa solo puede gestionar sus propias plantas.

### 2. Circuito Comercial y Transaccional (`/solicitudes`)
- **Entidad:** `transacciones`.
- Flujo B2B completo entre industrias generadoras y recicladoras/transformadoras:
  - **Emisión de propuesta:** El comprador define volumen (kg), precio unitario ofertado, costos de flete y modalidad de retiro (retiro en planta, flete convenido, entrega en destino).
  - **Generación de código trazable:** Identificador único con formato formal `TRX-YYYY-XXXXX`.
  - **Bandejas separadas:** *Solicitudes Recibidas* (para el vendedor) y *Mis Compras / Enviadas* (para el comprador).
  - **Decisión del oferente:** Aprobación (pasa el lote a estado `'Reservado'` y emite el certificado ecológico) o Rechazo.
  - **Cancelación:** El comprador puede anular solicitudes en estado pendiente.

### 3. Certificación de Huella Verde y Economía Circular (`/certificados`)
- **Entidad:** `certificados_ambientales`.
- Emisión automatizada e irreversible de certificados ambientales ante cada operación concluida.
- **Cálculo formal de impacto (Norma ISO 14044):**
  - Factores de emisión de CO2 evitado según familia de resina (PET: 2.15 kg CO2/kg, PP: 1.85, PE: 1.85, PVC: 1.70, etc.).
  - Estimación de ahorro energético en MWh equivalente.
- Constancia formal imprimible con código alfanumérico único (`CERT-MX-YYYY-XXXXXXXX`) y firma digital institucional.

### 4. Soporte Multiplataforma (MySQL XAMPP + SQLite Vercel)
- Compatible para ejecución en entorno local con **MySQL en XAMPP** (`database_completa.sql`, `database_hito2.sql`).
- Compatible para despliegues serverless en **Vercel** mediante SQLite (`writable/materiax_db.sqlite`, `api/index.php`) con autocreación y migración de esquemas en caliente.

### 5. Suite de Pruebas Unitarias Integrada
- Cobertura completa de modelos, filtros, autorización y arquitectura Cero JS (`tests/unit/Hito2FunctionalTest.php`, `ZeroJavaScriptTest.php`, `AdminModuleTest.php`, etc.) con **29 tests y 100% de éxito**.

---

## 🛠 Instalación y Puesta en Marcha (XAMPP)

1. **Clonar o copiar el proyecto** en la carpeta de XAMPP:
   ```
   C:\xampp\htdocs\MateriaxPro
   ```

2. **Iniciar servicios en XAMPP Control Panel:**
   - Iniciar **Apache**.
   - Iniciar **MySQL**.

3. **Importar la Base de Datos:**
   - Abrir **phpMyAdmin** (`http://localhost/phpmyadmin/`).
   - Ir a la pestaña **Importar** y seleccionar el archivo [`database.sql`](database.sql).
   - O ejecutar por terminal / Spark:
     ```bash
     php spark migrate
     php spark db:seed DatabaseSeeder
     ```

4. **Acceder a la Aplicación:**
   - Abrir en el navegador:
     ```
     http://localhost/MateriaxPro/
     ```
     *(El sistema redirige automáticamente a `public/`)*.

---

## 🔑 Credenciales de Prueba (Demo)

| Rol | Correo Electrónico | Contraseña |
| :--- | :--- | :--- |
| Empresa / Admin | `admin@materiax.com` | `admin123` |

*(También es posible crear nuevas cuentas desde el formulario de **Registro**)*.
