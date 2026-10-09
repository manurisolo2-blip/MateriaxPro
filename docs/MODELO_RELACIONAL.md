# Modelo Relacional y Normalización — MateriaX Pro
**Instituto Técnico Río Tercero — 6° Año "B"**  
**Materia:** Bases de Datos · Laboratorio de Aplicaciones II · Laboratorio de Programación  
**Proyecto:** MateriaX Pro — Plataforma de Economía Circular de Polímeros Industriales  
**Alumnos:** 6° B  

---

## 1. ¿De qué se trata este modelo?

Este modelo relacional representa la base de datos de **MateriaX Pro**, una plataforma web diseñada para que empresas e industrias puedan publicar y conseguir excedentes de plásticos y polímeros industriales (como polipropileno, polietileno, PVC, etc.) para reciclarlos o reutilizarlos.

Para que el sistema sea **claro, funcional y fácil de mantener**, la base de datos se organiza alrededor de dos entidades principales que resuelven el funcionamiento del sitio:
* **`usuarios`**: Guarda a las empresas que se registran en la plataforma.
* **`productos`**: Guarda los lotes de materiales plásticos que las empresas publican para vender o transferir.

---

## 2. Diferenciación de Roles y Permisos: ¿Por qué el Usuario NO puede ser Administrador?

Una regla fundamental del sistema es que **un usuario común (empresa) NUNCA puede ser administrador**, sin importar si está logeado o no logeado. 

Tienen propósitos y permisos completamente distintos en el sistema:

### Tabla de Permisos según el Tipo de Usuario

| Función / Sección | Usuario NO Logeado (Visitante) | Usuario Logeado (Empresa) | Administrador del Sistema |
| :--- | :---: | :---: | :---: |
| Ver página de inicio y explicación | Sí | Sí | Sí |
| Registrar una nueva empresa | Sí | No (ya tiene cuenta) | No |
| Iniciar sesión | Sí | No (ya inició) | No |
| Ver catálogo de materiales (Mercado) | No (requiere login) | **Sí** | **Sí** |
| Publicar un nuevo lote de polímero | No | **Sí** | No (solo modera) |
| Editar o borrar sus propios lotes | No | **Sí (solo los propios)** | **Sí (por moderación)** |
| Editar o borrar lotes de otras empresas | No | **NO (bloqueado)** | **Sí (por moderación)** |
| Ver su panel personal de estadísticas | No | **Sí (/panel)** | No (tiene /admin) |
| Modificar sus propios datos de empresa | No | **Sí (/perfil)** | No |
| **Aprobar o rechazar empresas nuevas** | **NO** | **NO** | **Sí (/admin)** |
| **Suspender o reactivar empresas** | **NO** | **NO** | **Sí (/admin)** |
| **Acceder al panel de control (/admin)** | **NO** | **NO** | **Sí (/admin)** |

### ¿Cómo aseguramos que un usuario no pueda ser administrador?

1. **En el Registro:** Cuando una empresa se registra desde el formulario público, el sistema le asigna automáticamente el rol `'empresa'` y el estado `'pendiente'`. No hay ninguna opción para elegir ser administrador.
2. **En el Perfil:** Cuando una empresa edita sus datos (nombre, teléfono, dirección, etc.), el sistema solo actualiza esos campos comerciales. El campo `rol` no se puede modificar desde el formulario.
3. **En el Filtro de Rutas (`AdminFilter`):** Todas las rutas de administración (`/admin`, `/admin/lotes`, aprobar/rechazar empresas) están protegidas por un filtro del servidor. Si un usuario no está logeado, lo manda al login. Si está logeado pero su rol es `'empresa'`, le bloquea el acceso con un mensaje de error y lo redirige al mercado.

---

## 3. Tablas de la Base de Datos

### 3.1. Tabla: `usuarios` (Empresas Registradas)
Guarda la información de cada empresa que participa en la red.

| Campo | Tipo de Dato | Clave | Nulo | Descripción |
| :--- | :--- | :---: | :---: | :--- |
| **id** | INT | **PK** | NO | Número único de identificación (autoincremental). |
| **nombre** | VARCHAR(100) | | NO | Nombre o razón social de la empresa. |
| **email** | VARCHAR(150) | **UK** | NO | Correo electrónico corporativo (único, se usa para login). |
| **password** | VARCHAR(255) | | NO | Contraseña encriptada con algoritmo seguro (bcrypt). |
| **cuit** | VARCHAR(20) | | NO | CUIT fiscal de la empresa (ej: 30-XXXXXXXX-X). |
| **telefono** | VARCHAR(30) | | NO | Teléfono de contacto institucional. |
| **rubro** | VARCHAR(100) | | SÍ | Sector de la fábrica (ej: Inyección, Extrusión, Reciclado). |
| **ciudad** | VARCHAR(100) | | SÍ | Ciudad o localidad donde está radicada. |
| **provincia** | VARCHAR(100) | | SÍ | Provincia (ej: Córdoba). |
| **direccion** | VARCHAR(150) | | SÍ | Calle y número de la planta o sede legal. |
| **rol** | VARCHAR(50) | | NO | Rol en el sistema: `'empresa'` (usuario común) o `'admin'`. |
| **estado** | ENUM | | NO | Estado de la cuenta: `'pendiente'`, `'activo'`, `'inactivo'`, `'rechazado'`. |
| **created_at** | DATETIME | | SÍ | Fecha y hora en la que se registró la empresa. |
| **updated_at** | DATETIME | | SÍ | Fecha y hora de la última modificación. |
| **ultimo_login**| DATETIME | | SÍ | Fecha y hora del último inicio de sesión. |

* **Clave Primaria (PK):** `id` (identifica a cada empresa de forma única).
* **Clave Única (UK):** `email` (no pueden existir dos cuentas con el mismo correo).

---

### 3.2. Tabla: `productos` (Lotes de Polímeros Publicados)
Guarda los lotes de materiales plásticos y polímeros que las empresas publican para reutilizar o vender.

| Campo | Tipo de Dato | Clave | Nulo | Descripción |
| :--- | :--- | :---: | :---: | :--- |
| **id** | INT | **PK** | NO | Número único del lote de producto (autoincremental). |
| **user_id** | INT | **FK** | NO | ID de la empresa que publicó el lote (conecta con `usuarios.id`). |
| **nombre** | VARCHAR(150) | | NO | Título o nombre del material (ej: "Scrap de Polipropileno Negro"). |
| **tipo_polimero**| VARCHAR(50) | | NO | Tipo de plástico: `PE`, `PP`, `PVC`, `ABS`, `PET`, etc. |
| **cantidad_kg** | DECIMAL(10,2) | | NO | Cantidad disponible en kilogramos. |
| **precio_unitario**| DECIMAL(10,2)| | NO | Precio por kilogramo en pesos ($ ARS). |
| **ubicacion** | VARCHAR(100) | | NO | Ciudad o parque industrial donde se encuentra el material. |
| **descripcion** | TEXT | | SÍ | Detalles técnicos: pureza, si es molido o en piezas, empaque, etc. |
| **estado** | ENUM | | NO | Estado comercial: `'Disponible'`, `'Reservado'`, `'Vendido'`. |
| **created_at** | DATETIME | | SÍ | Fecha y hora de la publicación del lote. |
| **updated_at** | DATETIME | | SÍ | Fecha y hora de la última edición. |

* **Clave Primaria (PK):** `id` (identifica a cada lote de forma única).
* **Clave Foránea (FK):** `user_id` (apunta al `id` de la tabla `usuarios`).

---

## 4. Relación entre las Tablas

La relación entre ambas tablas es de **1 a N (Uno a Muchos)**:

$$\text{usuarios } \mathbf{(1)} \longleftrightarrow \mathbf{(N)} \text{ productos}$$

* **Una empresa (`usuarios`)** puede publicar **0, 1 o muchos** lotes de materiales (`productos`).
* **Cada lote (`productos`)** pertenece a **una sola** empresa responsable (`user_id`).
* **Integridad Referencial (`ON DELETE CASCADE`):** Si una empresa es dada de baja definitivamente del sistema, todos sus lotes publicados se eliminan automáticamente para evitar que queden publicaciones "huérfanas" sin dueño.

---

## 5. Normalización Explicada Fácil (1FN, 2FN y 3FN)

Para garantizar que la base de datos esté bien armada y no tenga datos repetidos ni errores, aplicamos las **tres primeras formas normales**:

### 1. Primera Forma Normal (1FN) — "Datos Atómicos y sin Listas"
* **¿Qué pide la regla?** Que cada celda de la tabla contenga un único valor (que sea atómico) y que no haya columnas repetidas ni listas separadas por comas.
* **¿Cómo lo cumplimos?**
  * La dirección no se guardó toda junta en un solo texto mezclado, sino que se separó en `direccion`, `ciudad` y `provincia`. Así podemos filtrar fácilmente por ciudad sin tener que desarmar textos.
  * Cada publicación tiene su propia fila en la tabla `productos`, en lugar de guardar una lista de productos adentro de la empresa.

### 2. Segunda Forma Normal (2FN) — "Dependencia Total de la Clave"
* **¿Qué pide la regla?** Que la tabla esté en 1FN y que todos los datos dependan de la clave primaria completa, no de una parte de ella.
* **¿Cómo lo cumplimos?**
  * Como tanto `usuarios` como `productos` tienen una **clave primaria simple de un solo campo (`id`)**, no existen claves compuestas. Por lo tanto, todos los campos dependen directamente y al 100% del `id`. Se cumple automáticamente la 2FN.

### 3. Tercera Forma Normal (3FN) — "Sin Dependencias Transitivas"
* **¿Qué pide la regla?** Que la tabla esté en 2FN y que ningún campo que no sea clave dependa de otro campo que tampoco sea clave (no guardar datos repetidos que le pertenezcan a otra entidad).
* **¿Cómo lo cumplimos?**
  * En la tabla `productos` **NO** guardamos el teléfono, el CUIT, el email ni el nombre de la empresa. Solo guardamos el `user_id`.
  * Si necesitamos saber de qué empresa es un lote, hacemos un `JOIN` entre `productos` y `usuarios`. De esta manera, si la empresa cambia de teléfono, se actualiza en un solo lugar (`usuarios`) y no hay que modificar cientos de publicaciones.

---

## 6. Esquema Relacional en Notación de Codd

La forma estándar de escribir el modelo relacional es subrayando la clave primaria y marcando las claves foráneas:

* **USUARIOS** (<u>id</u>, nombre, email, password, cuit, telefono, rubro, ciudad, provincia, direccion, rol, estado, created_at, updated_at, ultimo_login)
* **PRODUCTOS** (<u>id</u>, user_id*, nombre, tipo_polimero, cantidad_kg, precio_unitario, ubicacion, descripcion, estado, created_at, updated_at)

*(Donde `user_id*` es la clave foránea que hace referencia a `USUARIOS.id`)*.

---

## 7. Script SQL de Creación (Fácil de importar en phpMyAdmin)

```sql
-- 1. Crear tabla de usuarios
CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `cuit` VARCHAR(20) NOT NULL,
  `telefono` VARCHAR(30) NOT NULL,
  `rubro` VARCHAR(100) NULL,
  `ciudad` VARCHAR(100) NULL,
  `provincia` VARCHAR(100) NULL,
  `direccion` VARCHAR(150) NULL,
  `rol` VARCHAR(50) NOT NULL DEFAULT 'empresa',
  `estado` ENUM('pendiente', 'activo', 'inactivo', 'rechazado') NOT NULL DEFAULT 'pendiente',
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ultimo_login` DATETIME NULL,
  KEY `idx_usuarios_rol` (`rol`),
  KEY `idx_usuarios_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Crear tabla de productos
CREATE TABLE `productos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nombre` VARCHAR(150) NOT NULL,
  `tipo_polimero` VARCHAR(50) NOT NULL,
  `cantidad_kg` DECIMAL(10,2) NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `ubicacion` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `estado` ENUM('Disponible', 'Reservado', 'Vendido') NOT NULL DEFAULT 'Disponible',
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_productos_user_id` (`user_id`),
  KEY `idx_productos_tipo` (`tipo_polimero`),
  KEY `idx_productos_estado` (`estado`),
  CONSTRAINT `fk_productos_usuarios` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
