# Sistema de Gestión Financiera Personal

Aplicación web del **Sistema de Gestión Financiera Personal** organizada con **Laravel 11** sobre XAMPP (Apache y MySQL). Conserva el frontend visual existente y utiliza migraciones de Laravel para preparar la estructura de base de datos aprobada.

> **Nota de esta fase:** se ha adaptado la estructura del proyecto a Laravel y se ha preparado la base técnica (vistas, rutas, assets, configuración y migraciones). **No** se ha implementado autenticación real, sesiones personalizadas, cookies, registro funcional, consultas CRUD ni lógica financiera.

## Acceso a la aplicación

```text
URL principal:
http://localhost/TrackerFinanzasPersonales/
```

- **Ubicación del proyecto:** preferiblemente en `C:\xampp\htdocs\TrackerFinanzasPersonales` (ajusta la ruta si tu copia local está en otra ubicación).
- **Servicios requeridos:** inicia **Apache** y **MySQL** desde el panel de control de XAMPP.
- **Configuración de Apache:** Apache debe exponer únicamente el directorio `public/` del proyecto mediante un **Alias** hacia `/TrackerFinanzasPersonales` (ver sección [Configurar Apache (XAMPP)](#configurar-apache-xampp)).
- **Pantalla inicial:** al abrir la URL principal debe mostrarse la pantalla de **Login**.
- **Dirección alternativa (opcional):** si se configura un Virtual Host, puede usarse `http://sistema-financiero.test` como alternativa; esta no sustituye la URL principal documentada arriba.

## Tecnologías utilizadas

- **PHP** 8.2 (compatible con la versión instalada en el entorno de trabajo).
- **Laravel** 11.x (instalado mediante Composer).
- **Composer** (gestión de dependencias PHP).
- **MySQL** (XAMPP).
- **Apache** (XAMPP).
- **HTML, CSS y JavaScript** (frontend conservado desde `public/assets/`).

**Node.js y npm no son necesarios** para ejecutar la aplicación en esta fase: los estilos y scripts se sirven directamente desde `public/assets/`, sin compilación con Vite.

> **Nota sobre Laravel 13:** Laravel 13 requiere PHP 8.3+. En el entorno auditado se encontró PHP 8.2.12, por lo que se instaló **Laravel 11** (compatible con PHP 8.2). Si el equipo actualiza a PHP 8.3+, se podrá evaluar una migración a Laravel 13 en una fase posterior.

## Requisitos previos

Cada integrante debe tener instalado:

- **XAMPP** con Apache, MySQL y una versión de **PHP compatible con Laravel 11** (PHP 8.2 o superior).
- **Composer**.
- **Git** (si se obtiene el proyecto desde el repositorio remoto).

Extensiones PHP requeridas (habilitadas en el `php.ini` que usa Apache/CLI): Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, XML.

Comprobación de versiones:

```bash
php -v
composer --version
```

En Windows con XAMPP, conviene comprobar también el PHP que usa Apache/XAMPP:

```powershell
C:\xampp\php\php.exe -v
```

## Obtener el proyecto

1. Clona el repositorio o copia la carpeta del proyecto.
2. Ubícalo dentro de la carpeta de XAMPP, por ejemplo:

```text
C:\xampp\htdocs\TrackerFinanzasPersonales
```

Ajusta el nombre de la carpeta si tu copia local usa otro nombre.

## Instalar dependencias PHP

Desde la raíz del proyecto, ejecuta:

```bash
composer install
```

Composer descargará las dependencias necesarias en la carpeta `vendor/` según lo definido en `composer.lock`. **No** uses `composer update` como comando habitual de instalación.

La carpeta `vendor/` no se versiona en Git.

## Crear el archivo de entorno

Crea `.env` a partir de `.env.example`.

En Windows (PowerShell):

```powershell
Copy-Item .env.example .env
```

Cada integrante debe configurar sus propios datos locales de conexión MySQL en `.env`. El archivo `.env` no se comparte en Git.

Para la URL principal documentada, `.env.example` utiliza:

```env
APP_URL=http://localhost/TrackerFinanzasPersonales
```

## Configurar MySQL

1. Inicia **MySQL** desde el panel de control de XAMPP.
2. Accede a **phpMyAdmin** (por ejemplo `http://localhost/phpmyadmin`).
3. Crea la base de datos vacía:

```sql
CREATE DATABASE sistema_gestion_financiera;
```

4. Configura los parámetros `DB_*` en `.env` (usuario/contraseña según tu instalación local de XAMPP):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_gestion_financiera
DB_USERNAME=root
DB_PASSWORD=
```

**Nuevas instalaciones:** deben utilizar las **migraciones de Laravel** para crear las tablas. No ejecutes simultáneamente `db/db.sql` y `php artisan migrate` sobre la misma base vacía como si fueran procesos independientes, ya que crearían las mismas tablas y pueden provocar conflictos.

`db/db.sql` se conserva como **referencia del esquema original aprobado**.

## Generar clave de aplicación

```bash
php artisan key:generate
```

Si modificas variables de entorno y Laravel tiene caché de configuración, límpiala con:

```bash
php artisan config:clear
```

## Crear las tablas

Con la base de datos creada y `.env` configurado, ejecuta:

```bash
php artisan migrate
```

Ejecuta este comando cuando prepares una instalación nueva o cuando existan migraciones pendientes. **No** lo utilices sobre una base existente sin verificar previamente su estado y su historial de migraciones (tabla `migrations`).

No uses comandos destructivos como `migrate:fresh`, `migrate:refresh` o `db:wipe` sobre bases con información.

## Configurar Apache (XAMPP)

La aplicación debe ejecutarse mediante **Apache de XAMPP** apuntando al directorio `public/` del proyecto. **No** se utiliza `php artisan serve` como método principal.

### 1. Requisitos de Apache

- Habilita el módulo **`mod_rewrite`**.
- Permite el uso de `.htaccess` en el directorio público (`AllowOverride All`).
- Conserva `public/.htaccess` (incluido en el proyecto).

### 2. Alias para la URL principal (recomendado)

Para conservar la dirección original `http://localhost/TrackerFinanzasPersonales/`, configura un **Alias** que apunte al directorio `public/` del proyecto (sin exponer la raíz del repositorio).

Configuración de referencia (ajusta la ruta real):

```apache
Alias /TrackerFinanzasPersonales "C:/xampp/htdocs/TrackerFinanzasPersonales/public"

<Directory "C:/xampp/htdocs/TrackerFinanzasPersonales/public">
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
    DirectoryIndex index.php
</Directory>
```

> **Nota:** `public/.htaccess` incluye `RewriteBase /TrackerFinanzasPersonales/` para el funcionamiento correcto de las rutas bajo ese subdirectorio. Si instalas el proyecto con otro nombre/ruta base, ajusta `RewriteBase` en `public/.htaccess` y el `Alias` en consecuencia.

**Dónde aplicarlo (XAMPP en Windows):**
1. Identifica el archivo de configuración cargado por Apache. En XAMPP suele ser `C:\xampp\apache\conf\extra\httpd-xampp.conf` (incluido desde `httpd.conf`). Antes de modificar, **haz una copia de seguridad** del archivo.
2. Comprueba si ya existe un `Alias /TrackerFinanzasPersonales` equivalente para evitar duplicados o conflictos.
3. Agrega o actualiza el bloque anterior apuntando a la ruta real de `.../public` (el Alias debe estar fuera de bloques incompatibles y el `<Directory>` debe referenciar la ruta física real).
4. Valida la sintaxis de Apache con:
   ```powershell
   C:\xampp\apache\bin\httpd.exe -t
   ```
5. Reinicia **Apache** desde el Panel de Control de XAMPP (botón **Stop** y luego **Start** en el módulo Apache) para que cargue la configuración. Comprueba que Apache quede en ejecución y sin errores en `C:\xampp\apache\logs\error.log`.
6. Abre en el navegador: `http://localhost/TrackerFinanzasPersonales/` (debe mostrarse el Login).

Si no puedes modificar la configuración global de Apache en tu equipo, solicita a quien administre XAMPP que incorpore el Alias anterior en el archivo de configuración correspondiente y reinicie Apache.

Si no puedes modificar la configuración global de Apache en tu equipo, solicita a quien administre XAMPP que incorpore el Alias anterior en el archivo de configuración correspondiente y reinicie Apache.

### 3. Virtual Host alternativo (opcional)

Como alternativa puede configurarse `http://sistema-financiero.test` mediante Virtual Host. Se incluye un ejemplo en `docs/apache/sistema-financiero.conf` (ajustar ruta, habilitar vhosts, agregar entrada en `hosts`, reiniciar Apache). Esta URL es **adicional** y no reemplaza la URL principal.

## Ejecutar el proyecto

1. Abrir XAMPP.
2. Iniciar **Apache**.
3. Iniciar **MySQL**.
4. Abrir la URL principal: `http://localhost/TrackerFinanzasPersonales/`.

Acceso de demostración (validación en frontend, sin autenticación real):

| Usuario | Contraseña |
| :------ | :--------- |
| `admin` | `admin123` |

## Estructura principal del proyecto

```text
├── app/                    # Código de aplicación (controladores/modelos en fases posteriores)
├── bootstrap/
├── config/
├── database/
│   ├── migrations/         # Migraciones del esquema aprobado
│   └── ...
├── db/
│   └── db.sql              # Esquema SQL original (referencia única)
├── docs/                   # Documentación y ejemplos de configuración (Apache)
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── styles.css          # Estilos compartidos (layout, componentes, utilidades)
│   │   │   └── pages/              # Estilos particulares por módulo
│   │   ├── js/
│   │   ├── img/
│   │   └── favicon/
│   └── index.php           # Punto de entrada de Laravel
├── resources/
│   └── views/              # Layouts, partials y vistas Blade
├── routes/
│   └── web.php             # Rutas GET de navegación
├── storage/
├── .env.example
├── artisan
├── composer.json
└── composer.lock
```

### Organización de estilos

- **`public/assets/css/styles.css`:** hoja general con estilos compartidos (variables, base, estructura, menú/navbar, componentes, formularios, tablas, responsive general, etc.).
- **`public/assets/css/pages/*.css`:** hojas particulares por módulo (`analisis.css`, `configuracion.css`, `dashboard.css`, `deudas.css`, `distribucion.css`, `educacion.css`, `metas.css`, `movimientos.css`, `presupuestos.css`).
- El layout (`resources/views/layouts/app.blade.php`) carga `styles.css` y expone `@stack('styles')`; cada vista de módulo añade su hoja específica con `@push('styles')`, por lo que cada página recibe la hoja general y su CSS particular. Login y Registro utilizan únicamente la hoja general. Los recursos se sirven desde `public/assets/` (sin Vite).

## Solución de errores comunes

- **Error 403 Forbidden:** revisa `C:\xampp\apache\logs\error.log` y `access.log` para la URL solicitada. Verifica que el Alias apunte a `.../public`, que el bloque `<Directory>` tenga `Require all granted`, `AllowOverride All` y la ruta física correcta, que `mod_alias`/`mod_rewrite` estén activos y que no haya reglas o Alias duplicados. Comprueba que la URL solicitada sea la correcta (p. ej. `http://localhost/TrackerFinanzasPersonales/`); solicitudes con caracteres no válidos en la ruta pueden generar 403 en Apache.
- **Error 404 en rutas de Laravel:** verifica que el Alias/Virtual Host apunte a `public/`, que `mod_rewrite` esté activo, que `AllowOverride All` esté configurado y que exista `public/.htaccess`. En subdirectorios, `public/.htaccess` debe incluir el `RewriteBase` adecuado (por defecto `/TrackerFinanzasPersonales/`).
- **Error 500:** revisa `storage/logs/laravel.log` y `C:\xampp\apache\logs\error.log`, permisos de escritura en `storage/` y `bootstrap/cache/`, y que `APP_KEY` esté generado (`php artisan key:generate`). Si el log de Apache indica "limit of 10 internal redirects", revisa `RewriteBase` y reglas de `.htaccess`.
- **Apache no inicia:** ejecuta `C:\xampp\apache\bin\httpd.exe -t` para ver errores de sintaxis, revisa `error.log`, conflictos de puerto 80 y cambios recientes en la configuración (restaura la copia de seguridad si es necesario).
- **MySQL no conecta:** confirma que MySQL esté iniciado, que la base `sistema_gestion_financiera` exista y que `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env` sean correctos.
- **Composer no reconoce PHP:** instala Composer y verifica que esté en el `PATH`. Comprueba con `composer --version` y `php -v`.
- **Recursos CSS/JS no cargan:** verifica que `public/assets/` exista, que las respuestas HTTP sean 200 y que `APP_URL`/acceso por la URL correcta coincidan con el subdirectorio configurado.
- **Problemas con `.htaccess` o `mod_rewrite`:** confirma que Apache permite sobreescrituras (`AllowOverride All`) y que el módulo rewrite está cargado; reinicia Apache tras cambios.
- **Configuración incorrecta del Alias:** evita exponer la raíz del proyecto; el Alias debe apuntar a `.../public`. Reinicia Apache tras cualquier cambio y valida con `httpd.exe -t`.
- **Permisos de `storage/` y `bootstrap/cache/`:** el usuario que ejecuta Apache/PHP debe poder escribir en estas carpetas.
- **Conflicto con base de datos existente:** si la base ya fue creada con `db/db.sql`, no borres ni reconstruyas automáticamente. Revisa si las tablas ya existen y evita ejecutar migraciones que intenten recrearlas sin un plan de transición.

## Actualizar el proyecto

Para obtener cambios del repositorio e instalar dependencias nuevas:

```bash
git pull
composer install
```

Si hay nuevas migraciones, ejecútalas con precaución y solo cuando corresponda a tu entorno:

```bash
php artisan migrate
```

## Documentación

La documentación original del proyecto se mantiene en `docs/` (planteamiento, requisitos, MER). El esquema SQL de referencia se conserva en `db/db.sql`.
