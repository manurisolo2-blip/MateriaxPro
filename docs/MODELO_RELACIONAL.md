# Hito 1 — Modelo Relacional y Normalización
**Instituto Técnico Río Tercero — Curso 6° B**  
**Proyecto:** MateriaX — Red Industrial de Reutilización Circular  
**Espacios Curriculares:** Bases de Datos · Laboratorio de Aplicaciones II · Laboratorio de Programación  
**Docentes:** Vanesa Stucher · Francisco Rissone · Simón Zanetti  

---

## 1. Metodología de Traducción del DER al Modelo Relacional

La transformación del Diagrama Entidad-Relación conceptual al esquema lógico relacional se realizó aplicando rigurosamente las **reglas canónicas de mapeo relacional**:

1. **Regla 1 (Entidades Regulares $\to$ Tablas):**  
   Cada entidad del DER (`USUARIO` y `PRODUCTO`) se mapea en una relación (tabla) independiente del modelo relacional.
   
2. **Regla 2 (Atributos Simples y Compuestos $\to$ Columnas):**  
   Cada atributo atómico pasa a ser una columna con su tipo de dato correspondiente. El atributo compuesto *Radicación* se descompone en tres columnas atómicas independientes: `direccion`, `ciudad` y `provincia`.

3. **Regla 3 (Relación Binaria 1:N $\to$ Propagación de Clave Foránea):**  
   La relación `PUBLICA` vincula `USUARIO (1)` con `PRODUCTO (N)`. Según la regla de transformación para relaciones 1:N, la clave primaria del lado "1" (`USUARIO.id`) se propaga como **clave foránea (FK)** en la relación del lado "N" (`PRODUCTOS.user_id`). Dado que la participación de `PRODUCTO` es total, este atributo se define como obligatorio (`NOT NULL`).

4. **Regla 4 (Integridad Referencial y Restricciones de Acción):**  
   Se establece la regla `ON DELETE CASCADE ON UPDATE CASCADE` para garantizar que la baja o actualización del identificador de una empresa mantenga la consistencia transaccional sin dejar registros huérfanos.

---

## 2. Notación Relacional Formal (Modelo de Codd)

De acuerdo con el estándar formal del modelo relacional, las relaciones se representan mediante sus esquemas relacionales, subrayando las **claves primarias (PK)** con línea continua y señalando las **claves foráneas (FK)**:

$$\text{USUARIOS}(\underline{\text{id}}, \text{nombre}, \text{email}^{\text{UK}}, \text{password}, \text{cuit}, \text{telefono}, \text{rubro}, \text{ciudad}, \text{provincia}, \text{direccion}, \text{rol}, \text{estado}, \text{created\_at}, \text{updated\_at}, \text{ultimo\_login})$$

* **Clave Primaria (PK):** `id`
* **Clave Alternativa / Candidata (UK):** `email`
* **Restricción de Dominio:** $\text{estado} \in \{\text{'pendiente'}, \text{'activo'}, \text{'inactivo'}, \text{'rechazado'}\}$

$$\text{PRODUCTOS}(\underline{\text{id}}, \text{user\_id}^{\text{FK}}, \text{nombre}, \text{tipo\_polimero}, \text{cantidad\_kg}, \text{precio\_unitario}, \text{ubicacion}, \text{descripcion}, \text{estado}, \text{created\_at}, \text{updated\_at})$$

* **Clave Primaria (PK):** `id`
* **Clave Foránea (FK):** $\text{user\_id} \to \text{USUARIOS}(\text{id}) \quad [\text{ON DELETE CASCADE, ON UPDATE CASCADE}]$
* **Restricción de Dominio:** $\text{estado} \in \{\text{'Disponible'}, \text{'Reservado'}, \text{'Vendido'}\}$
* **Restricción de Dominio:** $\text{cantidad\_kg} > 0 \quad \land \quad \text{precio\_unitario} \ge 0$

---

## 3. Diccionario Físico de Datos (Motor MySQL / MariaDB InnoDB)

### Tabla: `usuarios` (Entidad Principal)
Almacena la información de las plantas industriales oferentes/demandantes y administradores auditores.

| Columna | Tipo de Dato MySQL | Nulo | Clave | Valor por Defecto | Restricción / Comentario |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT(11) UNSIGNED` | NO | **PK** | `AUTO_INCREMENT` | Identificador único autoincremental. |
| `nombre` | `VARCHAR(100)` | NO | | | Razón social o nombre de la empresa. |
| `email` | `VARCHAR(150)` | NO | **UK** | | Correo electrónico corporativo único. |
| `password` | `VARCHAR(255)` | NO | | | Hash seguro bcrypt (`password_hash`). |
| `cuit` | `VARCHAR(20)` | NO | | | CUIT validado (formato 30-XXXXXXXX-X). |
| `telefono` | `VARCHAR(30)` | NO | | | Teléfono de planta / contacto institucional. |
| `rubro` | `VARCHAR(100)` | SÍ | | `NULL` | Sector: Inyección, Extrusión, Reciclado... |
| `ciudad` | `VARCHAR(100)` | SÍ | | `NULL` | Ciudad o localidad de radicación. |
| `provincia` | `VARCHAR(100)` | SÍ | | `NULL` | Provincia de radicación. |
| `direccion` | `VARCHAR(150)` | SÍ | | `NULL` | Domicilio legal o parque industrial. |
| `rol` | `VARCHAR(50)` | NO | **INDEX**| `'empresa'` | Perfil: `'empresa'` o `'admin'`. |
| `estado` | `ENUM(...)` | NO | **INDEX**| `'pendiente'` | `'pendiente'`, `'activo'`, `'inactivo'`, `'rechazado'`. |
| `created_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP` | Fecha y hora de alta del registro. |
| `updated_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP` | Actualización automática en modificación. |
| `ultimo_login` | `DATETIME` | SÍ | | `NULL` | Fecha y hora del último login autenticado. |

---

### Tabla: `productos` (Entidad Secundaria)
Almacena los lotes de materiales y excedentes de polímeros puestos en circulación circular.

| Columna | Tipo de Dato MySQL | Nulo | Clave | Valor por Defecto | Restricción / Comentario |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT(11) UNSIGNED` | NO | **PK** | `AUTO_INCREMENT` | Identificador numérico del lote. |
| `user_id` | `INT(11) UNSIGNED` | NO | **FK, INDEX** | | Referencia a `usuarios(id)`. |
| `nombre` | `VARCHAR(150)` | NO | | | Denominación del material/scrap/pellet. |
| `tipo_polimero` | `VARCHAR(50)` | NO | **INDEX**| | Familia: PE, PP, PVC, ABS, Nylon (PA), PET. |
| `cantidad_kg` | `DECIMAL(10,2)` | NO | | | Volumen disponible en kg (peso neto). |
| `precio_unitario`| `DECIMAL(10,2)` | NO | | | Precio por kg en pesos argentinos ($ ARS). |
| `ubicacion` | `VARCHAR(100)` | NO | | | Planta fabril o parque industrial de retiro. |
| `descripcion` | `TEXT` | SÍ | | `NULL` | Ficha técnica, pureza, malla, presentación. |
| `estado` | `ENUM(...)` | NO | **INDEX**| `'Disponible'` | `'Disponible'`, `'Reservado'`, `'Vendido'`. |
| `created_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP` | Fecha y hora de publicación. |
| `updated_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP` | Fecha y hora de última modificación. |

---

## 4. Demostración Formal de Formas Normales (Normalización)

El diseño relacional implementado en **MateriaX Pro** satisface rigurosamente los axiomas y condiciones de las **tres primeras formas normales (1FN, 2FN, 3FN)** y la **Forma Normal de Boyce-Codd (FNBC)**:

### A. Primera Forma Normal (1FN)
Una relación $R$ está en 1FN si y sólo si:
1. Todos los atributos contienen **valores atómicos** (indivisibles dentro del dominio).
2. No existen **grupos repetitivos** ni atributos multivaluados en una misma tupla.
3. Existe una **clave primaria definida** que identifica de manera unívoca cada tupla.

* **Demostración en `usuarios`:** Todos los campos (`nombre`, `email`, `cuit`, etc.) almacenan un único valor atómico. El domicilio se descompuso en `direccion`, `ciudad` y `provincia`. La clave primaria es `id`. Cumple 1FN.
* **Demostración en `productos`:** Ningún registro almacena múltiples polímeros ni listas de precios en un solo campo. Cada lote es unívocamente determinado por `id`. Cumple 1FN.

---

### B. Segunda Forma Normal (2FN)
Una relación $R$ está en 2FN si y sólo si:
1. Está en 1FN.
2. Todo atributo no primo (aquél que no forma parte de ninguna clave candidata) tiene **dependencia funcional completa** de cada clave candidata; es decir, ningún atributo no primo depende funcionalmente de un subconjunto propio de una clave candidata (no existen dependencias parciales).

* **Teorema de Clave Simple:** *Si una relación está en 1FN y todas sus claves candidatas son simples (compuestas por un único atributo), entonces la relación está necesariamente en 2FN.*
* **Demostración:**
  * En `usuarios`, la clave primaria es simple: $\{\text{id}\}$. La clave candidata alternativa también es simple: $\{\text{email}\}$. Al no existir claves compuestas, es matemáticamente imposible que exista una dependencia funcional parcial $A \to Y$ con $A \subset \text{PK}$.
  * En `productos`, la clave primaria es simple: $\{\text{id}\}$. Todo atributo no primo (`nombre`, `tipo_polimero`, `cantidad_kg`, `precio_unitario`, `ubicacion`, `descripcion`, `estado`) depende funcionalmente de la clave completa `id`.
  * **Conclusión:** Ambas tablas están estrictamente en **2FN**.

---

### C. Tercera Forma Normal (3FN)
Una relación $R$ está en 3FN si y sólo si:
1. Está en 2FN.
2. Para toda dependencia funcional no trivial $X \to Y$, se cumple al menos una de las siguientes condiciones:
   * $X$ es una superclave de $R$.
   * $Y$ es un atributo primo (pertenece a alguna clave candidata de $R$).
   * *O equivalentemente:* No existen **dependencias transitivas** de atributos no primos respecto de la clave primaria.

* **Demostración en `usuarios`:**
  * Dependencias funcionales existentes:
    $$\text{id} \to \{\text{nombre, email, password, cuit, telefono, rubro, ciudad, provincia, direccion, rol, estado, ...}\}$$
    $$\text{email} \to \{\text{id, nombre, password, cuit, telefono, rubro, ciudad, provincia, direccion, rol, estado, ...}\}$$
  * Ningún atributo no primo determina a otro atributo no primo (por ejemplo, el `rubro` no determina la `ciudad`, ni el `telefono` determina el `rol`). No hay dependencias transitivas $\text{id} \to X \to Y$. Cumple 3FN.

* **Demostración en `productos`:**
  * Dependencias funcionales existentes:
    $$\text{id} \to \{\text{user\_id, nombre, tipo\_polimero, cantidad\_kg, precio\_unitario, ubicacion, descripcion, estado, ...}\}$$
  * Los datos de la empresa oferente (`nombre`, `email`, `cuit`, `telefono`) **NO se almacenan en `productos`**, evitando la dependencia transitiva $\text{id} \to \text{user\_id} \to \text{empresa\_email}$. Toda la información de la empresa se consulta a través del JOIN relacional.
  * Por tanto, todo atributo no primo depende **directa, exclusivamente y sin intermediarios** de la clave primaria `id`. Cumple **3FN**.

---

### D. Forma Normal de Boyce-Codd (FNBC / BCNF)
Una relación está en FNBC si para toda dependencia funcional no trivial $X \to Y$, el determinante $X$ es una **superclave**.

* Tanto en `usuarios` (donde los únicos determinantes son las superclaves `id` y `email`) como en `productos` (donde el único determinante es `id`), todo determinante es superclave.
* **Conclusión:** El esquema de base de datos se encuentra en **FNBC (Boyce-Codd)**, el estándar óptimo de diseño sin anomalías de inserción, borrado ni actualización.

---

## 5. Estrategia de Indexación y Optimización Física en MariaDB/MySQL

Para garantizar alto rendimiento con tiempos de respuesta sub-milisegundo en las consultas más frecuentes de la plataforma:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        MAPA DE ÍNDICES B-TREE                          │
├───────────────────┬───────────────────────────────┬────────────────────┤
│ Tabla             │ Índice / Nombre               │ Propósito          │
├───────────────────┼───────────────────────────────┼────────────────────┤
│ usuarios          │ PRIMARY KEY (id)              │ Búsqueda por PK    │
│ usuarios          │ UNIQUE KEY (email)            │ Login unívoco      │
│ usuarios          │ KEY idx_usuarios_estado       │ Filtro auditoría   │
│ usuarios          │ KEY idx_usuarios_rol          │ Filtro rol admin   │
├───────────────────┼───────────────────────────────┼────────────────────┤
│ productos         │ PRIMARY KEY (id)              │ Búsqueda por PK    │
│ productos         │ KEY idx_productos_user_id     │ JOINs con usuarios │
│ productos         │ KEY idx_productos_tipo        │ Filtro polímero    │
│ productos         │ KEY idx_productos_estado      │ Filtro mercado     │
└───────────────────┴───────────────────────────────┴────────────────────┘
```

1. **`PRIMARY KEY (id)` (Clustered Index):** Organiza físicamente las filas en el disco sobre un árbol B+ para accesos en $O(\log N)$.
2. **`UNIQUE (email)`:** Garantiza la unicidad corporativa y acelera el inicio de sesión (`SELECT * FROM usuarios WHERE email = ?`).
3. **`KEY idx_productos_user_id (user_id)`:** Optimiza la consulta del panel de cada empresa (`WHERE user_id = ?`) y acelera los `JOINs` entre productos y usuarios.
4. **`KEY idx_productos_tipo_polimero (tipo_polimero)`:** Acelera el filtrado del catálogo del mercado circular (`WHERE tipo_polimero = 'Polietileno (PE)'`).
5. **`KEY idx_productos_estado (estado)`:** Acelera la exclusión de lotes vendidos o reservados (`WHERE estado = 'Disponible'`).
6. **`KEY idx_usuarios_estado (estado)`:** Acelera la bandeja de entrada del Administrador para empresas pendientes de auditoría (`WHERE estado = 'pendiente'`).

---

## 6. Integridad Transaccional y Motor de Almacenamiento

* **Motor InnoDB:** Se utiliza exclusivamente el motor transaccional `InnoDB`, el cual ofrece:
  * Cumplimiento estricto de las propiedades **ACID** (Atomicidad, Consistencia, Aislamiento y Durabilidad).
  * Soporte nativo de **Claves Foráneas (Foreign Keys)** con verificación en tiempo de ejecución.
  * **Bloqueos a nivel de fila (Row-Level Locking)** para máxima concurrencia en transacciones de reserva y venta.
  * Conjunto de caracteres `utf8mb4` con colación `utf8mb4_unicode_ci` para soporte completo de acentuación, caracteres especiales y símbolos industriales.
