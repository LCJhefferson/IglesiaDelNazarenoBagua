# Changelog — Walkthrough Antes/Después
**Proyecto:** Iglesia del Nazareno Bagua  
**Periodo:** Implementación P2.1 — Desarrollo de Aplicaciones Web II

---

## FASE 1 — PSR-4 Project-Wide

### 1.1 `composer.json` — Declaración PSR-4

**Antes:**
```json
{
    "require": {
        "pusher/pusher-php-server": "^7.2"
    }
}
```

**Después:**
```json
{
    "require": {
        "pusher/pusher-php-server": "^7.2"
    },
    "autoload": {
        "psr-4": {
            "aplicacion\\": "aplicacion/"
        }
    }
}
```

**Impacto:** Composer ahora es la autoridad del autoloading. Se ejecutó `php composer.phar dump-autoload -o` (classmap optimizado de 179 clases).

---

### 1.2 `aplicacion/modelos/userLogin.php` → `UserLogin.php`

**Antes:** Archivo `userLogin.php`, clase `userLogin` (violación PSR-1: no StudlyCaps).

**Después:** Archivo `UserLogin.php`, clase `UserLogin`. Renombrado con `git mv` en dos pasos para forzar el cambio de mayúscula en Windows (filesystem case-insensitive).

```php
// ANTES
class userLogin { ... }

// DESPUÉS
class UserLogin { ... }
```

---

### 1.3 `aplicacion/controladores/RegistroController.php` — Referencias actualizadas

**Antes:**
```php
use aplicacion\modelos\userLogin;
use aplicacion\dao\userDAO;

$this->userDAO = new userDAO();
$user = new userLogin($username, $password, $id_rol, $estado);
```

**Después:**
```php
use aplicacion\modelos\UserLogin;
use aplicacion\dao\UserDAO;

$this->userDAO = new UserDAO();
$user = new UserLogin($username, $password, $id_rol, $estado);
```

---

### 1.4 `aplicacion/dao/UserDAO.php` — Type hint actualizado

**Antes:**
```php
use aplicacion\modelos\userLogin;
// ...
public function registrar(userLogin $userLogin): bool { ... }
```

**Después:**
```php
use aplicacion\modelos\UserLogin;
// ...
public function registrar(UserLogin $userLogin): bool { ... }
```

---

### 1.5 Rutas de autoload en archivos de entrada

Todos los archivos que tenían `require_once` al autoloader artesanal fueron actualizados a `vendor/autoload.php`:

| Archivo | Ruta anterior | Ruta nueva |
|---------|--------------|------------|
| `public/index.php` | `aplicacion/core/Autoload.php` | `vendor/autoload.php` |
| `procesos/auth/procesar_login.php` | (ya correcto) | `vendor/autoload.php` |
| `aplicacion/vistas/admin/dashboard.php` | (ya correcto) | `vendor/autoload.php` |
| `aplicacion/vistas/web/procesos/get_live_status.php` | (ya correcto) | `vendor/autoload.php` |

---

## FASE 2 — Core Framework (6 archivos nuevos)

### 2.1 `aplicacion/core/Response.php` — Helper JSON centralizado

**Antes:** Cada controlador/endpoint construía la respuesta JSON de forma diferente:
```php
// En get_live_status.php
header('Content-Type: application/json');
echo json_encode(["is_live" => true, ...]);
// Sin código HTTP explícito, sin estructura consistente
```

**Después:** Respuestas uniformes con códigos HTTP correctos:
```php
Response::success($data);           // 200 + {"success":true,"data":{...}}
Response::created($data);           // 201
Response::error("mensaje", 400);    // 400 + {"success":false,"message":"..."}
Response::notFound("No existe");    // 404
Response::unprocessable($errors);   // 422 + {"success":false,"errors":{...}}
```

---

### 2.2 `aplicacion/core/QueryBuilder.php` — Builder encadenable

**Antes:** SQL escrito a mano en cada DAO:
```php
// En InicioDAO.php — repetido en cada DAO
$this->con->query("SELECT COUNT(*) FROM miembros")->fetchColumn();
```

**Después:** Builder fluido reutilizable:
```php
(new QueryBuilder())
    ->table('recursos')
    ->where('tipo', '=', 'pdf')
    ->orderBy('fecha_creacion', 'DESC')
    ->limit(5)
    ->get();
```

Métodos: `table`, `select`, `where`, `orderBy`, `limit`, `get`, `first`, `insert`, `update`, `delete`.

---

### 2.3 `aplicacion/core/Model.php` — Base abstracta Active Record

**Antes:** No existía base común. Cada DAO repetía la inicialización PDO y el SQL básico.

**Después:** Subclases heredan CRUD completo declarando solo su tabla:
```php
class Recurso extends Model {
    protected static string $tabla = 'recursos';
}

// Sin escribir SQL:
Recurso::all();
Recurso::find(3);
Recurso::where('categoria', '=', 'predica')->get();
Recurso::create(['titulo' => '...', 'tipo' => 'pdf', ...]);
Recurso::update(['titulo' => 'Nuevo'], ['id' => 3]);
Recurso::delete(['id' => 3]);
```

---

### 2.4 `aplicacion/core/Validator.php` — Validación de inputs

**Antes:** Validaciones dispersas o ausentes:
```php
// En RecursoController.php
$datos = [
    'titulo' => trim($_POST['titulo'] ?? ''),  // Sin validar longitud ni obligatoriedad
    ...
];
```

**Después:** Validación declarativa con errores estructurados:
```php
$v = Validator::make($_POST, [
    'titulo'    => 'required|max:200',
    'categoria' => 'required|max:100',
    'tipo'      => 'required|in:pdf,img,vid,doc,yt',
]);

if ($v->fails()) {
    Response::unprocessable($v->errors());  // HTTP 422
}
```

Reglas: `required`, `min:N`, `max:N`, `email`, `in:a,b,c`.

---

### 2.5 `aplicacion/core/Middleware.php` — Auth y CSRF centralizados

**Antes:** Control de acceso repetido en cada vista admin:
```php
// En dashboard.php
if (!isset($_SESSION['usuario'])) {
    header("Location: /IglesiaDelNazarenoBagua/login");
    exit;
}
if (!in_array($_SESSION['rol_id'], [1, 2])) { ... }
```

**Después:** Una sola llamada:
```php
// Para vistas HTML
Middleware::auth([1, 2]);

// Para endpoints API (responde JSON en vez de redirigir)
Middleware::apiAuth([1, 2]);

// CSRF
$token = Middleware::csrfGenerate();   // en formularios
Middleware::csrfVerify();              // en POST handlers
```

---

### 2.6 `aplicacion/core/Router.php` — Rutas amigables

**Antes:** No existía router. El `index.php` tenía una cadena de `if/else` para mapear vistas.

**Después:** Router RESTful con parámetros de URL:
```php
$router = new Router();
$router->get('/api/recursos',         [RecursoApiController::class, 'index']);
$router->get('/api/recursos/{id}',    [RecursoApiController::class, 'show']);
$router->post('/api/recursos',        [RecursoApiController::class, 'store']);
$router->put('/api/recursos/{id}',    [RecursoApiController::class, 'update']);
$router->delete('/api/recursos/{id}', [RecursoApiController::class, 'destroy']);
$router->dispatch();
```

---

## FASE 3 — API REST de Recursos

### 3.1 `aplicacion/modelos/Recurso.php` — Hereda Model

**Antes:**
```php
class Recurso {
    private $id;
    private $titulo;
    // Solo getters/setters. Sin acceso a BD.
}
```

**Después:**
```php
class Recurso extends Model {
    protected static string $tabla = 'recursos';
    // Mantiene getters/setters + hereda all/find/where/create/update/delete
}
```

---

### 3.2 `aplicacion/controladores/api/RecursoApiController.php` — REST completo

**Antes:** No existía ningún endpoint API para recursos. Solo el controlador web (`RecursoController`) que usaba redirecciones y formularios HTML.

**Después:** Controlador REST con los 5 verbos:

| Verbo  | Ruta                 | HTTP Success | HTTP Error |
|--------|----------------------|-------------|------------|
| GET    | `/api/recursos`      | 200         | 401        |
| GET    | `/api/recursos/{id}` | 200         | 404        |
| POST   | `/api/recursos`      | 201         | 422        |
| PUT    | `/api/recursos/{id}` | 200         | 404 / 422  |
| DELETE | `/api/recursos/{id}` | 200 (soft)  | 404        |

DELETE hace soft-delete (mueve a `recursos_papelera`), igual que la interfaz web.

---

### 3.3 `public/index.php` — Bloque de enrutamiento API

**Antes:** Solo routing de vistas (sin soporte API):
```php
$vista = $_GET['vista'] ?? 'inicio';
// if/else para mapear rutas...
```

**Después:** El bloque API se evalúa primero:
```php
$_uriActual = strtok($_SERVER['REQUEST_URI'], '?');
if (str_starts_with($_uriActual, '/IglesiaDelNazarenoBagua/api/')) {
    $router = new \aplicacion\core\Router();
    // ...rutas registradas...
    $router->dispatch();   // responde JSON y termina
}
// Routing de vistas sigue igual debajo
$vista = $_GET['vista'] ?? 'inicio';
```

---

## FASE 4 — AuthController

### 4.1 `aplicacion/controladores/AuthController.php` — Lógica auth centralizada

**Antes:** No existía. La lógica de autenticación vivía en archivos de proceso procedurales.

**Después:**
```php
class AuthController {
    public function login(): void    { /* valida, verifica, regenera sesión, redirige */ }
    public function logout(): void   { /* destruye sesión, redirige */ }
    public function registrar(): void { /* delega a RegistroController, redirige */ }
    private function redirect(string $ruta): void { ... }
}
```

---

### 4.2 `procesos/auth/procesar_login.php` — Reducido a wrapper

**Antes (57 líneas):**
```php
<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
use aplicacion\dao\UserDAO;
$urlBase = "/IglesiaDelNazarenoBagua/";
$usuario  = trim($_POST['usuario']  ?? '');
$password = trim($_POST['password'] ?? '');
if (empty($usuario) || empty($password)) { ... }
$dao = new UserDAO();
$resultado = $dao->buscarParaLogin($usuario);
if (!$resultado) { ... }
if (!password_verify($password, $resultado['password'])) { ... }
session_regenerate_id(true);
$_SESSION['usuario']    = $resultado['username'];
// ... etc.
```

**Después (3 líneas):**
```php
<?php
require_once __DIR__ . '/../../vendor/autoload.php';
(new \aplicacion\controladores\AuthController())->login();
```

---

### 4.3 `procesos/auth/logout.php` — Reducido a wrapper

**Antes (7 líneas):**
```php
<?php
session_start();
session_unset();
session_destroy();
header("Location: /IglesiaDelNazarenoBagua/public/index.php?vista=login");
exit;
```
> Nota: la URL de redirección era `/public/index.php?vista=login` (ruta antigua).

**Después (3 líneas + URL corregida):**
```php
<?php
require_once __DIR__ . '/../../vendor/autoload.php';
(new \aplicacion\controladores\AuthController())->logout();
```
> Ahora redirige a `/IglesiaDelNazarenoBagua/login` (URL limpia del router).

---

### 4.4 `procesos/registros/procesar_reguistro.php` — Doble corrección

**Antes (20 líneas, 2 bugs):**
```php
<?php
// BUG 1: apuntaba al autoloader artesanal eliminado
require_once __DIR__ . '/aplicacion/core/Autoload.php';
use aplicacion\controladores\RegistroController;
$controller = new RegistroController();
$resultado = $controller->registrar(...$_POST values...);
if ($resultado) {
    // BUG 2: URL directa al archivo PHP, sin pasar por el router
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/contenidos/usuarios_admin.php?exito=1");
} else {
    header("Location: /IglesiaDelNazarenoBagua/aplicacion/vistas/admin/contenidos/usuarios_admin.php?error=1");
}
```

**Después (3 líneas, ambos bugs corregidos):**
```php
<?php
require_once __DIR__ . '/../../vendor/autoload.php';
(new \aplicacion\controladores\AuthController())->registrar();
```
> Redirige a `dashboard?seccion=usuarios_admin&exito=1` (router limpio).

---

## Resumen de Archivos por Fase

| Fase | Archivos creados | Archivos modificados |
|------|-----------------|----------------------|
| 1    | —               | `composer.json`, `UserLogin.php` (rename), `RegistroController.php`, `UserDAO.php`, `index.php`, `procesar_login.php`, `dashboard.php`, `get_live_status.php` |
| 2    | `Response.php`, `QueryBuilder.php`, `Model.php`, `Validator.php`, `Middleware.php`, `Router.php` | — |
| 3    | `RecursoApiController.php` | `Recurso.php`, `index.php` |
| 4    | `AuthController.php` | `procesar_login.php`, `logout.php`, `procesar_reguistro.php` |
| 5    | `JUSTIFICACION_TECNICA.md`, `ARCHITECTURE.md`, `CHANGELOG.md` | — |

**Total:** 10 archivos nuevos · 12 archivos modificados · 1 archivo renombrado.
