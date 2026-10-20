# TrackerFinanzasPersonales

Prototipo visual (V1) de un sistema de gestión de finanzas personales.
Tecnologías: **HTML, CSS, JavaScript nativo y PHP básico** sobre XAMPP/Apache.
En esta entrega **no hay base de datos**: el sistema es una maqueta funcional de la interfaz.

## Cómo ejecutarlo

1. Copia la carpeta en `C:\xampp\htdocs\`.
2. Inicia **Apache** desde el panel de control de XAMPP.
3. Abre <http://localhost/TrackerFinanzasPersonales/>.

La dirección raíz abre la pantalla de acceso. Credenciales de demostración:

| Usuario | Contraseña |
| :------- | :--------- |
| `admin`  | `admin123` |

> El acceso es una demostración académica: la comparación de credenciales se hace en el
> navegador (JavaScript), sin sesiones, sin contraseñas almacenadas y sin datos reales.

## Estructura

```text
TrackerFinanzasPersonales/
├── index.php              # enrutador: ?page=<slug> -> pages/<archivo>.html
├── login.php              # pantalla de acceso
├── registro.php           # formulario de registro (visual)
├── actions/               # lógica futura de cada módulo (reservada, sin implementar)
├── assets/
│   ├── css/styles.css     # hoja de estilos única
│   ├── favicon/favicon.ico
│   ├── img/logo.svg
│   └── js/app.js          # acceso al sistema y navegación
├── database/db.sql        # esquema previsto para la etapa de base de datos
├── docs/                  # documentación del proyecto
├── includes/              # componentes compartidos (header, navbar, footer, funciones)
└── pages/                 # los nueve módulos en HTML puro
```

## Módulos

Dashboard · Movimientos · Presupuestos · Metas y fondo de emergencia · Deudas ·
Análisis · Educación financiera · 50/30/20 · Configuración

La documentación del proyecto (planteamiento, requisitos, MER) está en `docs/`.
