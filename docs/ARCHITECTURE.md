# Arquitectura del Sistema — Iglesia del Nazareno Bagua

## Stack Tecnológico

| Componente   | Tecnología                        |
|--------------|-----------------------------------|
| Lenguaje     | PHP 8.x                           |
| Base de datos| MySQL (PDO, FETCH_ASSOC)          |
| Autoloading  | Composer PSR-4 (optimizado)       |
| Servidor     | Apache / XAMPP                    |
| Push en vivo | Pusher PHP Server ^7.2            |
| Patrón base  | MVC + DAO + Core Framework propio |

---

## Estructura de Directorios

```
IglesiaDelNazarenoBagua/
│
├── public/                         ← Único punto de entrada web
│   └── index.php                   ← Front controller (routing + autoload)
│
├── aplicacion/
│   ├── config/
│   │   └── Conexion.php            ← Singleton PDO
│   │
│   ├── core/                       ← Framework propio (Fase 2)
│   │   ├── Model.php               ← Base abstracta: all/find/where/create/update/delete
│   │   ├── QueryBuilder.php        ← Builder encadenable: table→where→get
│   │   ├── Validator.php           ← Validación: required|min|max|email|in
│   │   ├── Response.php            ← JSON helpers: success/created/error/notFound/unprocessable
│   │   ├── Middleware.php          ← Auth (web+API) + CSRF
│   │   ├── Router.php              ← Rutas {param} → controlador::método
│   │   └── Autoload.php            ← [LEGADO] spl_autoload_register — mantenido por compatibilidad
│   │
│   ├── modelos/                    ← Value Objects (getters/setters)
│   │   ├── Recurso.php             ← extends Model ($tabla = 'recursos')
│   │   ├── UserLogin.php           ← Modelo de usuario para registro
│   │   ├── Miembro.php
│   │   ├── GrupoDiscipulado.php
│   │   ├── TransmisionModelo.php
│   │   ├── VisitaModelo.php
│   │   ├── Cargo.php
│   │   └── Rol.php
│   │
│   ├── dao/                        ← Data Access Objects (SQL directo con PDO)
│   │   ├── RecursoDAO.php          ← CRUD complejo: thumbnails, papelera, archivos
│   │   ├── UserDAO.php
│   │   ├── InicioDAO.php
│   │   ├── MiembroDAO.php
│   │   ├── GrupoDAO.php
│   │   ├── DiscipuladoDAO.php
│   │   ├── NoticiaDAO.php
│   │   ├── TransmisionDAO.php
│   │   └── VisitaDAO.php
│   │
│   ├── controladores/
│   │   ├── AuthController.php      ← login / logout / registrar (Fase 4)
│   │   ├── RegistroController.php
│   │   ├── RecursoController.php   ← Acciones web (formularios, descargas)
│   │   ├── VisitaController.php
│   │   ├── MiembroController.php
│   │   ├── GrupoController.php
│   │   ├── DiscipuladoController.php
│   │   ├── NoticiaController.php
│   │   ├── TransmisionController.php
│   │   └── api/
│   │       └── RecursoApiController.php  ← REST: GET/POST/PUT/DELETE (Fase 3)
│   │
│   └── vistas/
│       ├── admin/                  ← Dashboard admin (autenticado)
│       └── web/                    ← Vistas públicas
│
├── procesos/                       ← Entry points delgados (wrappers 2 líneas)
│   ├── auth/
│   │   ├── procesar_login.php      → AuthController::login()
│   │   └── logout.php             → AuthController::logout()
│   └── registros/
│       └── procesar_reguistro.php → AuthController::registrar()
│
├── vendor/                         ← Composer (PSR-4 classmap optimizado, 179 clases)
├── composer.json                   ← PSR-4 declarado + pusher/pusher-php-server
└── docs/
    ├── ARCHITECTURE.md             ← Este archivo
    ├── JUSTIFICACION_TECNICA.md    ← Justificación por criterio de rúbrica
    └── CHANGELOG.md                ← Walkthrough antes/después de cada cambio
```

---

## Flujo de una Petición Web Normal

```
Navegador  →  Apache  →  public/index.php
                               │
                               ├─ require vendor/autoload.php   (PSR-4 Composer)
                               │
                               ├─ ¿URI empieza con /api/?
                               │       └─ SÍ → Router::dispatch() → ApiController → Response::json → exit
                               │
                               ├─ ¿$vista === 'procesar_login'?
                               │       └─ include procesos/auth/procesar_login.php
                               │                   └─ AuthController::login()
                               │
                               ├─ ¿$vista empieza con 'admin/'?
                               │       └─ include aplicacion/vistas/admin/dashboard.php
                               │                   └─ Middleware::auth() → contenidos/sección.php
                               │
                               └─ Si no → include aplicacion/vistas/web/$vista.php
```

---

## Flujo de una Petición API REST

```
curl -X POST /api/recursos  →  public/index.php
                                    │
                                    └─ Router detecta POST /api/recursos
                                            │
                                            └─ RecursoApiController::store()
                                                    │
                                                    ├─ Middleware::apiAuth()     → 401 si no hay sesión
                                                    ├─ Validator::make($_POST)   → 422 si campos inválidos
                                                    ├─ Recurso::create([...])    → QueryBuilder → PDO INSERT
                                                    └─ Response::created($row)   → HTTP 201 + JSON + exit
```

---

## Diagrama de Capas

```
┌─────────────────────────────────────────────────┐
│                   PRESENTACIÓN                   │
│   Vistas PHP (web/) · Dashboard (admin/)         │
│   JSON responses (Response::*)                   │
└───────────────────┬─────────────────────────────┘
                    │
┌───────────────────▼─────────────────────────────┐
│                  CONTROLADORES                   │
│   AuthController · RecursoController             │
│   RecursoApiController · VisitaController…       │
│   (usan Middleware, Validator, Response)         │
└───────────────────┬─────────────────────────────┘
                    │
┌───────────────────▼─────────────────────────────┐
│              CAPA DE DATOS (CORE)                │
│   Model (abstracto) → QueryBuilder → PDO         │
│   DAOs (lógica compleja: thumbnails, papelera)   │
└───────────────────┬─────────────────────────────┘
                    │
┌───────────────────▼─────────────────────────────┐
│                BASE DE DATOS                     │
│   MySQL · iglesiadelnazareno                     │
│   Tablas: recursos · usuarios · miembros…        │
└─────────────────────────────────────────────────┘
```

---

## Namespaces PSR-4

| Namespace                          | Directorio                          |
|------------------------------------|-------------------------------------|
| `aplicacion\config`                | `aplicacion/config/`                |
| `aplicacion\core`                  | `aplicacion/core/`                  |
| `aplicacion\modelos`               | `aplicacion/modelos/`               |
| `aplicacion\dao`                   | `aplicacion/dao/`                   |
| `aplicacion\controladores`         | `aplicacion/controladores/`         |
| `aplicacion\controladores\api`     | `aplicacion/controladores/api/`     |
