# Modelo Entidad-Relación (MER): Sistema de Gestión Financiera Personal

El siguiente documento describe el MER del sistema de gestión financiera personal, detallando sus entidades, atributos, tipos de datos, claves primarias y foráneas, relaciones, cardinalidades y participaciones.

Es importante distinguir entre la cardinalidad y la participación: la cardinalidad indica cuántas instancias de una entidad pueden relacionarse con otra, mientras que la participación determina si esa relación es obligatoria u opcional.

---

## 1. Entidades y Atributos

### 1.1. Entidad: `USUARIO`

- **Descripción:** Representa a la persona que utiliza el sistema para administrar sus ingresos, gastos, presupuestos, metas de ahorro y deudas.

| Atributo                 | Tipo de dato | Clave / Restricción | Descripción                                                     |
| :----------------------- | :----------- | :------------------ | :-------------------------------------------------------------- |
| `id_usuario`             | INT          | PK                  | Identificador único del usuario.                                |
| `nombre`                 | VARCHAR      | —                   | Nombre del usuario.                                             |
| `correo`                 | VARCHAR      | UK                  | Correo electrónico único para identificar la cuenta.            |
| `password_hash`          | VARCHAR      | —                   | Contraseña almacenada como hash para proteger las credenciales. |
| `ingreso_fijo_mensual`   | DECIMAL      | —                   | Ingreso mensual fijo del usuario.                               |
| `porcentaje_necesidades` | DECIMAL      | —                   | Porcentaje del ingreso destinado a necesidades.                 |
| `porcentaje_deseos`      | DECIMAL      | —                   | Porcentaje del ingreso destinado a deseos.                      |
| `porcentaje_ahorro`      | DECIMAL      | —                   | Porcentaje del ingreso destinado al ahorro.                     |

- **Claves:**
  - **PK (Primary Key):** `id_usuario`.
  - **UK (Unique Key):** `correo`.
- **Reglas de negocio:** El correo no puede repetirse entre usuarios. Los porcentajes de distribución del ingreso deberían sumar $100\%$ si el sistema utiliza la regla 50/30/20 u otra distribución personalizada equivalente.

---

### 1.2. Entidad: `CATEGORIA`

- **Descripción:** Representa las categorías utilizadas para clasificar los movimientos financieros, por ejemplo, alimentación, transporte, vivienda, entretenimiento o salario.

| Atributo               | Tipo de dato | Clave / Restricción | Descripción                                                      |
| :--------------------- | :----------- | :------------------ | :--------------------------------------------------------------- |
| `id_categoria`         | INT          | PK                  | Identificador único de la categoría.                             |
| `id_usuario`           | INT          | FK                  | Usuario propietario de la categoría.                             |
| `nombre`               | VARCHAR      | —                   | Nombre de la categoría.                                          |
| `tipo_movimiento`      | ENUM         | —                   | Tipo de movimiento que admite la categoría.                      |
| `clasificacion_503020` | ENUM         | —                   | Clasificación como necesidad, deseo o ahorro, según corresponda. |
| `es_predeterminada`    | BOOLEAN      | —                   | Indica si la categoría pertenece al conjunto predeterminado.     |
| `activa`               | BOOLEAN      | —                   | Indica si la categoría está habilitada para su uso.              |

- **Claves:**
  - **PK:** `id_categoria`.
  - **FK:** `id_usuario` referencia a `USUARIO.id_usuario`.
- **Reglas de negocio:** Una categoría pertenece a un usuario. El sistema puede incluir categorías predeterminadas y permitir que el usuario cree otras, de acuerdo con las reglas de administración de categorías.

---

### 1.3. Entidad: `MOVIMIENTO`

- **Descripción:** Almacena cada operación financiera registrada por el usuario, como ingresos, gastos u otros movimientos contemplados por el sistema.

| Atributo        | Tipo de dato | Clave / Restricción | Descripción                                                |
| :-------------- | :----------- | :------------------ | :--------------------------------------------------------- |
| `id_movimiento` | INT          | PK                  | Identificador único del movimiento.                        |
| `id_usuario`    | INT          | FK                  | Usuario que registra el movimiento.                        |
| `id_categoria`  | INT          | FK                  | Categoría a la que pertenece el movimiento.                |
| `monto`         | DECIMAL      | —                   | Valor monetario del movimiento.                            |
| `fecha`         | DATE         | —                   | Fecha en la que ocurrió el movimiento.                     |
| `tipo`          | ENUM         | —                   | Tipo de movimiento, como ingreso o gasto.                  |
| `descripcion`   | VARCHAR      | —                   | Descripción breve del movimiento.                          |
| `metodo_pago`   | VARCHAR      | —                   | Medio utilizado para realizar el pago o recibir el dinero. |
| `notas`         | TEXT         | —                   | Información adicional sobre el movimiento.                 |

- **Claves:**
  - **PK:** `id_movimiento`.
  - **FK:** `id_usuario` referencia a `USUARIO.id_usuario`.
  - **FK:** `id_categoria` referencia a `CATEGORIA.id_categoria`.
- **Reglas de negocio:** Cada movimiento debe estar asociado a un usuario y a una categoría válida. El monto debe cumplir las restricciones monetarias definidas por el sistema.

---

### 1.4. Entidad: `PRESUPUESTO`

- **Descripción:** Permite establecer límites de gasto para categorías durante periodos determinados y controlar el consumo del presupuesto.

| Atributo            | Tipo de dato | Clave / Restricción | Descripción                                                  |
| :------------------ | :----------- | :------------------ | :----------------------------------------------------------- |
| `id_presupuesto`    | INT          | PK                  | Identificador único del presupuesto.                         |
| `id_usuario`        | INT          | FK                  | Usuario que configura el presupuesto.                        |
| `id_categoria`      | INT          | FK                  | Categoría a la que se aplica el presupuesto.                 |
| `monto_limite`      | DECIMAL      | —                   | Máximo de gasto establecido.                                 |
| `fecha_inicio`      | DATE         | —                   | Fecha de inicio del periodo presupuestario.                  |
| `fecha_fin`         | DATE         | —                   | Fecha de finalización del periodo.                           |
| `porcentaje_alerta` | DECIMAL      | —                   | Porcentaje consumido a partir del cual se genera una alerta. |

- **Claves:**
  - **PK:** `id_presupuesto`.
  - **FK:** `id_usuario` referencia a `USUARIO.id_usuario`.
  - **FK:** `id_categoria` referencia a `CATEGORIA.id_categoria`.
- **Reglas de negocio:** El presupuesto debe corresponder al usuario y a una categoría válida. La fecha de inicio debe ser anterior o igual a la fecha de finalización. El porcentaje de alerta permite mostrar avisos estáticos según el consumo registrado.

---

### 1.5. Entidad: `META_AHORRO`

- **Descripción:** Representa un objetivo financiero definido por el usuario, con una cantidad de dinero que desea reunir y una fecha objetivo.

| Atributo         | Tipo de dato | Clave / Restricción | Descripción                               |
| :--------------- | :----------- | :------------------ | :---------------------------------------- |
| `id_meta`        | INT          | PK                  | Identificador único de la meta.           |
| `id_usuario`     | INT          | FK                  | Usuario que establece la meta.            |
| `nombre`         | VARCHAR      | —                   | Nombre descriptivo de la meta.            |
| `tipo_meta`      | ENUM         | —                   | Tipo de objetivo de ahorro.               |
| `monto_objetivo` | DECIMAL      | —                   | Cantidad de dinero que se desea alcanzar. |
| `fecha_objetivo` | DATE         | —                   | Fecha prevista para alcanzar la meta.     |
| `estado`         | ENUM         | —                   | Estado actual de la meta.                 |

- **Claves:**
  - **PK:** `id_meta`.
  - **FK:** `id_usuario` referencia a `USUARIO.id_usuario`.
- **Reglas de negocio:** Una meta pertenece a un usuario y puede recibir múltiples aportes de ahorro. El estado permite identificar si la meta está activa, completada u otro estado definido por el sistema.

---

### 1.6. Entidad: `APORTE_AHORRO`

- **Descripción:** Registra las contribuciones monetarias que se realizan para avanzar en una meta de ahorro.

| Atributo    | Tipo de dato | Clave / Restricción | Descripción                                   |
| :---------- | :----------- | :------------------ | :-------------------------------------------- |
| `id_aporte` | INT          | PK                  | Identificador único del aporte.               |
| `id_meta`   | INT          | FK                  | Meta de ahorro a la que se destina el aporte. |
| `monto`     | DECIMAL      | —                   | Cantidad de dinero aportada.                  |
| `fecha`     | DATE         | —                   | Fecha en que se realizó el aporte.            |
| `nota`      | VARCHAR      | —                   | Observación opcional sobre el aporte.         |

- **Claves:**
  - **PK:** `id_aporte`.
  - **FK:** `id_meta` referencia a `META_AHORRO.id_meta`.
- **Reglas de negocio:** Cada aporte debe estar asociado a una meta existente. La suma de los aportes permite calcular el progreso económico de la meta.

---

### 1.7. Entidad: `DEUDA`

- **Descripción:** Representa una obligación financiera del usuario con un acreedor, incluyendo el monto inicial, el vencimiento y el pago mínimo establecido.

| Atributo            | Tipo de dato | Clave / Restricción | Descripción                                |
| :------------------ | :----------- | :------------------ | :----------------------------------------- |
| `id_deuda`          | INT          | PK                  | Identificador único de la deuda.           |
| `id_usuario`        | INT          | FK                  | Usuario responsable de la deuda.           |
| `acreedor`          | VARCHAR      | —                   | Persona o entidad a la que se debe dinero. |
| `monto_inicial`     | DECIMAL      | —                   | Valor original de la deuda.                |
| `fecha_vencimiento` | DATE         | —                   | Fecha de vencimiento de la obligación.     |
| `pago_minimo`       | DECIMAL      | —                   | Cantidad mínima de pago requerida.         |
| `estado`            | ENUM         | —                   | Estado actual de la deuda.                 |

- **Claves:**
  - **PK:** `id_deuda`.
  - **FK:** `id_usuario` referencia a `USUARIO.id_usuario`.
- **Reglas de negocio:** Una deuda pertenece a un usuario y puede recibir varios pagos. Su estado permite controlar si está pendiente, pagada u otro estado definido.

---

### 1.8. Entidad: `PAGO_DEUDA`

- **Descripción:** Registra los pagos realizados para reducir o liquidar una deuda. Además, permite relacionar un pago con el movimiento financiero que lo representa.

| Atributo        | Tipo de dato | Clave / Restricción | Descripción                             |
| :-------------- | :----------- | :------------------ | :-------------------------------------- |
| `id_pago`       | INT          | PK                  | Identificador único del pago.           |
| `id_deuda`      | INT          | FK                  | Deuda a la que corresponde el pago.     |
| `id_movimiento` | INT          | FK, UK              | Movimiento financiero asociado al pago. |
| `monto`         | DECIMAL      | —                   | Cantidad abonada a la deuda.            |
| `fecha`         | DATE         | —                   | Fecha en que se efectuó el pago.        |
| `descripcion`   | VARCHAR      | —                   | Descripción del pago realizado.         |

- **Claves:**
  - **PK:** `id_pago`.
  - **FK:** `id_deuda` referencia a `DEUDA.id_deuda`.
  - **FK:** `id_movimiento` referencia a `MOVIMIENTO.id_movimiento`.
  - **UK:** `id_movimiento`, que impide asociar un mismo movimiento a más de un registro de pago de deuda.
- **Reglas de negocio:** Cada pago debe pertenecer a una deuda y estar asociado a un movimiento financiero. La restricción única sobre `id_movimiento` permite que un movimiento represente, como máximo, un pago de deuda.

---

## 2. Relaciones, Cardinalidades y Participaciones

Para interpretar las relaciones se utiliza la siguiente convención:

- **$1:1$:** Una instancia de una entidad se relaciona con una sola instancia de la otra.
- **$1:N$:** Una instancia de una entidad puede relacionarse con varias instancias de la otra.
- **$N:M$:** Varias instancias de una entidad pueden relacionarse con varias instancias de la otra.
- **Participación total u obligatoria:** Cada instancia debe intervenir en la relación.
- **Participación parcial u opcional:** Una instancia puede existir sin intervenir todavía en la relación.

En las relaciones con clave foránea, la participación también depende de si esa clave admite valores `NULL` y de las restricciones que se definan en la base de datos.

### 2.1. `USUARIO` — `MOVIMIENTO`

- **Cardinalidad:** $1:N$.
- **Relación:** Un usuario registra movimientos financieros.
- **Participación de `USUARIO`:** Parcial; un usuario puede existir sin haber registrado movimientos.
- **Participación de `MOVIMIENTO`:** Total; cada movimiento debe pertenecer a un usuario.
- **Interpretación:** Un usuario puede registrar muchos movimientos, pero cada movimiento corresponde a un único usuario.

### 2.2. `USUARIO` — `CATEGORIA`

- **Cardinalidad:** $1:N$.
- **Relación:** Un usuario crea o administra categorías.
- **Participación de `USUARIO`:** Parcial; puede existir sin categorías propias.
- **Participación de `CATEGORIA`:** Total; cada categoría debe estar asociada a un usuario según el modelo actual.
- **Interpretación:** Un usuario puede tener varias categorías, pero cada categoría pertenece a un único usuario.
- **Observación:** Si las categorías predeterminadas deben compartirse entre todos los usuarios, convendría revisar este diseño para permitir categorías globales o un catálogo común.

### 2.3. `CATEGORIA` — `MOVIMIENTO`

- **Cardinalidad:** $1:N$.
- **Relación:** Una categoría clasifica movimientos.
- **Participación de `CATEGORIA`:** Parcial; puede existir sin movimientos asociados.
- **Participación de `MOVIMIENTO`:** Total; cada movimiento debe estar clasificado en una categoría.
- **Interpretación:** Una categoría puede clasificar muchos movimientos, pero cada movimiento utiliza una sola categoría.

### 2.4. `USUARIO` — `PRESUPUESTO`

- **Cardinalidad:** $1:N$.
- **Relación:** Un usuario configura presupuestos.
- **Participación de `USUARIO`:** Parcial; puede no haber creado presupuestos.
- **Participación de `PRESUPUESTO`:** Total; cada presupuesto pertenece a un usuario.
- **Interpretación:** Un usuario puede configurar varios presupuestos, pero cada presupuesto pertenece a un único usuario.

### 2.5. `CATEGORIA` — `PRESUPUESTO`

- **Cardinalidad:** $1:N$.
- **Relación:** Una categoría organiza presupuestos.
- **Participación de `CATEGORIA`:** Parcial; una categoría puede no tener presupuestos.
- **Participación de `PRESUPUESTO`:** Total; cada presupuesto debe estar asociado a una categoría.
- **Interpretación:** Una categoría puede tener varios presupuestos a lo largo del tiempo, pero cada presupuesto corresponde a una única categoría.
- **Observación:** Si el sistema solo permite un presupuesto por categoría y periodo, deberá añadirse una restricción de unicidad que contemple el usuario, la categoría y el periodo correspondiente.

### 2.6. `USUARIO` — `META_AHORRO`

- **Cardinalidad:** $1:N$.
- **Relación:** Un usuario establece metas de ahorro.
- **Participación de `USUARIO`:** Parcial; puede existir sin metas.
- **Participación de `META_AHORRO`:** Total; cada meta debe pertenecer a un usuario.
- **Interpretación:** Un usuario puede establecer varias metas, pero cada meta corresponde a un único usuario.

### 2.7. `META_AHORRO` — `APORTE_AHORRO`

- **Cardinalidad:** $1:N$.
- **Relación:** Una meta recibe aportes de ahorro.
- **Participación de `META_AHORRO`:** Parcial; una meta puede crearse sin recibir aportes.
- **Participación de `APORTE_AHORRO`:** Total; cada aporte debe pertenecer a una meta.
- **Interpretación:** Una meta puede recibir múltiples aportes, pero cada aporte está destinado a una sola meta.

### 2.8. `USUARIO` — `DEUDA`

- **Cardinalidad:** $1:N$.
- **Relación:** Un usuario gestiona deudas.
- **Participación de `USUARIO`:** Parcial; puede existir sin registrar deudas.
- **Participación de `DEUDA`:** Total; cada deuda pertenece a un usuario.
- **Interpretación:** Un usuario puede registrar varias deudas, pero cada deuda corresponde a un único usuario.

### 2.9. `DEUDA` — `PAGO_DEUDA`

- **Cardinalidad:** $1:N$.
- **Relación:** Una deuda recibe pagos.
- **Participación de `DEUDA`:** Parcial; una deuda puede registrarse sin haber recibido pagos.
- **Participación de `PAGO_DEUDA`:** Total; cada pago debe estar asociado a una deuda.
- **Interpretación:** Una deuda puede recibir múltiples pagos, pero cada registro de pago corresponde a una única deuda.

### 2.10. `MOVIMIENTO` — `PAGO_DEUDA`

- **Cardinalidad:** $1:1$ como máximo, considerando la restricción UK de `id_movimiento`.
- **Relación:** Un movimiento representa un pago de deuda.
- **Participación de `MOVIMIENTO`:** Parcial; no todos los movimientos representan pagos de deuda.
- **Participación de `PAGO_DEUDA`:** Total; cada registro de pago debe referenciar un movimiento.
- **Interpretación:** Un movimiento puede no estar relacionado con un pago de deuda o representar uno solo. Cada pago de deuda debe tener un movimiento asociado.
- **Importante:** Debido a que `id_movimiento` es una clave foránea única, la relación permite que un movimiento tenga como máximo un pago de deuda. Sin embargo, si `id_movimiento` no se declara `NOT NULL`, la base de datos podría permitir pagos sin movimiento asociado. Para garantizar la participación obligatoria de `PAGO_DEUDA`, el atributo debe ser obligatorio. Además, la clave única no obliga a que cada movimiento sea un pago de deuda: la relación es opcional desde el lado de `MOVIMIENTO`.

---

## 3. Resumen General de las Relaciones

| N.º | Entidad 1     | Entidad 2       | Cardinalidad | Participación de la entidad 1 | Participación de la entidad 2 |
| :-: | :------------ | :-------------- | :----------: | :---------------------------- | :---------------------------- |
|  1  | `USUARIO`     | `MOVIMIENTO`    |    $1:N$     | Parcial                       | Total                         |
|  2  | `USUARIO`     | `CATEGORIA`     |    $1:N$     | Parcial                       | Total                         |
|  3  | `CATEGORIA`   | `MOVIMIENTO`    |    $1:N$     | Parcial                       | Total                         |
|  4  | `USUARIO`     | `PRESUPUESTO`   |    $1:N$     | Parcial                       | Total                         |
|  5  | `CATEGORIA`   | `PRESUPUESTO`   |    $1:N$     | Parcial                       | Total                         |
|  6  | `USUARIO`     | `META_AHORRO`   |    $1:N$     | Parcial                       | Total                         |
|  7  | `META_AHORRO` | `APORTE_AHORRO` |    $1:N$     | Parcial                       | Total                         |
|  8  | `USUARIO`     | `DEUDA`         |    $1:N$     | Parcial                       | Total                         |
|  9  | `DEUDA`       | `PAGO_DEUDA`    |    $1:N$     | Parcial                       | Total                         |
| 10  | `MOVIMIENTO`  | `PAGO_DEUDA`    | $1:1$ máximo | Parcial                       | Total                         |

_Nota: La participación total indicada para las entidades dependientes presupone que sus claves foráneas correspondientes son obligatorias (`NOT NULL`)._
