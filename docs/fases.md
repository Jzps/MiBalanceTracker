# Historial técnico de desarrollo por fases

## Introducción

Este documento explica **cómo se ha construido técnicamente** el Sistema de Gestión Financiera Personal: qué se hizo en cada fase, qué carpetas y archivos se crearon o reorganizaron, y cómo se relacionan entre sí.

No sustituye a `docs/Requisitos.md`, `docs/MER.md` ni `docs/Planteamiento.md`: esos documentos describen el problema, el modelo de datos y el alcance funcional. Aquí se describe la arquitectura implementada y el estado real del código.

Este archivo debe actualizarse al finalizar cada fase posterior.

> **Nota de actualización de URL:** las referencias a `TrackerFinanzasPersonales` en las secciones históricas siguientes describen la configuración original de la Fase 1 y se conservan como registro. La URL estándar actual es `http://localhost/MiBalanceTracker/` (con `APP_URL=http://localhost/MiBalanceTracker`, `SESSION_PATH=/MiBalanceTracker`, `RewriteBase /MiBalanceTracker/` y el Alias de Apache apuntando a la carpeta física real de `public/`). Consulta `README.md` para la instalación vigente.

---

## Fase 1 — Preparar Laravel y XAMPP

### Qué se hizo

- Se instaló **Laravel 11.57.1** en la raíz del repositorio mediante Composer (`composer.json` y `composer.lock` versionados, `vendor/` ignorado).
- Se integró el frontend visual existente en la estructura Laravel sin rediseñarlo.
- Se configuró Apache/XAMPP para servir únicamente el directorio `public/` mediante un Alias:
  - `Alias /TrackerFinanzasPersonales "C:/xampp/htdocs/TrackerFinanzasPersonales/public"`
  - `<Directory>` con `AllowOverride All`, `Require all granted`, `Options -Indexes +FollowSymLinks` y `DirectoryIndex index.php`.
  - `public/.htaccess` conservado, con `RewriteBase /TrackerFinanzasPersonales/` para que las rutas funcionen bajo el subdirectorio.
- URL principal verificada: `http://localhost/TrackerFinanzasPersonales/`.

### Carpetas y archivos importantes

| Ruta | Responsabilidad |
|---|---|
| `app/` | Código de aplicación (controladores, middleware y modelos). |
| `bootstrap/` | Arranque de Laravel; en `bootstrap/app.php` se registró el alias del middleware de ingreso configurado (Fase 3). |
| `config/` | Configuración de base de datos, autenticación, sesiones, caché, etc. |
| `public/index.php` | Punto de entrada único de Laravel; Apache apunta a `public/`. |
| `public/.htaccess` | Reescritura de URLs hacia el front controller. |
| `public/assets/css/styles.css` | Hoja de estilos general (variables, layout, navbar, botones, formularios, tablas, responsive, impresión). |
| `public/assets/css/pages/*.css` | Nueve hojas específicas por módulo, cargadas solo en su vista mediante `@push('styles')`. |
| `public/assets/js/app.js` | Menú responsive, mostrar/ocultar contraseña y prevención de envío de formularios visuales de módulos. |
| `public/assets/img/`, `public/assets/favicon/` | Logotipo y favicon originales. |
| `resources/views/layouts/app.blade.php` | Layout principal: header, include del navbar, `@yield('content')`, `@stack('styles')` y carga de JS. |
| `resources/views/partials/navbar.blade.php` | Barra superior y menú lateral; enlaza los módulos mediante `route()` y contiene el formulario POST de logout (Fase 3). |
| `resources/views/pages/<modulo>/index.blade.php` | Vistas Blade de los nueve módulos, convertidas desde el HTML original sin cambiar su contenido. |
| `routes/web.php` | Rutas GET de navegación; en Fase 3 se reorganizaron en grupos público, autenticación, configuración inicial y privado. |
| `README.md` | Guía de instalación y ejecución con XAMPP. |

Las nueve páginas conservaron textualmente su contenido original dentro de `@section('content')`; el layout aporta la estructura común sin duplicar ni eliminar secciones. Los recursos CSS/JS/imágenes/favicon se sirven directamente desde `public/assets/` (sin Vite ni Node.js).

### Funcionalidades terminadas en Fase 1

Instalación de Laravel, organización de carpetas, vistas Blade, assets, CSS general y por módulo, rutas de navegación y acceso mediante Apache.

### Funcionalidades aún no implementadas en Fase 1

Base de datos, autenticación y cualquier lógica financiera.

---

## Fase 2 — Integrar la base de datos

### Qué se hizo

- Se configuró MySQL de XAMPP en `.env`/`.env.example` (`DB_CONNECTION=mysql`, base `sistema_gestion_financiera`; credenciales locales privadas no documentadas). `config/database.php` lee esas variables.
- Se conservó el esquema aprobado en `db/db.sql` como referencia única.
- Se mantuvieron las **ocho migraciones** en `database/migrations/`, en orden de dependencia: `usuario`, `categoria`, `movimiento`, `presupuesto`, `meta_ahorro`, `aporte_ahorro`, `deuda`, `pago_deuda`.
- La base real ya tenía las ocho tablas creadas desde el SQL original; se verificó que el esquema físico coincidía con las migraciones, se creó la tabla de control `migrations` y se registraron las ocho migraciones como ejecutadas (batch 1), sin reconstruir ni borrar tablas.
- Se crearon los ocho modelos Eloquent en `app/Models/`, con claves primarias personalizadas, `incrementing = true`, `keyType = int`, `timestamps = false`, `$fillable` explícito, casts decimales/fecha/boolean y todas las relaciones del MER.

### Modelos implementados

| Modelo | Tabla | Clave primaria |
|---|---|---|
| `App\Models\Usuario` | `usuario` | `id_usuario` |
| `App\Models\Categoria` | `categoria` | `id_categoria` |
| `App\Models\Movimiento` | `movimiento` | `id_movimiento` |
| `App\Models\Presupuesto` | `presupuesto` | `id_presupuesto` |
| `App\Models\MetaAhorro` | `meta_ahorro` | `id_meta` |
| `App\Models\AporteAhorro` | `aporte_ahorro` | `id_aporte` |
| `App\Models\Deuda` | `deuda` | `id_deuda` |
| `App\Models\PagoDeuda` | `pago_deuda` | `id_pago` |

### Flujo de acceso a datos

```text
Controlador futuro
       |
       v
Modelo Eloquent
       |
       v
Tabla MySQL
```

Cada modelo declara su tabla y su clave primaria real, por lo que Laravel no aplica las convenciones de pluralización ni asume una columna `id`. No se agregaron columnas `created_at`/`updated_at`, `deleted_at` ni tablas ajenas al esquema aprobado.

### Funcionalidades terminadas en Fase 2

Conexión MySQL real, migraciones verificadas, modelos y relaciones comprobadas (las relaciones se probaron con datos temporales en una base independiente, eliminada al finalizar).

### Funcionalidades aún no implementadas en Fase 2

Registro/login, controladores de módulos, CRUD y cálculos financieros.

---

## Fase 3 — Autenticación, sesiones, cookies y protección de rutas

### Archivos creados o modificados y su responsabilidad

| Archivo | Cambio | Responsabilidad |
|---|---|---|
| `app/Http/Controllers/Auth/AuthController.php` | Creado | Muestra login/registro/ingreso inicial; procesa registro, login, logout y guardado del ingreso fijo. |
| `app/Http/Middleware/Auth/EnsureIngresoFijoConfigurado.php` | Creado | Redirige a la configuración inicial cuando `ingreso_fijo_mensual` está pendiente. |
| `bootstrap/app.php` | Modificado | Registra el alias `ingreso.configurado` para el middleware anterior. |
| `app/Models/Usuario.php` | Modificado | Pasa a extender `Authenticatable`; declara `password_hash` como campo de contraseña y lo oculta en la serialización; conserva tabla, PK, fills, casts y relaciones. |
| `config/auth.php` | Modificado | El proveedor `users` usa `App\Models\Usuario::class`. |
| `config/session.php` | Modificado | Driver `file` por defecto, cookie `sistema_financiera_session`, `secure=false`, `http_only=true`, `same_site=lax`. |
| `.env` y `.env.example` | Modificados | Variables de sesión coherentes con la URL con subdirectorio (`SESSION_COOKIE`, `SESSION_PATH`, `SESSION_SECURE_COOKIE`). No se versionan credenciales privadas. |
| `resources/views/auth/login.blade.php` | Movido desde `pages/` y adaptado | Formulario POST con `@csrf`, campo `correo`, mensajes de validación y `old()`. Sin credenciales de demostración. |
| `resources/views/auth/registro.blade.php` | Movido desde `pages/` y adaptado | Formulario POST con `@csrf`, `password_confirmation`, validación y `old()`. |
| `resources/views/auth/ingreso-inicial.blade.php` | Creado | Formulario del ingreso fijo mensual con estilos generales existentes. |
| `resources/views/partials/navbar.blade.php` | Modificado | El enlace GET de "Cerrar sesión" se sustituyó por un formulario POST con `@csrf` y el mismo aspecto visual. |
| `public/assets/js/app.js` | Modificado | Se eliminó la simulación de credenciales; se conservan menú responsive, mostrar/ocultar contraseña y prevención de envío solo en formularios de módulos. |
| `routes/web.php` | Modificado | Rutas agrupadas: públicas, POST de autenticación, configuración inicial (`auth`) y módulos privados (`auth` + `ingreso.configurado`). |
| `phpunit.xml` | Modificado | Base de pruebas aislada `sistema_gestion_financiera_auth_test` y `APP_URL=http://localhost` para las pruebas. |
| `tests/Feature/Auth/*.php` | Creados | Pruebas de registro, login, logout, rutas protegidas e ingreso inicial. |
| `README.md` | Modificado | Sección de autenticación y referencia a este documento. |
| `docs/fases.md` | Creado | Este historial técnico. |

### Flujo de comunicación

```text
Formulario Blade
       |
       v
Ruta Laravel
       |
       v
AuthController
       |
       v
Modelo Usuario
       |
       v
Tabla usuario (MySQL)
       |
       v
Sesión Laravel
       |
       v
Cookie de sesión
       |
       v
Middleware
       |
       v
Módulos privados
```

**Registro:** `POST /registro` valida nombre, correo único y contraseña (mínimo 8 y `confirmed`), guarda `password_hash` con `Hash::make`, inicia sesión, regenera el ID de sesión y redirige a `/configuracion-inicial/ingreso`. No se crean categorías, movimientos ni otros registros.

**Login:** `POST /login` usa `Auth::attempt(['correo' => ..., 'password' => ...])`. Laravel consulta por `correo` y verifica el hash con `getAuthPasswordName() => password_hash`. Si falla, devuelve un mensaje genérico y conserva el correo. Si acierta, regenera la sesión y redirige a la configuración inicial si falta el ingreso, o a la URL pretendida / Dashboard. El endpoint tiene `throttle:6,1`.

**Logout:** `POST /logout` ejecuta `Auth::logout()`, invalida la sesión, regenera el token CSRF y redirige al Login. No se usa GET ni JavaScript para borrar cookies.

**Sesiones y cookies:** `SESSION_DRIVER=file` (archivos en `storage/framework/sessions/`). Laravel administra la cookie de sesión; atributos HttpOnly, SameSite=Lax, Secure=false en HTTP local y path `/TrackerFinanzasPersonales`. No hay tabla `sessions` ni cookies manuales.

**Middleware:** `auth` bloquea a los visitantes de los módulos; `ingreso.configurado` impide acceder mientras el ingreso fijo esté pendiente. La ruta de configuración inicial solo usa `auth` para evitar bucles.

**Identidad del usuario:** los futuros controladores obtendrán al usuario con `Auth::user()`, `$request->user()` y su identificador con `Auth::id()`, que corresponde a `usuario.id_usuario`. No se duplica el ID en otra variable de sesión ni en cookies.

### Funcionalidades terminadas en Fase 3

Registro real, hash de contraseñas, login real, logout por POST, sesión y cookie administradas por Laravel, protección de rutas, configuración inicial obligatoria del ingreso fijo y disponibilidad del usuario autenticado.

### Funcionalidades que todavía no existen

CRUD de movimientos, categorías, presupuestos, metas, aportes, deudas y pagos; cálculos del Dashboard, análisis o distribución 50/30/20; alertas; recuperación de contraseña; verificación de correo; "recordarme"; roles y permisos; API. El modelo `app/Models/User.php` permanece sin uso (configuración estándar de Laravel) y no sustituye a `Usuario`.

---

## Fase 4 — CRUD de Movimientos, Categorías y Presupuestos

### A. Responsabilidad

Conecta el frontend existente con MySQL para que tres módulos sean funcionales: registrar/consultar/editar/eliminar movimientos, administrar categorías propias (crear, editar, desactivar/activar) y gestionar presupuestos con progreso y alertas calculados desde movimientos reales. No incluye metas, aportes, deudas, pagos, Dashboard, Análisis ni Distribución funcionales.

### B. Archivos creados

| Archivo | Responsabilidad | Métodos |
|---|---|---|
| `app/Http/Controllers/MovimientoController.php` | Listado con filtros, alta, edición y baja de movimientos propios; valida categoría activa, propia y compatible con el tipo. | `index`, `store`, `update`, `destroy` |
| `app/Http/Controllers/CategoriaController.php` | Alta, edición y desactivación lógica de categorías propias. | `store`, `update`, `destroy` |
| `app/Http/Controllers/PresupuestoController.php` | Alta, edición, baja y cálculo de progreso/alertas de presupuestos propios. | `index`, `store`, `update`, `destroy` |
| `tests/Feature/ModulosTestCase.php` | Base de pruebas con `RefreshDatabase` y helpers para crear usuarios/categorías. | — |
| `tests/Feature/Movimientos/MovimientoTest.php` | 9 pruebas de movimientos. | — |
| `tests/Feature/Categorias/CategoriaTest.php` | 9 pruebas de categorías. | — |
| `tests/Feature/Presupuestos/PresupuestoTest.php` | 11 pruebas de presupuestos y cálculos. | — |
| `tests/Feature/Presupuestos/IntegracionUsuariosTest.php` | Prueba conjunta de aislamiento entre dos usuarios. | — |

### C. Archivos modificados

| Archivo | Motivo |
|---|---|
| `routes/web.php` | Las rutas GET de Movimientos y Presupuestos ahora apuntan a sus controladores; se añadieron rutas POST/PUT/DELETE para los tres módulos, dentro del grupo privado con `auth` + `ingreso.configurado`. |
| `resources/views/pages/movimientos/index.blade.php` | Conectar formulario, filtros, tabla y acciones; mostrar mensajes/errores; incorporar la administración de categorías sin nueva página. |
| `resources/views/pages/presupuestos/index.blade.php` | Conectar formulario, listado, progreso, alertas y acciones; mostrar mensajes/errores. |
| `public/assets/js/app.js` | Ya no cancela el envío de formularios con acción real; mantiene solo los formularios visuales sin backend y añade filtrado mínimo de categorías por tipo. |
| `docs/fases.md` | Esta sección. |
| `README.md` | Referencia a pruebas y módulos operativos (sin cambiar instalación/ejecución). |

### D. Controladores

- **MovimientoController**: `index` filtra por usuario y opcionalmente por búsqueda, tipo y mes; carga `categoria` y prepara datos para edición. `store`/`update` validan y asignan `id_usuario` desde `Auth::id()`. `destroy` bloquea la eliminación si existe `pagoDeuda`.
- **CategoriaController**: `store`/`update` validan ENUM y coherencia de tipo frente a movimientos y presupuestos existentes. `destroy` realiza baja lógica (`activa = false`); `update` con `activa=1` reactiva.
- **PresupuestoController**: `index` calcula gasto, disponible, porcentaje y estado por presupuesto. `store`/`update` validan categoría de gasto propia y activa, montos y fechas. `destroy` elimina solo el presupuesto.

### E. Modelos

Se reutilizan sin cambios `Movimiento`, `Categoria` y `Presupuesto` (tablas, PKs, `$fillable`, casts y relaciones de Fase 2), junto con `Usuario` para la identidad. No se crearon modelos nuevos. `Movimiento::pagoDeuda()` se consulta solo para proteger la integridad en la eliminación.

### F. Rutas

| Método | URL | Nombre | Operación |
|---|---|---|---|
| GET | `/movimientos` | `movimientos` | Listado (admite `buscar`, `tipo`, `mes`, `editar`, `editar_categoria`) |
| POST | `/movimientos` | `movimientos.store` | Registrar movimiento |
| PUT | `/movimientos/{movimiento}` | `movimientos.update` | Actualizar movimiento |
| DELETE | `/movimientos/{movimiento}` | `movimientos.destroy` | Eliminar movimiento |
| POST | `/categorias` | `categorias.store` | Crear categoría |
| PUT | `/categorias/{categoria}` | `categorias.update` | Editar / reactivar categoría |
| DELETE | `/categorias/{categoria}` | `categorias.destroy` | Desactivar categoría |
| GET | `/presupuestos` | `presupuestos` | Listado con progreso (admite `editar`) |
| POST | `/presupuestos` | `presupuestos.store` | Crear presupuesto |
| PUT | `/presupuestos/{presupuesto}` | `presupuestos.update` | Actualizar presupuesto |
| DELETE | `/presupuestos/{presupuesto}` | `presupuestos.destroy` | Eliminar presupuesto |

### G. Integración con frontend

Los formularios conservan sus clases, IDs y etiquetas; se añadieron `@csrf`, `@method`, `action` con `route()`, `old()` y `@error`. Las tablas muestran los registros reales y acciones con botones `btn btn-fantasma btn-pequeno`. Los mensajes usan `alerta alerta-info` / `alerta alerta-error` y los estados de presupuesto usan `etiqueta`, `barra` y `barra-relleno` con `--valor`. La administración de categorías se ubica dentro de la vista de Movimientos, sin nuevo módulo en el menú.

### H. Relaciones

`Usuario` → `movimientos`, `categorias` y `presupuestos` por `id_usuario`. `Categoria` → `movimientos` y `presupuestos` por `id_categoria`. `Movimiento` → `categoria` para mostrar el nombre; `Presupuesto` → `categoria` y, mediante consulta agregada, a los movimientos del mismo usuario/categoría/tipo/período.

### I. Seguridad

Todas las consultas usan `where('id_usuario', Auth::id())`. Los registros ajenos devuelven 404 (`findOrFail`). `id_usuario` nunca se toma del formulario. Las categorías se validan por propiedad, `activa = true` y `tipo_movimiento`; los presupuestos solo admiten categorías de gasto. Los filtros y parámetros de edición también se restringen al usuario autenticado.

### J. Cálculos y alertas

- Gasto = suma de `movimiento.monto` con mismo usuario, categoría, `tipo = gasto` y `fecha` entre `fecha_inicio` y `fecha_fin` (inclusive).
- Disponible = `monto_limite - gastado` (puede ser negativo).
- Porcentaje = `(gastado / monto_limite) * 100` (sin tope en 100%).
- Excedente: `gastado > monto_limite` (prioridad).
- Preventivo: `porcentaje >= porcentaje_alerta` y sin excedente.
- Normal: por debajo del umbral. Los valores se calculan en cada consulta; no se almacenan columnas derivadas ni cachés.

### K. Eliminaciones y conservación histórica

Las categorías se desactivan (`activa = false`) en lugar de borrarse, conservando movimientos y presupuestos históricos; los selectores de altas solo muestran activas. Un movimiento vinculado a `pago_deuda` no se elimina. Eliminar un presupuesto no afecta movimientos. Cambiar el tipo de una categoría se bloquea si existen relaciones incompatibles.

### L. Pruebas

`php artisan test`: **57 pruebas, 271 aserciones, todas pasan** (27 de autenticación + 30 nuevas). Las pruebas usan `sistema_gestion_financiera_auth_test`, nunca la base principal. Cubren altas, consultas, ediciones, bajas, validaciones, ENUM, baja lógica, aislamiento por usuario, cálculo de gastos, umbrales, excedentes y recálculo tras modificar/eliminar movimientos.

### M. Relación con fases siguientes

Los movimientos y categorías ya registrados estarán disponibles para Metas/Aportes, Deudas/Pagos, Dashboard, Análisis y Distribución 50/30/20. Estos módulos **no** están implementados en esta fase.

---

## Fase 5 — Metas, Aportes, Deudas y Pagos

### A. Responsabilidad

Hace funcionales los módulos de ahorro y deuda: crear/editar/eliminar metas y fondo de emergencia, registrar y consultar aportes con cálculo de progreso, administrar deudas con saldo calculado, y registrar pagos generando de forma atómica un movimiento de gasto vinculado. No incluye Dashboard, Análisis ni Distribución funcionales.

### B. Estructura de archivos

**Creados:** `app/Http/Controllers/MetaController.php`, `app/Http/Controllers/DeudaController.php`, `resources/views/pages/metas/_progreso.blade.php`, `tests/Feature/Metas/MetaTest.php`, `tests/Feature/Metas/AporteTest.php`, `tests/Feature/Deudas/DeudaTest.php`, `tests/Feature/Deudas/PagoDeudaTest.php`, `tests/Feature/Deudas/IntegracionPagoMovimientoTest.php`.

**Modificados:** `routes/web.php`, `resources/views/pages/metas/index.blade.php`, `resources/views/pages/deudas/index.blade.php`, `app/Http/Controllers/MovimientoController.php`, `public/assets/js/app.js`, `docs/fases.md`, `README.md`.

### C. MetaController

- `index()`: metas propias con aportes cargados; calcula acumulado, pendiente, porcentaje real y ancho de barra limitado a 100%; prepara edición.
- `store()`: valida nombre, tipo_meta, monto_objetivo y fecha; fuerza `estado = activa` e `id_usuario = Auth::id()`.
- `update()`: valida y actualiza solo metas propias; impide marcar `completada` manualmente y sincroniza el estado cuando el acumulado alcanza o deja de alcanzar el objetivo; permite pausar/cancelar.
- `destroy()`: solo permite baja física si no hay aportes; si los hay, bloquea y sugiere cancelar.
- `storeAporte()`: verifica meta propia y activa, valida monto/fecha/nota y registra el aporte mediante `$meta->aportes()` dentro de `DB::transaction`, actualizando el estado a `completada` cuando corresponde.

### D. DeudaController

- `index()`: deudas propias con pagos; calcula pagado, saldo y porcentaje; prepara edición y categorías de gasto activas para el formulario de pago.
- `store()`: valida acreedor, monto_inicial, fecha_vencimiento y pago_minimo; fuerza `pendiente` e `id_usuario` del servidor.
- `update()`: valida, impide un monto inicial menor a lo pagado, controla estados (`pagada` solo con saldo cero; `pendiente` con saldo cero se corrige a `pagada`; cancelación y reactivación permitidas).
- `destroy()`: baja física solo sin pagos; si los hay, bloquea y sugiere cancelar.
- `storePago()`: verifica deuda propia, estado y saldo; valida monto, fecha, categoría de gasto propia/activa y descripción; ejecuta la transacción descrita en H.

### E. Modelos reutilizados

`MetaAhorro`, `AporteAhorro`, `Deuda` y `PagoDeuda` se usan sin cambios, junto con `Movimiento` y `Categoria` para los pagos. No se crearon modelos ni migraciones.

### F. Relaciones

Usuario → metas (`id_usuario`), MetaAhorro → aportes (`id_meta`), Usuario → deudas (`id_usuario`), Deuda → pagos (`id_deuda`), PagoDeuda → Movimiento (`id_movimiento`, UNIQUE). La propiedad de aportes y pagos se comprueba siempre a través de la meta/deuda propietaria.

### G. Funcionamiento de los aportes

Se registran solo en `aporte_ahorro`. No crean movimientos ni afectan presupuestos (RN-06). Acumulado = `SUM(aportes.monto)`; pendiente = `MAX(objetivo - acumulado, 0)`; progreso = `acumulado / objetivo * 100`. El porcentaje real se muestra aunque supere 100%, pero la barra visual se limita a 100%.

### H. Funcionamiento de los pagos

Dentro de `DB::transaction` se crea: (1) un `Movimiento` de gasto con categoría válida, monto, fecha y descripción (derivada del acreedor si falta), y (2) un `PagoDeuda` con el `id_movimiento` recién creado. Se recalcula el saldo y, si llega a cero, la deuda pasa a `pagada`. Si falla cualquier inserción, se revierten ambas. El movimiento aparece en Movimientos y afecta presupuestos por el mecanismo de la Fase 4.

### I. Fórmulas

- Ahorro acumulado: suma de aportes de la meta.
- Ahorro pendiente: `MAX(objetivo - acumulado, 0)`.
- Porcentaje de progreso: `(acumulado / objetivo) * 100`.
- Saldo pendiente de deuda: `monto_inicial - SUM(pagos.monto)` (mínimo 0).
- Porcentaje pagado: `(pagado / monto_inicial) * 100`.

### J. Integridad y seguridad

Todas las consultas filtran por `Auth::id()`; los registros ajenos devuelven 404. No se aceptan propietarios desde formularios. Se conserva el historial: las metas con aportes y las deudas con pagos no se eliminan físicamente, se cancelan. No hay cascadas. `MovimientoController::update()` ahora bloquea la edición de movimientos vinculados a `pago_deuda` (antes solo bloqueaba la eliminación), con mensaje comprensible.

### K. Rutas

| Método | URL | Nombre | Operación |
|---|---|---|---|
| GET | `/metas` | `metas` | Listado con progreso |
| POST | `/metas` | `metas.store` | Crear meta/fondo |
| PUT | `/metas/{meta}` | `metas.update` | Editar/cancelar |
| DELETE | `/metas/{meta}` | `metas.destroy` | Eliminar sin aportes |
| POST | `/metas/{meta}/aportes` | `aportes.store` | Registrar aporte |
| GET | `/deudas` | `deudas` | Listado con pagos |
| POST | `/deudas` | `deudas.store` | Crear deuda |
| PUT | `/deudas/{deuda}` | `deudas.update` | Editar/cancelar/reactivar |
| DELETE | `/deudas/{deuda}` | `deudas.destroy` | Eliminar sin pagos |
| POST | `/deudas/{deuda}/pagos` | `pagos.store` | Registrar pago |

### L. Pruebas

`php artisan test`: **92 pruebas, 458 aserciones, todas pasan** (57 previas + 35 nuevas) sobre `sistema_gestion_financiera_auth_test`. Cubren metas y fondos, aportes, cálculos, estados automáticos, baja lógica, rollback de transacciones, pagos parciales/totales, validaciones, historia, aislamiento entre usuarios, integración con Movimientos/Presupuestos y protección de movimientos vinculados a pagos.

### M. Relación con fases siguientes

Las metas/aportes y deudas/pagos quedan disponibles para que Dashboard, Análisis y Distribución 50/30/20 consuman sus datos en fases posteriores. Esos módulos **no** están implementados aquí.

---

## Fase 6 — Integrar cálculos y consultas

Esta fase conecta los datos ya registrados por las fases anteriores con los tres módulos de consulta. No incorpora nuevas tablas, modelos ni reglas de negocio: solo consulta y presenta información, con la única excepción del formulario de porcentajes de Distribución, que actualiza tres columnas de `usuario`.

### A. Corrección inicial

`App\Http\Controllers\DistribucionController` contenía un fence Markdown de apertura (`php`) como primera línea y otro de cierre al final del archivo. Ese carácter `` ` `` producía `ParseError: syntax error, unexpected token "`"` en la línea 145 y hacía que `/distribucion` respondiera 500. Se eliminaron **únicamente esas dos líneas**; la lógica del controlador quedó intacta. Tras el cambio, `php -l` devuelve `No syntax errors detected` y las dos rutas (`distribucion` y `distribucion.actualizar`) aparecen en `php artisan route:list`.

### B. Responsabilidad de cada controlador

| Controlador | Responsabilidad |
|---|---|
| `DashboardController@index` | Resumen del mes actual del usuario autenticado. |
| `AnalisisController@index` | Estadísticas de los últimos tres meses naturales y comparación mensual. |
| `DistribucionController@index` | Referencias 50/30/20 y comparación con valores reales del mes. |
| `DistribucionController@actualizar` | Validación y guardado de los tres porcentajes del usuario. |

### C. Consultas y cálculos

**Dashboard** (`resources/views/pages/dashboard/index.blade.php`):

- `ingreso_fijo_mensual` se lee de `Auth::user()` y se mantiene en la variable `$ingresoFijo`, separada de `$ingresosAdicionales` (suma de `movimiento.tipo = ingreso` del mes). `$ingresos` es la suma de ambos, `$gastos` la suma de `movimiento.tipo = gasto` del mes y `$disponible = $ingresos - $gastos`.
- `$ahorro` suma `aporte_ahorro.monto` del mes a través de la relación `metaAhorro.id_usuario = Auth::id()`; los aportes no se cuentan como gastos.
- Los indicadores existentes de presupuestos y metas se calculan en memoria: gasto por categoría entre `fecha_inicio` y `fecha_fin` de cada presupuesto; acumulado, pendiente y progreso de cada meta con `aportes`. No se consultan deudas porque la vista no tiene ningún indicador de deudas.
- Se listan los cinco últimos movimientos del usuario con su categoría.
- La sección de distribución usa `ingreso_fijo_mensual` y los tres porcentajes del usuario para mostrar solo valores de **referencia**, sin guardarlos.

**Análisis** (`resources/views/pages/analisis/index.blade.php`):

- Rango: desde el inicio del mes `Carbon::now() - 2 meses` hasta el fin del mes actual. Solo `movimiento` filtrado por `Auth::id()` y ese rango de fechas.
- Totales de ingresos, gastos y `$ahorro = $ingresos - $gastos`, calculados sobre la colección recuperada.
- `$gastosPorCategoria` agrupa los gastos por `categoria.nombre` (con `with('categoria')`), ordena por total descendente y calcula el porcentaje de cada barra respecto al mayor total; `$evolucionGastos` y `$comparacionMensual` recorren exactamente los tres meses del rango y filtran por `fecha.format('Y-m')`.
- No se escribe ningún cálculo en la base de datos.

**Distribución 50/30/20** (`resources/views/pages/distribucion/index.blade.php`):

- Porcentajes desde `usuario.porcentaje_necesidades`, `porcentaje_deseos` y `porcentaje_ahorro`; referencias = `ingreso_fijo_mensual * porcentaje / 100`.
- Valores reales del mes: gastos filtrados por `categoria.clasificacion_503020 = necesidad|deseo` y aportes del usuario vía `AporteAhorro` y `MetaAhorro`. Los gastos y los aportes se mantienen en variables separadas.
- `$comparacion` entrega a la vista nombre, porcentaje, referencia, real, diferencia, porcentaje utilizado y ancho de barra para cada uno de los tres conceptos.
- `actualizar()` valida que cada porcentaje sea `numeric`, `0..100` y que la suma redondeada a dos decimales sea exactamente `100.00`. Si falla, lanza `ValidationException` antes de tocar el modelo, por lo que la configuración previa permanece igual. No modifica movimientos ni datos históricos.

### D. Integración y aislamiento

Todas las consultas usan `Auth::id()` directamente o `whereHas('metaAhorro', ... id_usuario ...)`, nunca parámetros de la petición. Las relaciones reutilizadas son `Movimiento::categoria`, `Presupuesto::categoria`, `MetaAhorro::aportes` y `AporteAhorro::metaAhorro`; no se crearon relaciones nuevas ni se modificaron modelos. Los datos de Dashboard, Análisis y Distribución coinciden con los registros de las fases 2, 4 y 5.

### E. Pruebas realizadas

Se añadieron 15 pruebas de característica en `tests/Feature/Dashboard`, `tests/Feature/Analisis` y `tests/Feature/Distribucion`, sobre la base aislada `sistema_gestion_financiera_auth_test`:

- **Dashboard (5):** totales, saldos y aportes del mes; separación de ingreso fijo e ingresos adicionales; meses sin movimientos; cálculo de presupuestos y metas; aislamiento entre usuarios.
- **Análisis (4):** totales y agrupación por categoría; comparación de los tres periodos con importes distintos; gráficos sin datos; aislamiento entre usuarios.
- **Distribución (6):** referencias con porcentajes personalizados; actualización que cambia referencias sin tocar movimientos; rechazo de suma distinta de 100; rechazo de valores fuera de rango; aportes no contabilizados como gastos ni generadores de movimientos; aislamiento entre usuarios.

Resultado: **15 pruebas, 103 aserciones, todas pasan**. La suite completa queda en **107 pruebas, 561 aserciones, todas pasan**, sin regresiones en las fases 1 a 5.

**Comprobación desde Apache/XAMPP (`http://localhost/MiBalanceTracker/`):** usando un usuario temporal creado solo para la verificación y eliminado al terminar (la cuenta ajena preexistente no se modificó), las tres pestañas devolvieron `200 OK` y mostraron sus encabezados y secciones. Desde el navegador HTTP se comprobó además que un envío con `50/30/21` mostraba el error de suma sin cambiar la configuración, y que `40/35/25` actualizaba las referencias a `$ 400`, `$ 350` y `$ 250`. No se realizó una inspección visual manual adicional más allá del contenido HTML verificado por HTTP.

---

## Organización general actual

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   ├── Auth/AuthController.php
│   │   ├── DashboardController.php     # Fase 6
│   │   ├── AnalisisController.php      # Fase 6
│   │   └── DistribucionController.php  # Fase 6
│   └── Middleware/Auth/EnsureIngresoFijoConfigurado.php
└── Models/                 # Ocho modelos del esquema + User.php sin uso
bootstrap/app.php           # Registro del alias de middleware
config/                     # auth.php, session.php, database.php, etc.
database/migrations/        # Ocho migraciones aprobadas
db/db.sql                   # Esquema SQL original (referencia)
docs/                       # Requisitos, MER, Planteamiento, fases.md, apache/
public/                     # index.php, .htaccess y assets (css/pages, js, img, favicon)
resources/views/
├── auth/                   # login, registro e ingreso-inicial
├── layouts/                # app.blade.php
├── partials/               # navbar.blade.php
└── pages/<modulo>/         # Vistas de los nueve módulos
routes/web.php              # Rutas públicas, auth, configuración inicial y privadas
tests/Feature/Auth/         # Pruebas de la Fase 3
tests/Feature/Dashboard/    # Pruebas de la Fase 6
tests/Feature/Analisis/     # Pruebas de la Fase 6
tests/Feature/Distribucion/ # Pruebas de la Fase 6
```

## Relación entre componentes

- Las **rutas** dirigen las peticiones al `AuthController` o a las vistas de módulo.
- El **controlador** valida y usa el **modelo `Usuario`**, que se comunica con la tabla MySQL `usuario`.
- Laravel mantiene la **sesión** en archivos y envía al navegador solo una **cookie con el ID de sesión**.
- El **middleware** lee esa sesión en cada petición: `auth` comprueba la identidad y `ingreso.configurado` comprueba el ingreso fijo.
- Las **vistas Blade** reciben los mensajes de validación y muestran los formularios con los estilos generales de `public/assets/css/styles.css`.
- El **JavaScript** conserva las interacciones visuales; ya no valida credenciales ni redirige el login.

## Fases posteriores

La Fase 6 quedó documentada y verificada arriba. El desarrollo continuará según el plan acordado: las fases posteriores se documentarán aquí cuando se implementen y verifiquen; mientras tanto no se presentan como terminadas.
