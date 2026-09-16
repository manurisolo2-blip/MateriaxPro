# Hito 1 — Modelo Relacional y Normalización
**Instituto Técnico Río Tercero — Curso 6° B**  
**Proyecto:** MateriaX — Red Industrial de Reutilización Circular  
**Espacios Curriculares:** Bases de Datos · Laboratorio de Aplicaciones II · Laboratorio de Programación  
**Docentes:** Vanesa Stucher · Francisco Rissone · Simón Zanetti  

---

## 1. Traducción del DER al Modelo Relacional

A partir del Diagrama Entidad-Relación y aplicando las reglas de transformación relacional:
1. Las entidades regulares se transforman en tablas relacionales.
2. Cada atributo atómico pasa a ser una columna de la tabla.
3. La relación **1 a N** entre `USUARIO` y `PRODUCTO` se resuelve propagando la clave primaria de la entidad del lado 1 (`USUARIO.id`) como clave foránea en la tabla del lado N (`PRODUCTOS.user_id`).

---

## 2. Definición Estructural de Tablas

### Tabla: `usuarios`
Almacena las cuentas empresariales autorizadas para operar en la plataforma.

| Campo | Tipo de Dato | Nulo | Clave | Descripción |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `INT` | NO | **PK** | Clave primaria autoincremental |
| `nombre` | `VARCHAR(100)` | NO | | Razón social o nombre institucional |
| `email` | `VARCHAR(150)` | NO | **UK** | Correo electrónico corporativo único |
| `password` | `VARCHAR(255)` | NO | | Hash criptográfico bcrypt |
| `cuit` | `VARCHAR(20)` | SÍ | | Identificación tributaria |
| `telefono` | `VARCHAR(30)` | SÍ | | Teléfono de contacto |
| `rol` | `VARCHAR(50)` | NO | | Rol en el sistema (default: `'empresa'`) |
| `created_at` | `DATETIME` | SÍ | | Fecha y hora de alta |
| `updated_at` | `DATETIME` | SÍ | | Fecha y hora de modificación |

### Tabla: `productos` (Entidad Secundaria)
Almacena los lotes de materiales y excedentes de polímeros industriales puestos en circulación.

| Campo | Tipo de Dato | Nulo | Clave | Descripción |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `INT` | NO | **PK** | Clave primaria autoincremental |
| `user_id` | `INT` | NO | **FK** | Referencia a `usuarios(id)` |
| `nombre` | `VARCHAR(150)` | NO | | Denominación del material/lote |
| `tipo_polimero` | `VARCHAR(50)` | NO | | Tipo: PE, PP, PVC, ABS, Nylon, PET |
| `cantidad_kg` | `DECIMAL(10,2)` | NO | | Cantidad total en kilogramos |
| `precio_unitario`| `DECIMAL(10,2)` | NO | | Precio por kilogramo en moneda local |
| `ubicacion` | `VARCHAR(100)` | NO | | Origen geográfico / Planta industrial |
| `descripcion` | `TEXT` | SÍ | | Ficha técnica, color y pureza |
| `estado` | `ENUM(...)` | NO | | `'Disponible'`, `'Reservado'`, `'Vendido'` |
| `created_at` | `DATETIME` | SÍ | | Fecha y hora de publicación |
| `updated_at` | `DATETIME` | SÍ | | Fecha y hora de actualización |

---

## 3. Notación Relacional Formal

- **USUARIOS** (<u>id</u>, nombre, email, password, cuit, telefono, rol, created_at, updated_at)
  - **PK:** `id`
  - **UK:** `email`

- **PRODUCTOS** (<u>id</u>, *user_id*, nombre, tipo_polimero, cantidad_kg, precio_unitario, ubicacion, descripcion, estado, created_at, updated_at)
  - **PK:** `id`
  - **FK:** `user_id` referencia a `USUARIOS(id)` con `ON DELETE CASCADE ON UPDATE CASCADE`

---

## 4. Análisis de Formas Normales (Normalización)

El diseño propuesto cumple rigurosamente con las tres primeras formas normales:

### Primera Forma Normal (1FN)
- **Atributos atómicos:** Todos los atributos contienen valores indivisibles (por ejemplo, `nombre`, `cantidad_kg`, `precio_unitario`).
- **No existen grupos repetitivos ni arrays:** No se almacenan múltiples polímeros o teléfonos en una misma celda.
- **Identificador único:** Cada tabla posee una clave primaria definida (`id`).

### Segunda Forma Normal (2FN)
- Está en 1FN.
- **Dependencia funcional completa:** Como ambas tablas tienen claves primarias simples de un solo atributo (`id`), todos los atributos no clave dependen funcionalmente en su totalidad de dicha clave primaria, eliminando dependencias parciales.

### Tercera Forma Normal (3FN)
- Está en 2FN.
- **Ausencia de dependencias transitivas:** Ningún atributo no clave depende de otro atributo no clave. Todos los atributos dependen únicamente de la clave primaria. Por ejemplo:
  - En `PRODUCTOS`, `cantidad_kg` y `precio_unitario` dependen del `id` del producto. La información del usuario que lo publica no se repite en `PRODUCTOS` (no se guardan email ni teléfono en `PRODUCTOS`), sino que se enlaza mediante la clave foránea `user_id`.

---

## 5. Integridad Referencial y Reglas de Negocio
1. **Integridad de Entidad:** Las claves primarias nunca son nulas y son autoincrementales.
2. **Integridad de Dominio:** Los tipos de datos restringen entradas inválidas (decimales positivos para cantidades y precios, listas fijas de polímeros y estados).
3. **Integridad Referencial:** Un producto no puede existir sin un usuario válido asignado (`user_id NOT NULL`). Si se elimina un usuario, el motor de base de datos borra en cascada sus publicaciones asociadas para evitar registros huérfanos.
