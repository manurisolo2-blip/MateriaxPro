# MateriaX Pro — Modelo Relacional y Normalización
**Red Industrial de Reutilización Circular y Simbiosis de Polímeros**  
**Instituto Técnico Río Tercero — Curso 6° B**  
**Espacios Curriculares:** Bases de Datos · Laboratorio de Aplicaciones II · Laboratorio de Programación  
**Docentes:** Vanesa Stucher · Francisco Rissone · Simón Zanetti  

---

## 1. Metodología de Traducción del DER al Modelo Relacional

La derivación del esquema relacional a partir del Diagrama Entidad-Relación conceptual se basa en la aplicación sistemática de las **reglas formales de mapeo relacional** (Elmasri & Navathe / Date):

1. **Regla 1 (Mapeo de Entidades Fuertes a Tablas):**  
   Cada entidad fuerte (`USUARIO`, `CATEGORIA_POLIMERO`) genera una relación base con su clave primaria correspondiente (`id`).
2. **Regla 2 (Mapeo de Entidades Débiles por Existencia):**  
   Entidades dependientes (`PLANTA_INDUSTRIAL`, `PRODUCTO`, `MENSAJE_NEGOCIACION`, `AUDITORIA_ESTADO_LOTE`) se convierten en tablas independientes cuya clave primaria es un identificador subrogado simple (`id`), incorporando como claves foráneas obligatorias (`NOT NULL`) los identificadores de sus entidades propietarias.
3. **Regla 3 (Mapeo de Relaciones Binarias 1:N):**  
   Para cada relación 1:N (ej. `USUARIO` $\to$ `PRODUCTO`), la clave primaria del lado "1" se propaga como **clave foránea (FK)** en el lado "N". Se indexa físicamente mediante árboles B-Tree para optimizar las operaciones de reunión (*JOIN*).
4. **Regla 4 (Mapeo de Relaciones 1:1 Subordinadas):**  
   La relación entre `TRANSACCION` y `CERTIFICADO_AMBIENTAL` es 1:1. La clave foránea `transaccion_id` se ubica en `CERTIFICADO_AMBIENTAL` con restricción de unicidad (`UNIQUE`), garantizando biunivocidad estricta.
5. **Regla 5 (Políticas de Integridad Referencial Transaccional):**  
   * **`ON DELETE CASCADE`:** Aplicado en registros subordinados operativos (ej. si se elimina un borrador de producto, sus mensajes de consulta se suprimen en cascada).
   * **`ON DELETE RESTRICT`:** Aplicado en entidades comerciales y de auditoría (ej. una empresa con transacciones históricas o certificados ambientales emitidos no puede ser suprimida físicamente sin antes archivar o preservar la trazabilidad fiscal).

---

## 2. Notación Relacional Formal (Modelo de Codd)

A continuación se presentan los esquemas relacionales formales, subrayando las **claves primarias (PK)** y señalando las **claves foráneas (FK)** y **candidatas (UK)**:

### 2.1. Módulo de Identidad y Sedes Fabriles
$$\text{USUARIOS}(\underline{\text{id}}, \text{nombre}, \text{email}^{\text{UK}}, \text{password}, \text{cuit}^{\text{UK}}, \text{telefono}, \text{rubro}, \text{direccion\_fiscal}, \text{ciudad\_fiscal}, \text{provincia\_fiscal}, \text{rol}, \text{estado}, \text{verificado\_afip}, \text{created\_at}, \text{updated\_at}, \text{ultimo\_login})$$
* $\text{PK} = \{\text{id}\}$
* $\text{UK}_1 = \{\text{email}\}, \quad \text{UK}_2 = \{\text{cuit}\}$

$$\text{PLANTAS\_INDUSTRIALES}(\underline{\text{id}}, \text{empresa\_id}^{\text{FK}}, \text{nombre\_planta}, \text{direccion}, \text{localidad}, \text{provincia}, \text{coordenadas\_gps}, \text{posee\_bascula}, \text{activa}, \text{created\_at}, \text{updated\_at})$$
* $\text{PK} = \{\text{id}\}$
* $\text{FK}: \text{empresa\_id} \to \text{USUARIOS}(\text{id}) \quad [\text{ON DELETE CASCADE}]$

---

### 2.2. Módulo de Catálogo y Oferta de Polímeros
$$\text{CATEGORIAS\_POLIMERO}(\underline{\text{id}}, \text{codigo\_spi}, \text{sigla}^{\text{UK}}, \text{nombre\_tecnico}, \text{densidad\_g\_cm3}, \text{temp\_fusion\_c}, \text{factor\_co2\_kg}, \text{reciclabilidad}, \text{activo})$$
* $\text{PK} = \{\text{id}\}$
* $\text{UK} = \{\text{sigla}\}$

$$\text{PRODUCTOS}(\underline{\text{id}}, \text{user\_id}^{\text{FK}}, \text{planta\_id}^{\text{FK}}, \text{categoria\_id}^{\text{FK}}, \text{nombre}, \text{tipo\_polimero}, \text{presentacion}, \text{color}, \text{fluidez\_mfi}, \text{contaminacion\_pct}, \text{cantidad\_kg}, \text{cantidad\_disp\_kg}, \text{pedido\_minimo\_kg}, \text{precio\_unitario}, \text{moneda}, \text{ubicacion}, \text{descripcion}, \text{estado}, \text{created\_at}, \text{updated\_at})$$
* $\text{PK} = \{\text{id}\}$
* $\text{FK}_1: \text{user\_id} \to \text{USUARIOS}(\text{id}) \quad [\text{ON DELETE CASCADE}]$
* $\text{FK}_2: \text{planta\_id} \to \text{PLANTAS\_INDUSTRIALES}(\text{id}) \quad [\text{ON DELETE SET NULL}]$
* $\text{FK}_3: \text{categoria\_id} \to \text{CATEGORIAS\_POLIMERO}(\text{id}) \quad [\text{ON DELETE RESTRICT}]$

---

### 2.3. Módulo Transaccional y Certificación Ambiental
$$\text{TRANSACCIONES}(\underline{\text{id}}, \text{codigo\_operacion}^{\text{UK}}, \text{producto\_id}^{\text{FK}}, \text{comprador\_id}^{\text{FK}}, \text{vendedor\_id}^{\text{FK}}, \text{planta\_origen\_id}^{\text{FK}}, \text{cantidad\_kg}, \text{precio\_unitario\_pactado}, \text{subtotal}, \text{costo\_flete}, \text{monto\_total}, \text{modalidad\_retiro}, \text{estado}, \text{ticket\_balanza\_kg}, \text{numero\_remito}, \text{fecha\_solicitud}, \text{fecha\_acuerdo}, \text{fecha\_despacho}, \text{fecha\_cierre})$$
* $\text{PK} = \{\text{id}\}$
* $\text{UK} = \{\text{codigo\_operacion}\}$
* $\text{FK}_1: \text{producto\_id} \to \text{PRODUCTOS}(\text{id}) \quad [\text{ON DELETE RESTRICT}]$
* $\text{FK}_2: \text{comprador\_id} \to \text{USUARIOS}(\text{id}) \quad [\text{ON DELETE RESTRICT}]$
* $\text{FK}_3: \text{vendedor\_id} \to \text{USUARIOS}(\text{id}) \quad [\text{ON DELETE RESTRICT}]$
* $\text{FK}_4: \text{planta\_origen\_id} \to \text{PLANTAS\_INDUSTRIALES}(\text{id}) \quad [\text{ON DELETE SET NULL}]$

$$\text{CERTIFICADOS\_AMBIENTALES}(\underline{\text{id}}, \text{codigo\_certificado}^{\text{UK}}, \text{transaccion\_id}^{\text{FK, UK}}, \text{generadora\_id}^{\text{FK}}, \text{revalorizadora\_id}^{\text{FK}}, \text{kg\_recuperados}, \text{co2\_evitado\_kg}, \text{mwh\_ahorrado}, \text{fecha\_emision})$$
* $\text{PK} = \{\text{id}\}$
* $\text{UK}_1 = \{\text{codigo\_certificado}\}, \quad \text{UK}_2 = \{\text{transaccion\_id}\}$
* $\text{FK}_1: \text{transaccion\_id} \to \text{TRANSACCIONES}(\text{id}) \quad [\text{ON DELETE RESTRICT}]$
* $\text{FK}_2: \text{generadora\_id} \to \text{USUARIOS}(\text{id}) \quad [\text{ON DELETE RESTRICT}]$
* $\text{FK}_3: \text{revalorizadora\_id} \to \text{USUARIOS}(\text{id}) \quad [\text{ON DELETE RESTRICT}]$

---

### 2.4. Módulo de Comunicación y Auditoría
$$\text{MENSAJES\_NEGOCIACION}(\underline{\text{id}}, \text{producto\_id}^{\text{FK}}, \text{transaccion\_id}^{\text{FK}}, \text{emisor\_id}^{\text{FK}}, \text{receptor\_id}^{\text{FK}}, \text{mensaje}, \text{leido}, \text{created\_at})$$
* $\text{PK} = \{\text{id}\}$
* $\text{FK}_1: \text{producto\_id} \to \text{PRODUCTOS}(\text{id}) \quad [\text{ON DELETE CASCADE}]$
* $\text{FK}_2: \text{transaccion\_id} \to \text{TRANSACCIONES}(\text{id}) \quad [\text{ON DELETE CASCADE}]$
* $\text{FK}_3: \text{emisor\_id} \to \text{USUARIOS}(\text{id}) \quad [\text{ON DELETE CASCADE}]$
* $\text{FK}_4: \text{receptor\_id} \to \text{USUARIOS}(\text{id}) \quad [\text{ON DELETE CASCADE}]$

$$\text{AUDITORIA\_ESTADO\_LOTE}(\underline{\text{id}}, \text{producto\_id}^{\text{FK}}, \text{usuario\_id}^{\text{FK}}, \text{estado\_anterior}, \text{estado\_nuevo}, \text{motivo}, \text{ip\_address}, \text{created\_at})$$
* $\text{PK} = \{\text{id}\}$
* $\text{FK}_1: \text{producto\_id} \to \text{PRODUCTOS}(\text{id}) \quad [\text{ON DELETE CASCADE}]$
* $\text{FK}_2: \text{usuario\_id} \to \text{USUARIOS}(\text{id}) \quad [\text{ON DELETE RESTRICT}]$

---

## 3. Diccionario Físico de Datos (MariaDB / MySQL InnoDB)

### 3.1. Tabla: `usuarios`
| Campo | Tipo MySQL | Nulo | Clave | Default | Restricción / Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT(11) UNSIGNED` | NO | **PK** | `AUTO_INCREMENT` | Identificador único de usuario. |
| `nombre` | `VARCHAR(120)` | NO | | | Razón social o denominación legal. |
| `email` | `VARCHAR(150)` | NO | **UK** | | Correo electrónico corporativo único. |
| `password` | `VARCHAR(255)` | NO | | | Hash seguro bcrypt. |
| `cuit` | `VARCHAR(20)` | NO | **UK** | | CUIT tributario validado ante AFIP. |
| `telefono` | `VARCHAR(30)` | NO | | | Teléfono de planta / contacto comercial. |
| `rubro` | `VARCHAR(100)` | SÍ | | `NULL` | Inyección, Extrusión, Reciclado, etc. |
| `direccion_fiscal`| `VARCHAR(150)` | SÍ | | `NULL` | Domicilio legal de la empresa. |
| `ciudad_fiscal` | `VARCHAR(100)` | SÍ | | `NULL` | Ciudad de la sede legal. |
| `provincia_fiscal`| `VARCHAR(100)`| SÍ | | `NULL` | Provincia de radicación. |
| `rol` | `VARCHAR(50)` | NO | **INDEX**| `'empresa'` | `'empresa'`, `'admin'`, `'operador'`. |
| `estado` | `ENUM(...)` | NO | **INDEX**| `'pendiente'` | `'pendiente'`, `'activo'`, `'inactivo'`, `'rechazado'`. |
| `verificado_afip` | `TINYINT(1)` | NO | | `0` | `1` = Homologado, `0` = Sin auditar. |
| `created_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP`| Alta de registro. |
| `updated_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP`| Modificación de perfil. |
| `ultimo_login` | `DATETIME` | SÍ | | `NULL` | Registro de última autenticación. |

---

### 3.2. Tabla: `plantas_industriales`
| Campo | Tipo MySQL | Nulo | Clave | Default | Restricción / Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT(11) UNSIGNED` | NO | **PK** | `AUTO_INCREMENT` | Identificador de planta. |
| `empresa_id` | `INT(11) UNSIGNED` | NO | **FK, INDEX**| | Referencia a `usuarios(id)`. |
| `nombre_planta`| `VARCHAR(100)` | NO | | | Nombre identificatorio del predio fabril. |
| `direccion` | `VARCHAR(150)` | NO | | | Dirección física para carga/descarga. |
| `localidad` | `VARCHAR(100)` | NO | **INDEX**| | Localidad o municipio. |
| `provincia` | `VARCHAR(100)` | NO | **INDEX**| | Provincia de radicación fabril. |
| `coordenadas_gps`| `VARCHAR(50)` | SÍ | | `NULL` | Formato latitud,longitud para geolocalización. |
| `posee_bascula`| `TINYINT(1)` | NO | | `1` | `1` = Báscula de camiones operativa. |
| `activa` | `TINYINT(1)` | NO | | `1` | `1` = Planta operativa, `0` = Clausurada. |
| `created_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP`| Fecha de alta de sede. |
| `updated_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP`| Fecha de edición. |

---

### 3.3. Tabla: `categorias_polimero`
| Campo | Tipo MySQL | Nulo | Clave | Default | Restricción / Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT(11) UNSIGNED` | NO | **PK** | `AUTO_INCREMENT` | Identificador de la resina. |
| `codigo_spi` | `TINYINT UNSIGNED` | NO | **INDEX**| | Código SPI (1 a 7). |
| `sigla` | `VARCHAR(10)` | NO | **UK** | | Identificador unívoco (`PP`, `PEAD`, `ABS`, etc.). |
| `nombre_tecnico`| `VARCHAR(100)` | NO | | | Nombre químico completo. |
| `densidad_g_cm3`| `DECIMAL(4,3)` | NO | | | Densidad estándar de referencia ($g/cm^3$). |
| `temp_fusion_c` | `SMALLINT` | SÍ | | `NULL` | Temperatura de fusión en °C. |
| `factor_co2_kg` | `DECIMAL(5,2)` | NO | | `1.85` | $kg\text{ }CO_2\text{eq}$ evitados por kg reciclado. |
| `reciclabilidad`| `ENUM(...)` | NO | | `'alta'` | `'alta'`, `'media'`, `'baja'`, `'especial'`. |
| `activo` | `TINYINT(1)` | NO | | `1` | Estado en catálogo. |

---

### 3.4. Tabla: `productos`
| Campo | Tipo MySQL | Nulo | Clave | Default | Restricción / Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT(11) UNSIGNED` | NO | **PK** | `AUTO_INCREMENT` | Identificador único del lote. |
| `user_id` | `INT(11) UNSIGNED` | NO | **FK, INDEX**| | Referencia a `usuarios(id)` titular. |
| `planta_id` | `INT(11) UNSIGNED` | SÍ | **FK, INDEX**| `NULL` | Referencia a `plantas_industriales(id)`. |
| `categoria_id` | `INT(11) UNSIGNED` | SÍ | **FK, INDEX**| `NULL` | Referencia a `categorias_polimero(id)`. |
| `nombre` | `VARCHAR(150)` | NO | | | Título técnico o comercial de la publicación. |
| `tipo_polimero`| `VARCHAR(50)` | NO | **INDEX**| | Sigla de resina (compatibilidad y filtros directos). |
| `presentacion` | `ENUM(...)` | NO | **INDEX**| `'scrap_molido'`| `'scrap_molido'`, `'pellet_regranulado'`, `'purga_torta'`, etc. |
| `color` | `VARCHAR(50)` | NO | | `'Negro'` | Pigmentación del material. |
| `fluidez_mfi` | `DECIMAL(6,2)` | SÍ | | `NULL` | Índice MFI en $g/10\text{ min}$. |
| `contaminacion_pct`|`DECIMAL(5,2)` | NO | | `0.00` | Porcentaje estimado de impurezas. |
| `cantidad_kg` | `DECIMAL(10,2)` | NO | | | Kilos totales originales publicados. |
| `cantidad_disp_kg`|`DECIMAL(10,2)` | NO | **INDEX**| | Kilos disponibles para reserva/compra. |
| `pedido_minimo_kg`|`DECIMAL(10,2)` | NO | | `100.00` | Volumen mínimo por operación. |
| `precio_unitario`| `DECIMAL(10,2)` | NO | | | Precio por kg en pesos/dólares. |
| `moneda` | `ENUM(...)` | NO | | `'ARS'` | `'ARS'`, `'USD'`. |
| `ubicacion` | `VARCHAR(100)` | NO | | | Ciudad de retiro visible. |
| `descripcion` | `TEXT` | SÍ | | `NULL` | Memoria técnica, empaque y pureza. |
| `estado` | `ENUM(...)` | NO | **INDEX**| `'Disponible'` | `'Disponible'`, `'Reservado'`, `'Vendido'`, `'Pausado'`, `'Retirado'`. |
| `created_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP`| Fecha de publicación. |
| `updated_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP`| Fecha de edición. |

---

### 3.5. Tabla: `transacciones`
| Campo | Tipo MySQL | Nulo | Clave | Default | Restricción / Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT(11) UNSIGNED` | NO | **PK** | `AUTO_INCREMENT` | Identificador de la transacción. |
| `codigo_operacion`| `VARCHAR(20)` | NO | **UK** | | Código de trazabilidad (ej: `TRX-2026-0001`). |
| `producto_id` | `INT(11) UNSIGNED` | NO | **FK, INDEX**| | Referencia a `productos(id)`. |
| `comprador_id`| `INT(11) UNSIGNED` | NO | **FK, INDEX**| | Empresa demandante adquirente. |
| `vendedor_id` | `INT(11) UNSIGNED` | NO | **FK, INDEX**| | Empresa oferente generadora. |
| `planta_origen_id`| `INT(11) UNSIGNED`| SÍ | **FK** | `NULL` | Planta donde se retira la mercadería. |
| `cantidad_kg` | `DECIMAL(10,2)` | NO | | | Kilos pactados en la transacción. |
| `precio_unitario_pactado`|`DECIMAL(10,2)`| NO | | | Precio por kilo cerrado. |
| `subtotal` | `DECIMAL(12,2)` | NO | | | $\text{cantidad\_kg} \times \text{precio\_pactado}$. |
| `costo_flete` | `DECIMAL(10,2)` | NO | | `0.00` | Flete o logística asociada. |
| `monto_total` | `DECIMAL(12,2)` | NO | | | Valor bruto total de la orden. |
| `modalidad_retiro`| `ENUM(...)` | NO | | `'retiro_comprador'`| Logística acordada entre partes. |
| `estado` | `ENUM(...)` | NO | **INDEX**| `'solicitada'` | `'solicitada'`, `'en_evaluacion'`, `'aprobada'`, `'pesaje_balanza'`, `'completada'`, etc. |
| `ticket_balanza_kg`|`DECIMAL(10,2)`| SÍ | | `NULL` | Kilos reales pesados en báscula. |
| `numero_remito`| `VARCHAR(50)` | SÍ | | `NULL` | Guía o remito legal de traslado. |
| `fecha_solicitud`| `DATETIME` | NO | | `CURRENT_TIMESTAMP`| Apertura de orden. |
| `fecha_acuerdo`| `DATETIME` | SÍ | | `NULL` | Confirmación de condiciones. |
| `fecha_despacho`| `DATETIME` | SÍ | | `NULL` | Carga sobre camión. |
| `fecha_cierre` | `DATETIME` | SÍ | | `NULL` | Recepción y conformidad final. |

---

### 3.6. Tabla: `certificados_ambientales`
| Campo | Tipo MySQL | Nulo | Clave | Default | Restricción / Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT(11) UNSIGNED` | NO | **PK** | `AUTO_INCREMENT` | Identificador único del certificado. |
| `codigo_certificado`| `VARCHAR(40)`| NO | **UK** | | Hash alfanumérico / UUID para QR. |
| `transaccion_id`| `INT(11) UNSIGNED`| NO | **FK, UK**| | Transacción única certificada (1:1). |
| `generadora_id`| `INT(11) UNSIGNED`| NO | **FK, INDEX**| | Empresa generadora del descarte. |
| `revalorizadora_id`|`INT(11) UNSIGNED`| NO | **FK, INDEX**| | Empresa recicladora/adquirente. |
| `kg_recuperados`| `DECIMAL(10,2)` | NO | | | Kilogramos certificados reincorporados. |
| `co2_evitado_kg`| `DECIMAL(10,2)` | NO | | | Reducción neta de huella de carbono. |
| `mwh_ahorrado` | `DECIMAL(8,2)` | NO | | | Energía equivalente preservada. |
| `fecha_emision`| `DATETIME` | NO | | `CURRENT_TIMESTAMP`| Fecha de firma y expedición digital. |

---

### 3.7. Tabla: `mensajes_negociacion`
| Campo | Tipo MySQL | Nulo | Clave | Default | Restricción / Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT(11) UNSIGNED` | NO | **PK** | `AUTO_INCREMENT` | Identificador del mensaje. |
| `producto_id` | `INT(11) UNSIGNED` | NO | **FK, INDEX**| | Lote consultado. |
| `transaccion_id`| `INT(11) UNSIGNED`| SÍ | **FK, INDEX**| `NULL` | Transacción asociada si ya se inició orden. |
| `emisor_id` | `INT(11) UNSIGNED` | NO | **FK, INDEX**| | Usuario remitente. |
| `receptor_id` | `INT(11) UNSIGNED` | NO | **FK, INDEX**| | Usuario destinatario. |
| `mensaje` | `TEXT` | NO | | | Propuesta técnica, consulta de flete o precio. |
| `leido` | `TINYINT(1)` | NO | | `0` | `1` = Leído, `0` = No leído. |
| `created_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP`| Fecha y hora de envío. |

---

### 3.8. Tabla: `auditoria_estados_lote`
| Campo | Tipo MySQL | Nulo | Clave | Default | Restricción / Descripción |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT(11) UNSIGNED` | NO | **PK** | `AUTO_INCREMENT` | Identificador del log. |
| `producto_id` | `INT(11) UNSIGNED` | NO | **FK, INDEX**| | Lote afectado. |
| `usuario_id` | `INT(11) UNSIGNED` | NO | **FK, INDEX**| | Usuario o admin que ejecutó la acción. |
| `estado_anterior`| `VARCHAR(30)` | NO | | | Estado previo al cambio. |
| `estado_nuevo`| `VARCHAR(30)` | NO | | | Nuevo estado aplicado. |
| `motivo` | `VARCHAR(255)` | SÍ | | `NULL` | Justificación técnica o comercial. |
| `ip_address` | `VARCHAR(45)` | SÍ | | `NULL` | Dirección IP de origen. |
| `created_at` | `DATETIME` | SÍ | | `CURRENT_TIMESTAMP`| Marca temporal inmutable del evento. |

---

## 4. Demostración Matemática Formal de Normalización

El esquema relacional completo de **MateriaX Pro** satisface con rigor matemático las condiciones de las **tres primeras formas normales (1FN, 2FN, 3FN)** y la **Forma Normal de Boyce-Codd (FNBC)**:

### 4.1. Primera Forma Normal (1FN)
* **Definición Formal:** Una relación $R$ está en 1FN si y sólo si el dominio de cada atributo contiene únicamente valores indivisibles (atómicos) y no existen grupos repetitivos ni atributos multivaluados en ninguna tupla.
* **Demostración:**
  1. Todos los campos de tipo dirección fueron desglosados en atributos atómicos (`direccion`, `localidad`, `provincia`).
  2. Los teléfonos, correos y contactos se modelan individualmente sin arrays serializados ni listas separadas por comas.
  3. Las múltiples plantas fabriles de una empresa se extrajeron a su propia relación `plantas_industriales`, eliminando cualquier grupo repetitivo de sedes dentro de `usuarios`.
  4. Los múltiples mensajes y estados históricos se modelaron en tablas hijas independientes vinculadas mediante claves foráneas.
  $$\therefore \text{El esquema se encuentra estrictamente en 1FN.}$$

### 4.2. Segunda Forma Normal (2FN)
* **Definición Formal:** Una relación $R$ está en 2FN si y sólo si está en 1FN y todo atributo no primo $A \in (R - K)$ depende funcionalmente de manera completa de cada clave candidata $K$ (no existen dependencias parciales de subconjuntos propios de una clave).
* **Demostración mediante el Teorema de Clave Simple:**
  $$\forall R \in \text{Esquema MateriaX}, \quad |PK(R)| = 1 \quad (\text{todas las claves primarias son el atributo simple subrogado } id)$$
  Dado que una dependencia parcial requiere formalmente que exista un subconjunto propio $K' \subset PK$ tal que $K' \to A$, y en este esquema $|PK| = 1$, no existen subconjuntos propios no vacíos de la clave.
  $$\nexists K' \subset PK \implies \text{Es matemáticamente imposible la presencia de dependencias parciales.}$$
  $$\therefore \text{El esquema se encuentra estrictamente en 2FN.}$$

### 4.3. Tercera Forma Normal (3FN)
* **Definición Formal:** Una relación $R$ está en 3FN si y sólo si está en 2FN y ningún atributo no primo depende transitivamente de una clave candidata (es decir, para toda dependencia funcional no trivial $X \to Y$, o bien $X$ es una superclave, o bien $Y$ está compuesto exclusivamente por atributos primos).
* **Demostración:**
  1. En `productos`, los datos fiscales y de contacto de la empresa oferente no se repiten: se resuelven exclusivamente mediante $user\_id \to USUARIOS(id)$.
  2. Las propiedades intrínsecas de la resina química (densidad, temperatura de fusión, factor de emisión $CO_2$) residen en `categorias_polimero` y no se duplican en cada lote: se resuelven mediante $categoria\_id \to CATEGORIAS\_POLIMERO(id)$.
  3. En `transacciones`, los nombres de las empresas y plantas se resuelven por sus respectivas claves foráneas `comprador_id`, `vendedor_id`, `planta_origen_id`. No existe transitividad entre atributos descriptivos.
  4. En `certificados_ambientales`, la relación con la transacción es 1:1, asegurando que los datos provienen del acuerdo comercial certificado.
  $$\therefore \text{El esquema se encuentra estrictamente en 3FN.}$$

### 4.4. Forma Normal de Boyce-Codd (FNBC / BCNF)
* **Definición Formal:** Una relación $R$ está en FNBC si y sólo si para toda dependencia funcional no trivial $X \to Y$ en $R$, el conjunto determinante $X$ es una **superclave** de $R$.
* **Demostración:**
  * En `usuarios`: Las dependencias no triviales son $id \to R$, $email \to R$ y $cuit \to R$. Como $\{id\}$, $\{email\}$ y $\{cuit\}$ son claves candidatas (superclaves), se cumple FNBC.
  * En `categorias_polimero`: $id \to R$ y $sigla \to R$. Ambos determinantes son superclaves. Se cumple FNBC.
  * En `transacciones`: $id \to R$ y $codigo\_operacion \to R$. Ambos son determinantes únicos (superclaves). Se cumple FNBC.
  * En `certificados_ambientales`: $id \to R$, $codigo\_certificado \to R$ y $transaccion\_id \to R$. Todos los determinantes son superclaves. Se cumple FNBC.
  $$\therefore \text{El esquema relacional completo cumple la Forma Normal de Boyce-Codd (FNBC).}$$

---

## 5. Estrategia Física de Indexación B-Tree (Optimización de Consultas)

Para soportar alta concurrencia de consultas en MySQL/MariaDB InnoDB, se establecen los siguientes índices B-Tree:

```sql
-- 1. Optimización en usuarios
CREATE INDEX idx_usuarios_rol ON usuarios(rol);
CREATE INDEX idx_usuarios_estado ON usuarios(estado);
CREATE INDEX idx_usuarios_cuit ON usuarios(cuit);

-- 2. Optimización en plantas_industriales
CREATE INDEX idx_plantas_empresa ON plantas_industriales(empresa_id);
CREATE INDEX idx_plantas_provincia_localidad ON plantas_industriales(provincia, localidad);

-- 3. Optimización en categorias_polimero
CREATE INDEX idx_categorias_spi ON categorias_polimero(codigo_spi);

-- 4. Optimización en productos
CREATE INDEX idx_productos_user_id ON productos(user_id);
CREATE INDEX idx_productos_planta_id ON productos(planta_id);
CREATE INDEX idx_productos_categoria_id ON productos(categoria_id);
CREATE INDEX idx_productos_tipo_polimero ON productos(tipo_polimero);
CREATE INDEX idx_productos_estado ON productos(estado);
CREATE INDEX idx_productos_stock_disponible ON productos(cantidad_disp_kg);

-- 5. Optimización en transacciones
CREATE INDEX idx_transacciones_comprador ON transacciones(comprador_id);
CREATE INDEX idx_transacciones_vendedor ON transacciones(vendedor_id);
CREATE INDEX idx_transacciones_producto ON transacciones(producto_id);
CREATE INDEX idx_transacciones_estado ON transacciones(estado);
CREATE INDEX idx_transacciones_fecha ON transacciones(fecha_solicitud);

-- 6. Optimización en mensajes y auditoría
CREATE INDEX idx_mensajes_producto ON mensajes_negociacion(producto_id);
CREATE INDEX idx_mensajes_receptor_leido ON mensajes_negociacion(receptor_id, leido);
CREATE INDEX idx_auditoria_producto ON auditoria_estados_lote(producto_id);
```

---

## 6. Vistas SQL de Negocio para Explotación Analítica

### 6.1. Vista de Catálogo Comercial Activo (`v_catalogo_activo`)
Permite al marketplace listar instantáneamente los lotes disponibles combinando los datos del oferente, la planta de retiro y las especificaciones químicas de la resina:

```sql
CREATE OR REPLACE VIEW v_catalogo_activo AS
SELECT 
    p.id AS producto_id,
    p.nombre AS titulo_lote,
    p.tipo_polimero,
    c.codigo_spi,
    c.nombre_tecnico AS familia_quimica,
    p.presentacion,
    p.color,
    p.fluidez_mfi,
    p.contaminacion_pct,
    p.cantidad_disp_kg AS kilos_disponibles,
    p.precio_unitario,
    (p.cantidad_disp_kg * p.precio_unitario) AS valor_lote_estimado,
    u.id AS vendedor_id,
    u.nombre AS empresa_vendedora,
    COALESCE(pl.nombre_planta, 'Sede Central') AS planta_retiro,
    COALESCE(pl.localidad, u.ciudad_fiscal, p.ubicacion) AS localidad_retiro,
    COALESCE(pl.provincia, u.provincia_fiscal) AS provincia_retiro,
    pl.posee_bascula,
    p.created_at AS fecha_publicacion
FROM productos p
INNER JOIN usuarios u ON p.user_id = u.id
LEFT JOIN categorias_polimero c ON p.categoria_id = c.id
LEFT JOIN plantas_industriales pl ON p.planta_id = pl.id
WHERE p.estado = 'Disponible' 
  AND p.cantidad_disp_kg > 0
  AND u.estado = 'activo';
```

### 6.2. Vista de Balance e Impacto Ambiental Circular (`v_balance_ambiental_co2`)
Calcula en tiempo real los indicadores sustentables (kilos reciclados, $CO_2$ evitado y energía preservada) consolidados por empresa generadora y por tipo de polímero:

```sql
CREATE OR REPLACE VIEW v_balance_ambiental_co2 AS
SELECT 
    u.id AS empresa_id,
    u.nombre AS empresa,
    c.sigla AS tipo_resina,
    COUNT(cert.id) AS certificados_emitidos,
    SUM(cert.kg_recuperados) AS total_kg_reincorporados,
    SUM(cert.co2_evitado_kg) AS total_kg_co2_evitados,
    SUM(cert.mwh_ahorrado) AS total_mwh_ahorrados
FROM certificados_ambientales cert
INNER JOIN usuarios u ON cert.generadora_id = u.id
INNER JOIN transacciones t ON cert.transaccion_id = t.id
INNER JOIN productos p ON t.producto_id = p.id
LEFT JOIN categorias_polimero c ON p.categoria_id = c.id
GROUP BY u.id, u.nombre, c.sigla;
```
