# Resumen de Cambios — P2.1
**Proyecto:** Iglesia del Nazareno Bagua | **Fecha:** 2026-05-21

---

## FASE 1 — PSR-4 y Autoloading con Composer

| Archivo | Qué cambió | Por qué |
|---|---|---|
| `composer.json` | Se añadió sección `autoload.psr-4` | Sin esto, Composer no actúa como autoloader oficial y la rúbrica queda en Nivel 3 |
| `aplicacion/modelos/userLogin.php` → `UserLogin.php` | Renombrado archivo + clase | PSR-1 obliga a que el nombre del archivo coincida con la clase en StudlyCaps |
| `aplicacion/controladores/RegistroController.php` | `userLogin` → `UserLogin`, `userDAO` → `UserDAO` | Actualizar referencias tras el renombrado |
| `aplicacion/dao/UserDAO.php` | `userLogin` → `UserLogin` en type hint | Idem |
| `public/index.php` | Ruta del autoload a `vendor/autoload.php` | Dejar de usar el autoloader artesanal |
| `procesos/auth/procesar_login.php` | Ruta del autoload a `vendor/autoload.php` | Idem |
| `aplicacion/vistas/admin/dashboard.php` | Ruta del autoload a `vendor/autoload.php` | Idem |
| `aplicacion/vistas/web/procesos/get_live_status.php` | Ruta del autoload a `vendor/autoload.php` | Idem |

---

## FASE 2 — Core Framework (6 clases nuevas)

Todos en `aplicacion/core/`. Se crearon para tener una base reutilizable en vez de repetir código en cada controlador.

| Clase | Qué hace | Por qué se creó |
|---|---|---|
| `Response.php` | Envía JSON con el código HTTP correcto (`success`, `error`, `created`, `notFound`, `unprocessable`) | Antes cada endpoint armaba el JSON diferente y sin código HTTP |
| `QueryBuilder.php` | Construye consultas SQL de forma encadenable (`table→where→orderBy→get`) | Evitar SQL escrito a mano y repetido en cada DAO |
| `Model.php` | Clase abstracta con `all()`, `find()`, `where()`, `create()`, `update()`, `delete()` | Base para modelos con acceso a BD sin SQL repetido |
| `Validator.php` | Valida inputs con reglas `required\|min:N\|max:N\|email\|in:a,b` | Centralizar validación en vez de ifs sueltos |
| `Middleware.php` | `auth()` para vistas, `apiAuth()` para API, `csrfGenerate/Verify()` para CSRF | Centralizar control de acceso y seguridad |
| `Router.php` | Mapea rutas `/ruta/{param}` a controladores por verbo HTTP | Necesario para los endpoints REST de la API |

---

## FASE 3 — API REST de Recursos

| Archivo | Qué cambió | Por qué |
|---|---|---|
| `aplicacion/modelos/Recurso.php` | Ahora extiende `Model` con `$tabla = 'recursos'` | Para heredar `all/find/create/update/delete` sin código extra |
| `aplicacion/controladores/api/RecursoApiController.php` | Creado desde cero con 5 endpoints REST | La rúbrica exige API REST con verbos HTTP correctos y validación |
| `public/index.php` | Bloque que detecta `/api/` y despacha por el Router | Sin esto las rutas `/api/recursos` no llegan al controlador API |

**Endpoints creados:** `GET /api/recursos`, `GET /api/recursos/{id}`, `POST /api/recursos` (201), `PUT /api/recursos/{id}` (200), `DELETE /api/recursos/{id}` (soft-delete, 200). Todos protegidos con `Middleware::apiAuth()` y validados con `Validator`.

---

## FASE 4 — AuthController

| Archivo | Qué cambió | Por qué |
|---|---|---|
| `aplicacion/controladores/AuthController.php` | Creado con `login()`, `logout()`, `registrar()` | Centralizar la lógica de autenticación que estaba dispersa en archivos procedurales |
| `procesos/auth/procesar_login.php` | De 57 líneas a 2 líneas (wrapper) | Toda la lógica se movió al controlador |
| `procesos/auth/logout.php` | De 7 líneas a 2 líneas (wrapper) + redirect corregido | Redirect apuntaba a URL antigua (`public/index.php?vista=login`) |
| `procesos/registros/procesar_reguistro.php` | De 20 líneas a 2 líneas + autoload corregido | Tenía el autoload artesanal apuntando a una ruta incorrecta |

---

## FASE 5 — Documentación

Carpeta `docs/` creada con tres archivos:

| Archivo | Contenido |
|---|---|
| `JUSTIFICACION_TECNICA.md` | Argumenta cada criterio de la rúbrica con ejemplos de código |
| `ARCHITECTURE.md` | Árbol de directorios, diagramas de flujo, tabla de namespaces |
| `CHANGELOG.md` | Walkthrough antes/después detallado de cada archivo |

---

## SEGURIDAD — Correcciones adicionales

| Archivo | Qué cambió | Por qué |
|---|---|---|
| `aplicacion/.htaccess` | `Require all denied` | Cualquiera podía acceder directamente a `membresia.php`, `usuarios_admin.php`, etc. sin autenticación. Esta sola línea bloquea todo el directorio a nivel Apache |
| `public/index.php` | Ruta `?vista=logout` agregada | El enlace del sidebar apuntaba a esta ruta pero no existía el handler; el logout no funcionaba |
| `aplicacion/vistas/admin/contenidos/membresia.php` | Eliminado `require` al autoload viejo + XSS corregido en `$nombreCargo` | El `require` apuntaba a un archivo obsoleto; `$nombreCargo` se imprimía sin escapar (XSS) |
| `aplicacion/vistas/admin/contenidos/procesar_eliminar.php` | Namespace `dao\NoticiaDAO` → `aplicacion\dao\NoticiaDAO` + eliminado `display_errors` + errores a `error_log` | Namespace incorrecto lo hacía fallar silenciosamente; los errores de BD se mostraban al usuario |
| `aplicacion/vistas/admin/contenidos/usuarios_admin.php` | `userDAO` → `UserDAO` + redirects corregidos | Clase en minúscula rompía el listado de usuarios; redirects apuntaban a rutas de archivo directas |

### CSRF — Protección completa en 13 archivos

**Por qué:** Sin CSRF, cualquier sitio externo puede enviar un formulario en nombre de un administrador autenticado (borrar miembros, crear usuarios, etc.) sin que el servidor lo detecte.

**Cómo se implementó en 3 capas:**

1. **Generación** — `dashboard.php` genera el token una vez por sesión y lo expone como variable PHP `$csrfToken` (disponible en todos los archivos incluidos) y como constante JS `CSRF_TOKEN`.

2. **Formularios** — Campo oculto `<input type="hidden" name="csrf_token">` añadido a los 13 formularios:
   - `membresia`, `noticias`, `transmision`, `recurso_admin` (×2), `usuarios_admin` (×2), `DiscipuladoGrupos`, `DiscipuladoIntegrantes`, `inv_reguistro_usuario`, `visitasListar` (×3), `login`

3. **Verificación** — `Middleware::csrfVerify()` añadido en:
   - `dashboard.php` → cubre todos los POST que entran por el panel admin
   - `AuthController::login()` → cubre el formulario de login
   - `index.php` (3 endpoints AJAX: guardarVisita, guardarAjustesVisita, eliminarVisita)

---

## Contador total de cambios

| Tipo | Cantidad |
|---|---|
| Archivos nuevos creados | 13 |
| Archivos existentes modificados | 24 |
| Clases registradas en Composer (final) | 179 |
