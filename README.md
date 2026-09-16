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
  - 🗑 **Eliminar:** Baja física del lote con confirmación.

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
