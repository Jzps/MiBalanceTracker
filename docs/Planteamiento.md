# Sistema de Gestión Financiera Personal

---

## 1. Descripción del Problema

Muchas personas presentan dificultades para administrar adecuadamente sus ingresos debido a la falta de organización y seguimiento de sus gastos cotidianos. La ausencia de un registro claro puede dificultar la identificación de cuánto dinero se utiliza, cuánto se puede destinar al ahorro y si se están cumpliendo los objetivos financieros.

Por esta razón, se propone desarrollar una aplicación web que centralice el registro de ingresos y gastos, el control de presupuestos, las metas de ahorro, las deudas y el análisis de la información financiera.

El sistema buscará que el usuario no solamente registre sus movimientos, sino que pueda visualizar, organizar y comprender su situación financiera mediante información presentada de manera clara.

---

## 2. Pregunta Problematizadora

¿Cómo puede el desarrollo de una aplicación web facilitar la organización y administración de las finanzas personales mediante el registro de ingresos y gastos, el establecimiento de presupuestos, el seguimiento de metas de ahorro y la visualización de información financiera?

---

## 3. Justificación

El proyecto resulta pertinente porque combina educación financiera y desarrollo de software, creando una herramienta práctica para organizar información que normalmente puede encontrarse dispersa o no registrarse. La aplicación utilizará el método 50/30/20 como una de sus herramientas de orientación, pero permitirá que el usuario configure su propia distribución financiera. Esta metodología propone destinar aproximadamente $50\%$ de los ingresos a necesidades, $30\%$ a deseos y $20\%$ al ahorro, aunque debe entenderse como una guía adaptable y no como una regla obligatoria.

---

## 4. Objetivo General

Desarrollar una aplicación web para la gestión de finanzas personales que permita a los usuarios registrar y organizar sus ingresos y gastos, establecer presupuestos, gestionar metas y fondos de emergencia, controlar deudas y visualizar información financiera que facilite la planificación y administración de su dinero.

---

## 5. Objetivos Específicos

- Diseñar una interfaz web intuitiva que permita al usuario acceder fácilmente a las diferentes funcionalidades de gestión financiera.
- Implementar un sistema de registro y autenticación de usuarios que permita almacenar y proteger la información financiera de cada usuario.
- Desarrollar un módulo de movimientos para registrar, consultar, editar y eliminar ingresos y gastos.
- Implementar categorías financieras que permitan clasificar los gastos según necesidades, deseos y otras categorías configurables.
- Desarrollar un módulo de presupuestos que permita establecer límites de gasto y visualizar el progreso de cada presupuesto.
- Implementar un módulo de metas y fondo de emergencia que permita establecer objetivos de ahorro y realizar seguimiento de su progreso.
- Desarrollar un módulo de gestión de deudas que permita registrar obligaciones pendientes y realizar seguimiento de los pagos realizados.
- Implementar un módulo de análisis financiero que presente estadísticas y representaciones gráficas basadas en la información registrada.
- Integrar el método 50/30/20 como una herramienta de orientación para distribuir los ingresos entre necesidades, deseos y ahorro.
- Incorporar mensajes de alerta estáticos y dinámicos basados en reglas predefinidas, como advertencias por aproximación o superación de presupuestos y mensajes relacionados con el progreso financiero.
- Incorporar contenido básico de educación financiera relacionado con presupuesto, ahorro, necesidades, deseos, deudas y planificación financiera.

---

## 6. Antecedentes

- **Antecedente 1: Aplicación web de finanzas personales (Universidad Politécnica de Madrid)**
  - _Descripción:_ Desarrollo de una plataforma web para el control de ingresos y gastos con categorías, reportes gráficos y documentación técnica completa (UPM).
  - _Relación:_ Aporta la base metodológica para centralizar movimientos y gráficos; nuestro proyecto amplía el alcance sumando presupuestos, metas, deudas y educación financiera.

- **Antecedente 2: Aplicación de gestión de gastos personales (Universidad Distrital)**
  - _Descripción:_ Prototipo funcional web bajo metodología iterativa para resolver el desorden en finanzas personales mediante categorías personalizadas (RedCol).
  - _Relación:_ Valida el uso de apps web para la organización financiera y resalta la necesidad de permitir categorías flexibles.

- **Antecedente 3: Cartera Futura — Educación financiera (Proyecto Colombiano)**
  - _Descripción:_ Aplicación web educativa enfocada en enseñar a estudiantes sobre ahorro, inversión y distribución del dinero (dinamarca.edu.co).
  - _Relación:_ Respalda directamente nuestra visión de trascender el simple registro de datos al integrar un módulo activo de educación financiera.

---

## 7. Marco Conceptual

- **Finanzas personales:** Conjunto de decisiones y actividades relacionadas con la administración de los recursos económicos de una persona, incluyendo ingresos, gastos, ahorro, deudas y planificación.
- **Ingreso:** Cantidad de dinero que recibe una persona durante un determinado período.
- **Gasto:** Cantidad de dinero utilizada para adquirir bienes, servicios o cubrir obligaciones.
- **Presupuesto:** Plan que permite organizar los ingresos disponibles y establecer límites para los diferentes gastos.
- **Ahorro:** Parte del dinero que se reserva para utilizarlo posteriormente, ya sea para afrontar imprevistos o alcanzar objetivos determinados.
- **Meta financiera:** Objetivo económico establecido por el usuario que requiere acumular una determinada cantidad de dinero durante un período.
- **Fondo de emergencia:** Cantidad de dinero reservada para afrontar situaciones inesperadas que puedan generar gastos o afectar temporalmente los ingresos.
- **Deuda:** Obligación económica que una persona tiene pendiente de pago.
- **Categoría financiera:** Clasificación utilizada para organizar los movimientos según su naturaleza, como alimentación, vivienda, transporte o entretenimiento.
- **Educación financiera:** Conjunto de conocimientos y herramientas que permiten comprender y gestionar de mejor manera los recursos económicos y tomar decisiones financieras informadas.
- **Regla 50/30/20:** Método orientativo de distribución del ingreso que propone asignar aproximadamente $50\%$ a necesidades, $30\%$ a deseos y $20\%$ a ahorro o determinados objetivos financieros. El Fondo Nacional del Ahorro también presenta esta distribución como una herramienta para organizar el presupuesto y relaciona el ahorro con metas y fondos para imprevistos.

---

## 8. Marco Teórico

### Marco Teórico 1 — Finanzas personales y administración del dinero

- **Nombre:** Finanzas personales y administración del dinero
- **Objetivo:** Comprender los principios básicos para organizar ingresos, gastos, presupuestos, ahorro y obligaciones financieras.
- **Resumen:**
  - _Identificación del problema:_ Desorganización en la gestión de recursos económicos y falta de control sobre la situación financiera cotidiana.
  - _Solución:_ Aplicar el concepto de bienestar financiero (CFPB) para administrar recursos, afrontar imprevistos y alcanzar metas.
  - _Implementación:_ Relacionar el registro de ingresos y gastos con presupuestos, deudas, metas y análisis dentro del sistema.
  - _Conclusión / análisis:_ Proporciona el marco conceptual que orienta las funciones de registro, planificación y consulta de la aplicación.

### Marco Teórico 2 — Presupuesto y método 50/30/20

- **Nombre:** Presupuesto personal y método 50/30/20
- **Objetivo:** Analizar una metodología sencilla de distribución de ingresos para utilizarla como herramienta en la aplicación.
- **Resumen:**
  - _Identificación del problema:_ Dificultad para planificar la distribución del dinero y equilibrar gastos obligatorios con ahorro.
  - _Solución:_ Utilizar la regla 50/30/20 ($50\%$ necesidades, $30\%$ deseos, $20\%$ ahorro) como guía de organización (FNA, BBVA).
  - _Implementación:_ Integrar la regla como un módulo de referencia configurable según la realidad de cada usuario.
  - _Conclusión / análisis:_ Sirve como una funcionalidad de orientación presupuestal flexible que el usuario puede adaptar libremente.

### Marco Teórico 3 — Desarrollo de aplicaciones web para gestión financiera

- **Nombre:** Aplicaciones web para la gestión de finanzas personales
- **Objetivo:** Analizar tecnologías web para almacenar, administrar y visualizar información financiera.
- **Resumen:**
  - _Identificación del problema:_ Necesidad de centralizar y consultar datos financieros de forma accesible desde cualquier dispositivo.
  - _Solución:_ Implementar un sistema web interactivo para procesar categorías, transacciones y estadísticas (UPM).
  - _Implementación:_ Utilización de HTML/CSS (interfaz), PHP (lógica de servidor), MySQL (base de datos) y XAMPP (entorno local).
  - _Conclusión / análisis:_ Aporta el sustento técnico para transformar la teoría financiera en un software funcional que gestiona usuarios y movimientos.

---

## 9. Metodología

La aplicación se construirá por etapas funcionales, comenzando por los componentes fundamentales y agregando progresivamente nuevas funcionalidades.

- **Etapa 1 — Análisis**
  - Definición del problema.
  - Identificación de usuarios.
  - Definición de funcionalidades.
  - Determinación del alcance.
- **Etapa 2 — Diseño**
  - Diseño de interfaz.
  - Diseño del Sidebar.
  - Diseño de pantallas.
  - Diseño de navegación.
  - Diseño de la base de datos.
- **Etapa 3 — Desarrollo**
  Se implementarán progresivamente:
  1. Registro e inicio de sesión.
  2. Configuración de ingreso inicial.
  3. Dashboard.
  4. Movimientos.
  5. Categorías.
  6. Presupuestos.
  7. Metas y fondo de emergencia.
  8. Deudas.
  9. Análisis.
  10. Educación financiera.
  11. 50/30/20.
  12. Configuración.
- **Etapa 4 — Integración**
  Se conectarán las funcionalidades para garantizar que los cambios realizados en una sección actualicen correctamente las demás.
  _Ejemplo:_ Registrar gasto $\rightarrow$ actualizar presupuesto $\rightarrow$ actualizar Dashboard $\rightarrow$ actualizar análisis.
- **Etapa 5 — Pruebas**
  Se probarán:
  - Registro de usuarios.
  - Inicio de sesión.
  - Registro de movimientos.
  - Cálculos.
  - Presupuestos.
  - Metas.
  - Deudas.
  - Gráficos.
  - Alertas.
  - Relaciones entre módulos.
- **Etapa 6 — Evaluación**
  Se verificará que la aplicación cumpla los objetivos definidos y que la interfaz sea comprensible para el usuario.

---

## 10. Recursos Necesarios

- **Hardware**
  - Computador.
  - Conexión a internet para investigación y documentación.
- **Software**
  - Visual Studio Code.
  - XAMPP.
  - Apache.
  - MySQL.
  - Navegador web.
- **Tecnologías**
  - HTML.
  - CSS.
  - JavaScript.
  - PHP.
  - MySQL.
- **Recursos de investigación**
  - Documentación sobre educación financiera.
  - Fuentes sobre ahorro y presupuestos.
  - Información sobre el método 50/30/20.
  - Trabajos académicos relacionados con aplicaciones web de finanzas personales.

---

## 11. Resultado Esperado

Se espera desarrollar una aplicación web funcional que permita a los usuarios organizar y consultar su información financiera personal de manera sencilla, mediante el registro de ingresos y gastos, creación de presupuestos, establecimiento de metas y fondo de emergencia, seguimiento de deudas y visualización de análisis financieros.

---

## 12. Referencias Bibliográficas (Normas APA)

- Consumer Financial Protection Bureau. (s. f.). _Why financial well-being?_ Consumer Financial Protection Bureau.
- Ye, B. (2024). _Aplicación Web para la Gestión de finanzas personales_ [Tesis de máster, Universidad Politécnica de Madrid]. Archivo Digital UPM.
- Vida según Richie. (s. f.). _A un paso de la quiebra: domine el dinero o viva en la miseria_ [Video]. YouTube.

---
