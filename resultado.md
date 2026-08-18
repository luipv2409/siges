**UNIVERSIDAD LATINOAMERICANA – “ULAT”**

**FACULTAD DE CIENCIAS Y TECNOLOGÍA**

**CARRERA DE INGENIERÍA DE SISTEMAS**

**![](data:image/png;base64...)**

**“DESARROLLO DE UN SISTEMA DE GESTION DE EMPEÑOS SIGES”**

ASIGNATURA: INGENIERIA DE SOFTWARE.

SIGLA: INS 701.

SEMESTRE: SÉPTIMO.

DOCENTE: ING. MENDIETA NAVARRO LINETH.

ELABORADO POR: GUNNAR LUDWING PECHO VALLEJOS

ALEXANDER JHOEL RODRIGUEZ MEJIA

FECHA: 26 DE JUNIO DE 2026

COCHABAMBA - BOLIVIA

2026

Contenido

[**1. INTRODUCCIÓN** 3](#_Toc232924765)

[**2. OBJETO DE ESTUDIO** 4](#_Toc232924766)

[**3. RESULTADOS DE LA ENCUESTA** 5](#_Toc232924767)

[**4. OBJETIVOS** 8](#_Toc232924768)

[**5. CARACTERÍSTICAS DEL SOFTWARE** 10](#_Toc232924769)

[**6. REQUERIMIENTOS** 13](#_Toc232924770)

[**7. LISTA DE TAREAS** 19](#_Toc232924771)

[**8. APLICACIÓN DE LA METODOLOGÍA ÁGIL** 26](#_Toc232924772)

[**9. DIAGRAMAS UML** 32](#_Toc232924773)

[**10. CONCLUSIONES** 42](#_Toc232924774)

# **1. INTRODUCCIÓN**

El mundo de las casas de empeños siempre ha sido un negocio que requiere mucha organización y control. En estos establecimientos, la gente deja objetos de valor a cambio de un préstamo, y si no pagan a tiempo, esos objetos pueden ser vendidos en subastas. El problema es que muchos de estos negocios todavía funcionan con métodos manuales, usando libretas, hojas de cálculo o sistemas muy básicos que no integran todas las áreas del negocio.

Cuando visitamos varias casas de empeños en nuestra ciudad, nos dimos cuenta de que los empleados pasan mucho tiempo haciendo cálculos de intereses a mano, buscando prendas en el almacén, y tratando de recordar qué clientes tienen pagos atrasados. Esto no solo es lento, sino que también genera errores que pueden costar dinero al negocio.

Por eso decidimos desarrollar SIGES, un sistema completo que automatiza todos estos procesos. Con SIGES, los empleados pueden registrar clientes y prendas de forma rápida, el sistema calcula automáticamente los intereses según el tipo de contrato, y todo queda registrado digitalmente. También tiene un sistema de subastas para cuando los clientes no pagan, y un panel de control para que el dueño pueda ver cómo va el negocio en tiempo real.

Lo mejor de todo es que SIGES es una aplicación web progresiva (PWA), lo que significa que se puede instalar en el celular como una app normal, y funciona incluso sin internet. Esto lo hace perfecto para negocios que quizás no tienen una conexión estable todo el tiempo.

# **2. OBJETO DE ESTUDIO**

El objeto de estudio de nuestro proyecto es el proceso completo de gestión de una casa de empeños. Analizamos detalladamente cómo funcionan estos negocios para poder diseñar un sistema que cubra todas sus necesidades.

Específicamente, estudiamos:

**El proceso de empeño:** Desde que el cliente llega con un objeto hasta que firma el contrato y recibe el dinero. Esto incluye cómo se registra al cliente, cómo se valúa el objeto, cómo se calculan los intereses y cómo se genera el contrato legal.

**La gestión de inventario:** Cómo se almacenan y organizan las prendas en el local, qué sistema de ubicación usan para encontrarlas rápidamente, y cómo se controla el espacio disponible.

**El proceso de cobranza:** Cómo se llevan los registros de pagos, cómo se notifica a los clientes sobre vencimientos, y qué pasa cuando un cliente no paga a tiempo.

**El proceso de subastas:** Cómo se maneja la venta de prendas en subasta, cómo se reciben las ofertas, y cómo se realiza la liquidación final.

**Los reportes financieros:** Qué información necesita el dueño para tomar decisiones, como cuánto dinero tiene prestado, cuánto está ganando en intereses, y qué productos son los más rentables.

Entender estos procesos nos permitió diseñar un sistema que realmente resuelve los problemas del día a día en una casa de empeños.

# **3. RESULTADOS DE LA ENCUESTA**

Para conocer mejor las necesidades del negocio, realizamos una encuesta a dueños y empleados de casas de empeños. Los resultados más importantes fueron:

**3.1. Datos de los Encuestados**

| Aspecto | Resultado |
| --- | --- |
| **Total de encuestados** | 15 personas |
| **Cargos** | 4 dueños, 8 empleados, 3 clientes frecuentes |
| **Años de experiencia** | Promedio de 8 años en el rubro |
| **Tamaño del negocio** | 7 pequeñas (1-3 empleados), 5 medianas (4-10 empleados), 3 grandes (más de 10 empleados) |

**3.2. Problemáticas Identificadas**

| Problema | Porcentaje | Descripción |
| --- | --- | --- |
| **Cálculo manual de intereses** | 80% | Los empleados usan calculadora o papel para calcular intereses, lo que genera errores |
| **Pérdida de documentos** | 73% | Contratos y registros se pierden o dañan con el tiempo |
| **Inventario desordenado** | 67% | No saben exactamente dónde está cada prenda en el almacén |
| **Subastas improvisadas** | 60% | Las subastas se hacen sin un sistema formal, con pujas en papel |
| **Morosidad no controlada** | 53% | No hay un sistema que alerte automáticamente sobre pagos atrasados |
| **Falta de reportes** | 47% | El dueño no tiene información clara sobre el estado del negocio |

**3.3. Requerimientos Solicitados**

| Requerimiento | Porcentaje | Descripción |
| --- | --- | --- |
| **Registro digital de empeños** | 93% | Quieren dejar los papeles y tener todo digital |
| **Cálculo automático de intereses** | 87% | Que el sistema calcule todo automáticamente |
| **Alertas de vencimiento** | 80% | Que avise cuando un empeño está por vencer |
| **Gestión de subastas** | 73% | Un sistema para manejar subastas de forma ordenada |
| **Reportes financieros** | 67% | Poder ver cómo va el negocio en números |
| **Acceso desde celular** | 60% | Poder usar el sistema desde el teléfono |
| **Control de inventario** | 53% | Saber dónde está cada prenda |

**3.4. Principales Hallazgos**

Los encuestados nos contaron que los principales dolores de cabeza son:

1. **"Perdemos mucho tiempo buscando papeles"** - Los contratos en papel se extravían o dañan.
2. **"Los clientes se olvidan de pagar"** - No hay un sistema de recordatorios automáticos.
3. **"No sabemos cuánto estamos ganando realmente"** - Sin reportes claros, es difícil tomar decisiones.
4. **"Las subastas son un caos"** - Sin un sistema, las subastas se vuelven desordenadas.
5. **"Las prendas se pierden en el almacén"** - Sin un sistema de ubicación, encontrar una prenda toma mucho tiempo.

Estos hallazgos confirmaron que nuestro proyecto iba por buen camino y que realmente estábamos resolviendo problemas reales del negocio.

# **4. OBJETIVOS**

**4.1. Objetivo General**

Desarrollar un sistema de gestión integral para casas de empeños que automatice todos los procesos operativos y administrativos, permitiendo a los dueños y empleados trabajar de manera más eficiente y con menos errores.

El objetivo es que el sistema cubra desde el momento en que un cliente llega con un objeto, hasta el momento en que ese objeto es devuelto o vendido en subasta, pasando por todo el proceso intermedio: valuación, contrato, pagos, refrendos y liquidaciones.

**4.2. Objetivos Específicos**

1. **Diseñar e implementar un módulo de autenticación** que permita a dueños, empleados y clientes acceder al sistema con diferentes niveles de permiso, garantizando que cada persona vea solo lo que le corresponde.
2. **Desarrollar un módulo de empeños completo** que permita registrar clientes con todos sus datos (CI, nombre, teléfono, correo, dirección), registrar prendas con fotos y descripción detallada, calcular automáticamente los intereses según el tipo de contrato, y generar contratos digitales.
3. **Implementar un sistema de valuación inteligente** que ayude a los empleados a determinar el valor de las prendas usando precios de referencia del mercado y considerando la depreciación de los productos, especialmente los electrónicos.
4. **Crear un módulo de inventario y trazabilidad** que permita asignar ubicaciones físicas a las prendas (pasillo, estante, nivel) y mantener un historial de movimientos para saber siempre dónde está cada objeto.
5. **Desarrollar un motor de subastas en tiempo real** que permita gestionar subastas de prendas impagas, recibir pujas de los clientes, controlar los incrementos mínimos, y gestionar la liquidación final.
6. **Implementar paneles de control personalizados** para que el dueño vea indicadores financieros clave, el empleado vea sus tareas pendientes, y el cliente vea el estado de sus empeños.
7. **Añadir un sistema de notificaciones automáticas** que recuerde a los clientes sobre pagos próximos a vencer y avise cuando se realizan pujas en las subastas.
8. **Crear reportes financieros avanzados** con filtros por categoría, fecha, estado, y otras variables, para que el dueño pueda tomar decisiones informadas.

# **5. CARACTERÍSTICAS DEL SOFTWARE**

SIGES es un sistema con características técnicas y funcionales que lo hacen ideal para una casa de empeños:

**5.1. Características Técnicas**

| Característica | Descripción |
| --- | --- |
| **Aplicación Web Progresiva (PWA)** | Se puede instalar en el celular como una app y funciona sin internet |
| **Diseño Responsivo** | Se adapta a cualquier tamaño de pantalla: PC, tablet o celular |
| **Sin necesidad de servidor propio** | Funciona completamente en el navegador, sin costos de hosting |
| **Persistencia local** | Guarda los datos en el dispositivo usando localStorage |
| **Interfaz moderna** | Diseño limpio, intuitivo y fácil de usar |
| **Validación de datos** | Controla que la información ingresada sea correcta |
| **Rendimiento rápido** | Carga en menos de 2 segundos |

**5.2. Características Funcionales**

| Módulo | Características |
| --- | --- |
| **Autenticación** | Login con email y contraseña, 3 roles: Dueño, Empleado, Cliente, gestión de sesiones |
| **Empeños** | Registro de clientes con validación, captura de fotos con cámara, tipos de contrato (cuotas mensuales o pago único), cálculo automático de intereses, generación de contrato PDF |
| **Valuación** | Asistente de valuación con precios de referencia, depreciación automática, factor de condición |
| **Subastas** | Visualización en tiempo real, sistema de pujas, compra directa (+50%), anti-sniping (extensión de tiempo), pago instantáneo al ganar |
| **Inventario** | Mapa de almacén con ubicaciones, seguimiento de movimientos, alertas de vencimiento, control de ocupación |
| **Notificaciones** | Alertas de pagos próximos, avisos de subastas, recordatorios automáticos |
| **Reportes** | KPIs financieros, filtros por categoría y estado, exportación a CSV, gráficos interactivos |
| **Configuración** | Tasas de interés configurables, margen de compra directa, días de gracia para subastas |

**5.3. Roles y Permisos**

| Rol | Acceso |
| --- | --- |
| **Dueño (OWNER)** | Todo el sistema: reportes, configuraciones, empeños, subastas, inventario |
| **Empleado (EMPLOYEE)** | Empeños, valuación, subastas, inventario, notificaciones |
| **Cliente (CLIENT)** | Ver subastas, participar en subastas, ver sus empeños, su perfil |

**5.4. Ventajas Competitivas**

1. **Gratuito y sin costos de mantenimiento** - No necesita servidor ni licencias.
2. **Fácil de aprender** - Interfaz intuitiva que no requiere entrenamiento extenso.
3. **Accesible desde cualquier lugar** - Solo se necesita un navegador web.
4. **Offline** - Funciona sin internet, ideal para zonas con conexión irregular.
5. **Instalable** - Se instala como una app en el celular.
6. **Escalable** - Diseñado para crecer con el negocio.

# **6. REQUERIMIENTOS**

**6.1. Requerimientos Funcionales**

Los requerimientos funcionales describen qué debe hacer exactamente el sistema. Los organizamos por módulos:

**Módulo de Autenticación y Seguridad**

| ID | Nombre | Descripción |
| --- | --- | --- |
| RF-01 | Gestión de Roles | El sistema permite definir perfiles con permisos diferentes para Dueño, Empleado y Cliente |
| RF-02 | Autenticación | Los usuarios deben iniciar sesión con email y contraseña para acceder al sistema |
| RF-03 | Recuperación de contraseña | El sistema permite restablecer la contraseña mediante email (simulado) |
| RF-04 | Cierre de sesión | Los usuarios pueden cerrar sesión para proteger sus datos |

**Módulo de Empeños**

| ID | Nombre | Descripción |
| --- | --- | --- |
| RF-05 | Registro de cliente | Captura de datos del cliente con validación: CI, nombre, email, teléfono, dirección |
| RF-06 | Registro de prenda | Captura de datos de la prenda: nombre, categoría, descripción, fotos, valor |
| RF-07 | Captura de fotos | El sistema permite tomar fotos con la cámara o subir archivos, mínimo 3 fotos |
| RF-08 | Cálculo de intereses | Cálculo automático según el tipo de contrato y el plazo seleccionado |
| RF-09 | Tipos de contrato | Cuotas mensuales o pago único a plazo fijo |
| RF-10 | Generación de contrato | El sistema genera un contrato digital con todos los datos legales |
| RF-11 | Refrendo | Permite pagar intereses para extender el plazo del empeño |
| RF-12 | Desempeño | Permite pagar el monto total para recuperar la prenda |

**Módulo de Valuación**

| ID | Nombre | Descripción |
| --- | --- | --- |
| RF-13 | Asistente de valuación | Ayuda al empleado a determinar el valor de la prenda usando precios de referencia |
| RF-14 | Búsqueda de precios | Simula la búsqueda de precios en sitios de venta de productos usados |
| RF-15 | Depreciación | Aplica depreciación automática a productos electrónicos según su antigüedad |
| RF-16 | Factor de condición | Permite ajustar el valor según el estado del producto (Excelente, Bueno, Regular, Malo) |

**Módulo de Subastas**

| ID | Nombre | Descripción |
| --- | --- | --- |
| RF-17 | Conversión automática | Las prendas con 90+ días de impago pasan automáticamente a subasta |
| RF-18 | Visualización de subastas | Los clientes pueden ver las subastas activas sin necesidad de registrarse |
| RF-19 | Sistema de pujas | Los clientes registrados pueden hacer pujas en tiempo real |
| RF-20 | Compra directa | Los clientes pueden comprar directamente con un precio fijo (+50%) |
| RF-21 | Anti-sniping | Si hay una puja en el último minuto, el tiempo se extiende 2 minutos más |
| RF-22 | Liquidación | Al finalizar, el sistema reparte el monto según la regla 70/10/20 |

**Módulo de Inventario**

| ID | Nombre | Descripción |
| --- | --- | --- |
| RF-23 | Ubicación de prendas | Asignación de ubicación física: pasillo, estante, nivel |
| RF-24 | Mapa de almacén | Visualización gráfica de la ocupación del almacén |
| RF-25 | Alertas de vencimiento | Notifica sobre prendas próximas a vencer (3 días) |
| RF-26 | Historial de movimientos | Registra cada vez que una prenda cambia de ubicación |

**Módulo de Reportes**

| ID | Nombre | Descripción |
| --- | --- | --- |
| RF-27 | Dashboard de KPIs | Muestra indicadores clave: capital invertido, intereses, subastas activas |
| RF-28 | Filtros avanzados | Permite filtrar por categoría, estado, fechas, búsqueda por producto |
| RF-29 | Exportación de reportes | Permite descargar reportes en formato CSV |
| RF-30 | Configuración del sistema | Permite al dueño configurar tasas de interés y parámetros del sistema |

**6.2. Requerimientos No Funcionales**

Los requerimientos no funcionales describen cómo debe ser el sistema:

| ID | Descripción |
| --- | --- |
| RNF-01 | El sistema debe ser **responsivo** y verse bien en PC, tablet y celular |
| RNF-02 | El tiempo de carga de la página principal debe ser **menor a 3 segundos** |
| RNF-03 | Debe ser una **PWA** y permitir su instalación en dispositivos móviles |
| RNF-04 | La interfaz debe ser **intuitiva** y fácil de usar para personas no técnicas |
| RNF-05 | Debe manejar correctamente la **concurrencia** en las subastas (varios usuarios pujando) |
| RNF-06 | Los datos deben **persistir** en el dispositivo (modo offline) |
| RNF-07 | El código debe ser **modular** para facilitar su mantenimiento y extensión |
| RNF-08 | Las **validaciones** de datos deben ser estrictas para evitar errores |
| RNF-09 | El sistema debe ser **seguro** y no permitir accesos no autorizados |
| RNF-10 | Debe funcionar en navegadores **modernos**: Chrome, Edge, Firefox, Safari |

# **7. LISTA DE TAREAS**

Para organizar el desarrollo, dividimos el proyecto en tareas específicas y las asignamos a cada miembro del equipo:

**7.1. Tareas de Configuración Inicial**

| ID | Tarea | Descripción | Responsable | Prioridad |
| --- | --- | --- | --- | --- |
| T-01 | Configurar entorno de desarrollo | Instalar XAMPP, Visual Studio Code, Git | Ambos | Alta |
| T-02 | Crear repositorio Git | Inicializar repositorio y subir a GitHub | Estudiante 1 | Alta |
| T-03 | Estructura de carpetas | Crear la estructura de archivos del proyecto | Estudiante 2 | Alta |
| T-04 | Definir tecnologías | HTML5, CSS3, JavaScript, Bootstrap, Chart.js, jsPDF | Ambos | Alta |
| T-05 | Configurar PWA | Crear manifest.json y sw.js | Estudiante 1 | Media |

**7.2. Tareas del Módulo de Autenticación**

| ID | Tarea | Descripción | Responsable | Prioridad |
| --- | --- | --- | --- | --- |
| T-06 | Diseñar interfaz de login | Crear página de login con Bootstrap | Estudiante 2 | Alta |
| T-07 | Implementar autenticación | Lógica de login con JWT simulado | Estudiante 1 | Alta |
| T-08 | Implementar roles | Asignar permisos según rol (OWNER/EMPLOYEE/CLIENT) | Estudiante 1 | Alta |
| T-09 | Diseñar navbar | Barra de navegación con menú según rol | Estudiante 2 | Alta |
| T-10 | Implementar registro de usuarios | Formulario de registro para nuevos clientes | Estudiante 1 | Media |

**7.3. Tareas del Módulo de Empeños**

| ID | Tarea | Descripción | Responsable | Prioridad |
| --- | --- | --- | --- | --- |
| T-11 | Diseñar formulario de cliente | Paso 1: Datos del cliente con validaciones | Estudiante 2 | Alta |
| T-12 | Implementar validaciones | Validar CI, email, celular, dirección | Estudiante 1 | Alta |
| T-13 | Diseñar formulario de producto | Paso 2: Datos de la prenda | Estudiante 2 | Alta |
| T-14 | Implementar cámara | Captura de fotos desde cámara o archivo | Estudiante 1 | Media |
| T-15 | Implementar asistente de valuación | Búsqueda de precios de referencia | Estudiante 1 | Media |
| T-16 | Diseñar formulario de contrato | Paso 3: Tipo de contrato y cálculo de intereses | Estudiante 2 | Alta |
| T-17 | Implementar cálculo de intereses | Intereses progresivos según plazo | Estudiante 1 | Alta |
| T-18 | Generar contrato PDF | Crear PDF con datos del empeño | Estudiante 2 | Media |

**7.4. Tareas del Módulo de Subastas**

| ID | Tarea | Descripción | Responsable | Prioridad |
| --- | --- | --- | --- | --- |
| T-19 | Diseñar vista de subastas | Listado de subastas activas con imágenes | Estudiante 2 | Alta |
| T-20 | Implementar lógica de pujas | Sistema de pujas con validaciones | Estudiante 1 | Alta |
| T-21 | Implementar compra directa | Compra con precio fijo (+50%) | Estudiante 1 | Media |
| T-22 | Implementar anti-sniping | Extensión de tiempo en último minuto | Estudiante 1 | Media |
| T-23 | Implementar temporizador | Cuenta regresiva en tiempo real | Estudiante 2 | Media |
| T-24 | Implementar liquidación | Reparto 70/10/20 al finalizar subasta | Estudiante 1 | Media |

**7.5. Tareas del Módulo de Inventario**

| ID | Tarea | Descripción | Responsable | Prioridad |
| --- | --- | --- | --- | --- |
| T-25 | Diseñar vista de inventario | Listado de prendas con ubicaciones | Estudiante 2 | Alta |
| T-26 | Implementar mapa de almacén | Visualización gráfica de ocupación | Estudiante 2 | Media |
| T-27 | Implementar alertas de vencimiento | Notificaciones de prendas próximas a vencer | Estudiante 1 | Media |
| T-28 | Implementar historial de movimientos | Registro de cambios de ubicación | Estudiante 1 | Baja |

**7.6. Tareas de Reportes y Dashboards**

| ID | Tarea | Descripción | Responsable | Prioridad |
| --- | --- | --- | --- | --- |
| T-29 | Diseñar dashboard del dueño | KPIs financieros y gráficos | Estudiante 2 | Alta |
| T-30 | Implementar gráficos | Gráficos con Chart.js | Estudiante 2 | Media |
| T-31 | Implementar filtros | Filtros por categoría, estado, fechas | Estudiante 1 | Media |
| T-32 | Implementar exportación | Exportar reportes a CSV | Estudiante 1 | Baja |
| T-33 | Implementar configuración | Tasas de interés y parámetros | Estudiante 1 | Media |

**7.7. Tareas de Pruebas y Documentación**

| ID | Tarea | Descripción | Responsable | Prioridad |
| --- | --- | --- | --- | --- |
| T-34 | Pruebas unitarias | Probar cada módulo individualmente | Ambos | Alta |
| T-35 | Pruebas de integración | Probar la interacción entre módulos | Ambos | Alta |
| T-36 | Pruebas de usabilidad | Verificar que la interfaz es intuitiva | Ambos | Media |
| T-37 | Documentación técnica | Explicar el código y la arquitectura | Estudiante 2 | Media |
| T-38 | Manual de usuario | Guía para usar el sistema | Estudiante 1 | Media |

# **8. APLICACIÓN DE LA METODOLOGÍA ÁGIL**

Para el desarrollo de SIGES utilizamos una adaptación de la metodología ágil **Scrum**, que es ideal para equipos pequeños como el nuestro (2 personas). A continuación, explico cómo aplicamos cada uno de los elementos de esta metodología.

**8.1. ¿Por qué Scrum?**

Elegimos Scrum por las siguientes razones:

1. **Es flexible**: Nos permitió adaptar los requerimientos a medida que avanzábamos en el proyecto.
2. **Es iterativo**: Trabajamos en ciclos cortos (sprints de 2 semanas) y fuimos entregando funcionalidades funcionales desde el principio.
3. **Es colaborativo**: Nos obligó a comunicarnos constantemente y a coordinar nuestro trabajo.
4. **Requiere documentación mínima**: No tuvimos que escribir documentos largos, lo que nos permitió enfocarnos en programar.
5. **Nos dio visibilidad**: Al final de cada sprint, podíamos ver qué habíamos logrado y qué faltaba.

**8.2. Roles que Asumimos**

Como solo éramos dos personas, tuvimos que ser flexibles con los roles:

| Rol | Persona | Responsabilidades |
| --- | --- | --- |
| **Product Owner** | Estudiante 1 | Definir qué funcionalidades eran prioritarias, mantener el backlog ordenado, validar que el software cumplía con lo esperado |
| **Scrum Master** | Estudiante 2 | Facilitar las reuniones, eliminar obstáculos, asegurar que siguiéramos el proceso |
| **Equipo de Desarrollo** | Ambos | Implementar las funcionalidades, probar el código, resolver problemas técnicos |

**8.3. Nuestros Sprints**

Dividimos el proyecto en 6 sprints de 2 semanas cada uno:

**Sprint 0 - "Configuración Inicial" (Semana 1-2)**

* Configuramos el entorno de desarrollo (XAMPP, Visual Studio Code, Git)
* Creamos la estructura de carpetas del proyecto
* Definimos las tecnologías a utilizar
* Configuramos el repositorio en GitHub

**Sprint 1 - "Autenticación y Seguridad" (Semana 3-4)**

* Implementamos el login con email y contraseña
* Creamos los roles (Dueño, Empleado, Cliente)
* Diseñamos la barra de navegación según el rol
* Implementamos el registro de nuevos usuarios

**Sprint 2 - "Módulo de Empeños" (Semana 5-6)**

* Diseñamos el formulario de registro de clientes con validaciones
* Implementamos la captura de fotos con cámara y archivos
* Creamos el asistente de valuación con precios de referencia
* Implementamos el cálculo de intereses según el tipo de contrato
* Generamos contratos PDF

**Sprint 3 - "Valuación y Depreciación" (Semana 7-8)**

* Mejoramos el sistema de valuación
* Implementamos la depreciación automática para productos electrónicos
* Añadimos el factor de condición (Excelente, Bueno, Regular, Malo)

**Sprint 4 - "Inventario y Trazabilidad" (Semana 9-10)**

* Creamos la vista de inventario con ubicación de prendas
* Implementamos el mapa de almacén
* Añadimos alertas de vencimiento

**Sprint 5 - "Motor de Subastas" (Semana 11-12)**

* Desarrollamos el sistema de subastas en tiempo real
* Implementamos el sistema de pujas
* Añadimos la compra directa (+50%)
* Implementamos anti-sniping y temporizador

**Sprint 6 - "Reportes y Optimización" (Semana 13-14)**

* Creamos el dashboard del dueño con gráficos
* Implementamos filtros avanzados
* Añadimos la exportación de reportes
* Configuramos el sistema (tasas de interés, parámetros)

**8.4. Cómo Organizábamos el Trabajo**

**Daily Standup (15 minutos diarios)**

Todas las mañanas nos reuníamos (presencialmente o por videollamada) para responder tres preguntas:

* ¿Qué hice ayer?
* ¿Qué voy a hacer hoy?
* ¿Hay algún problema que me impida avanzar?

**Sprint Planning (1 hora al inicio de cada sprint)**

Al principio de cada sprint, planificábamos qué tareas íbamos a hacer. El Product Owner (Estudiante 1) explicaba qué funcionalidades eran más importantes, y entre los dos decidíamos cuánto podíamos abarcar.

**Sprint Review (al final de cada sprint)**

Mostrábamos lo que habíamos logrado, probábamos las funcionalidades nuevas y recibíamos retroalimentación.

**Sprint Retrospective (al final de cada sprint)**

Reflexionábamos sobre lo que habíamos hecho bien y lo que podíamos mejorar para el siguiente sprint.

**8.5. Herramientas que Usamos para la Gestión**

| Herramienta | Para qué la usamos |
| --- | --- |
| **Jira** | Para gestionar nuestro backlog, crear tareas y hacer seguimiento del progreso |
| **Git** | Para controlar las versiones de nuestro código |
| **GitHub** | Para almacenar el código en la nube y colaborar |
| **GitFlow** | Para organizar nuestras ramas: main (producción), develop (desarrollo), feature/\* (nuevas funcionalidades) |

**8.6. Lo que Aprendimos con Scrum**

Usar Scrum nos enseñó varias cosas importantes:

1. **La planificación es clave**: Aunque sea ágil, tener un plan claro nos ayudó a no perder el rumbo.
2. **Hay que ser realistas**: A veces queríamos abarcar demasiado en un sprint y no lográbamos todo.
3. **La comunicación es fundamental**: Al ser solo dos, era fácil coordinarnos, pero igual era importante hablar a diario.
4. **Los sprints nos dieron ritmo**: Saber que teníamos que entregar algo cada 2 semanas nos mantuvo enfocados.
5. **La retroalimentación temprana nos salvó**: Mostrar el software al "cliente" (en nuestro caso, el docente) nos ayudó a corregir errores a tiempo.

# **9. DIAGRAMAS UML**

Para diseñar y documentar el sistema, creamos varios diagramas UML que nos ayudaron a visualizar la estructura y el comportamiento del software. Cada diagrama tiene un propósito específico:

**9.1. Diagrama de Casos de Uso**

**¿Qué muestra?**

El diagrama de casos de uso muestra las interacciones entre los actores (Dueño, Empleado, Cliente, Sistema Automático) y las funcionalidades del sistema.

**¿Por qué es importante?**

Nos ayudó a definir qué funcionalidades debía tener el sistema y quién podía usarlas.

![](data:image/png;base64...)

**9.2. Diagrama de Clases**

**¿Qué muestra?**

El diagrama de clases muestra la estructura estática del sistema: las clases principales, sus atributos y sus relaciones.

**¿Por qué es importante?**

Fue la base para diseñar la arquitectura del código y entender cómo se relacionaban los datos entre sí.

![](data:image/png;base64...)

**9.3. Diagrama de Secuencia**

**¿Qué muestra?**

El diagrama de secuencia muestra la interacción entre los componentes a lo largo del tiempo para un escenario específico. En nuestro caso, el flujo de registro de un empeño.

**¿Por qué es importante?**

Nos ayudó a entender el flujo de información entre las capas del sistema (presentación, lógica, datos) y a detectar posibles problemas antes de programar.

![](data:image/png;base64...)

**9.4. Diagrama de Componentes**

**¿Qué muestra?**

El diagrama de componentes muestra la organización física del código fuente, agrupando los archivos por su responsabilidad.

**¿Por qué es importante?**

Nos permitió organizar el proyecto de forma modular, facilitando el mantenimiento y la extensión del sistema.

![](data:image/png;base64...)

**9.5. Diagrama de Despliegue**

**¿Qué muestra?**

El diagrama de despliegue muestra la distribución física del sistema en los diferentes dispositivos (cliente, servidor web) y cómo se comunican.

**¿Por qué es importante?**

Nos ayudó a entender cómo iba a funcionar el sistema en el entorno real (local y en producción).

![](data:image/png;base64...)

**9.6. Diagrama de Estados**

**¿Qué muestra?**

El diagrama de estados muestra el ciclo de vida de una prenda desde que entra al sistema hasta que sale. Los estados incluyen: Registrada, En Custodia, Activa, Refrendada, Mora, En Subasta, Vendida, Desempeñada.

**¿Por qué es importante?**

Fue fundamental para entender cómo debía comportarse el sistema en cada etapa del proceso de empeño, especialmente para la automatización de subastas por impago.

![](data:image/png;base64...)

# **10. CONCLUSIONES**

**10.1. Logro de los Objetivos**

**Objetivo General:**

Logramos desarrollar un sistema de gestión integral para casas de empeños que automatiza todos los procesos operativos. SIGES funciona correctamente y cubre desde el registro de clientes hasta la gestión de subastas y la generación de reportes.

**Objetivos Específicos:**

1. Diseñamos e implementamos un módulo de autenticación con tres roles y gestión de sesiones.
2. Desarrollamos un módulo de empeños completo con registro de clientes, prendas, cálculo de intereses y generación de contratos.
3. Implementamos un sistema de valuación con asistente de precios y depreciación automática.
4. Creamos un módulo de inventario con ubicación de prendas y alertas de vencimiento.
5. Desarrollamos un motor de subastas en tiempo real con pujas, compra directa y anti-sniping.
6. Implementamos paneles de control personalizados para cada rol.
7. Añadimos un sistema de notificaciones automáticas.
8. Creamos reportes financieros avanzados con filtros y exportación.

**10.2. Qué Aprendimos con este Proyecto**

**Sobre Ingeniería de Software:**

Aprendimos que un buen proyecto de software no es solo escribir código, sino planificar, analizar requerimientos, diseñar una arquitectura adecuada y probar constantemente. También aprendimos que la documentación es importante, incluso para un proyecto pequeño.

**Sobre la Metodología Ágil:**

Entendimos que Scrum no es solo una teoría, sino una herramienta práctica que realmente ayuda a organizar el trabajo. Los sprints nos dieron estructura, las reuniones diarias nos mantuvieron alineados, y las retrospectivas nos permitieron mejorar continuamente.

**Sobre Desarrollo Web:**

Mejoramos nuestras habilidades en HTML5, CSS3 y JavaScript. Aprendimos a usar Bootstrap para diseño responsivo, Chart.js para gráficos, y jsPDF para generar contratos. También entendimos cómo funciona una PWA y cómo implementar un Service Worker.

**Sobre Trabajo en Equipo:**

Aunque éramos solo dos, aprendimos a coordinarnos, a comunicarnos efectivamente, a resolver conflictos y a apoyarnos mutuamente. También aprendimos a usar herramientas de colaboración como Git y Jira.

**10.3. Reflexión sobre la Tecnología Elegida**

Elegir JavaScript con HTML5 y CSS3 fue la decisión correcta. Nos permitió desarrollar rápido, el código es fácil de entender, y la aplicación funciona en cualquier navegador moderno. Bootstrap nos ahorró mucho tiempo de diseño, y las librerías externas (Chart.js, jsPDF) nos dieron funcionalidades avanzadas sin tener que programarlas desde cero.

La decisión de hacerlo como PWA fue acertada. La aplicación se puede instalar en el celular, funciona sin internet, y la experiencia de usuario es similar a una aplicación nativa.

**10.4. Limitaciones que Encontramos**

A pesar de los buenos resultados, el sistema tiene algunas limitaciones:

1. **Persistencia local**: Como usamos localStorage, los datos se pierden si el usuario limpia el navegador. En un sistema real, usaríamos una base de datos en la nube.
2. **Simulación de pagos**: Los pagos son simulados, no hay integración con bancos reales.
3. **Simulación de scraping**: Los precios de referencia son simulados, no se conectan a sitios reales.
4. **Sin autenticación real**: Las contraseñas no están encriptadas realmente (solo almacenadas en localStorage).
5. **Sin notificaciones reales**: Las notificaciones son internas, no se envían por WhatsApp o email.

**10.5. Recomendaciones para Futuros Proyectos**

Si alguien quisiera continuar con este proyecto o hacer algo similar, recomendaríamos:

1. **Usar una base de datos real** como Firebase o Supabase para persistencia en la nube.
2. **Implementar un backend** con Node.js/Express para manejar la lógica del lado del servidor.
3. **Integrar pagos reales** con alguna pasarela de pagos local.
4. **Usar web scraping real** para obtener precios actualizados del mercado.
5. **Integrar notificaciones reales** con WhatsApp Business API o Firebase Cloud Messaging.
6. **Añadir más funcionalidades** como gestión de empleados, múltiples sucursales, etc.

**10.6. Reflexión Final**

Este proyecto fue una experiencia muy valiosa. No solo aplicamos los conceptos teóricos de Ingeniería de Software, sino que también enfrentamos desafíos reales: desde decidir qué funcionalidades incluir hasta resolver problemas técnicos difíciles. Aprendimos a trabajar bajo presión, a cumplir plazos y a entregar un producto de calidad.

SIGES es un sistema completo y funcional que realmente podría usarse en una casa de empeños. Aunque tiene limitaciones, demuestra que, con las herramientas adecuadas y una buena metodología, dos estudiantes pueden desarrollar un software profesional.

Estamos orgullosos de lo que logramos y esperamos que este proyecto sea útil para otros estudiantes que quieran aprender sobre desarrollo de software y metodologías ágiles.
