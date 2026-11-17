# Historial técnico de desarrollo por fases

## Introducción

Este documento explica **cómo se ha construido técnicamente** el Sistema de Gestión Financiera Personal: qué se hizo en cada fase, qué carpetas y archivos se crearon o reorganizaron, y cómo se relacionan entre sí.

No sustituye a `docs/Requisitos.md`, `docs/MER.md` ni `docs/Planteamiento.md`: esos documentos describen el problema, el modelo de datos y el alcance funcional. Aquí se describe la arquitectura implementada y el estado real del código.

Este archivo debe actualizarse al finalizar cada fase posterior.

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

## Organización general actual

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   └── Auth/AuthController.php
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
```

## Relación entre componentes

- Las **rutas** dirigen las peticiones al `AuthController` o a las vistas de módulo.
- El **controlador** valida y usa el **modelo `Usuario`**, que se comunica con la tabla MySQL `usuario`.
- Laravel mantiene la **sesión** en archivos y envía al navegador solo una **cookie con el ID de sesión**.
- El **middleware** lee esa sesión en cada petición: `auth` comprueba la identidad y `ingreso.configurado` comprueba el ingreso fijo.
- Las **vistas Blade** reciben los mensajes de validación y muestran los formularios con los estilos generales de `public/assets/css/styles.css`.
- El **JavaScript** conserva las interacciones visuales; ya no valida credenciales ni redirige el login.

## Fases posteriores

El desarrollo continuará según el plan acordado. Las siguientes fases se documentarán aquí cuando se implementen y verifiquen; mientras tanto no se presentan como terminadas.
