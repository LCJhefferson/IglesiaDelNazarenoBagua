# Justificación Técnica — Producto P2.1
**Curso:** Desarrollo de Aplicaciones Web II — 5° Ciclo  
**Proyecto:** Sistema de Gestión Iglesia del Nazareno Bagua  
**Stack:** PHP 8.x · MySQL · PDO · Composer · Apache (XAMPP)

---
---

## Fundamentación Arquitectónica (Análisis Comparativo)

### Patrón elegido
**MVC + DAO + Core Framework propio** sobre PHP vanilla con Composer y PSR-4.

### Alternativas evaluadas y descartadas

Antes de implementar, comparamos 4 estilos arquitectónicos contra las restricciones reales del proyecto (8 entidades CRUD, hosting compartido, equipo de 5° ciclo, plazo de entrega ajustado):

| Estilo | Pros | Contras | ¿Por qué NO? |
|---|---|---|---|
| **MVC + DAO** (elegido) | Maduro, conocido, encaja con CRUDs, deploy simple en hosting compartido, permite trabajo paralelo del equipo | Riesgo de "fat controllers" si crece sin disciplina | ✅ **Elegido**. Se mitiga el riesgo con la capa Core (Model, Validator, Middleware). |
| Arquitectura por capas (DDD táctico) | Excelente separación dominio/infraestructura, ideal para reglas de negocio complejas | Excesivo boilerplate: servicios, repositorios, DTOs, value objects | ❌ Sobreingeniería para 8 CRUDs simples. El esfuerzo no se traduce en beneficios visibles. |
| Microservicios | Escalabilidad horizontal independiente | Múltiples despliegues, orquestación, monitoreo distribuido | ❌ No requerimos escalar. Una iglesia local no genera tráfico que lo justifique. |
| Arquitectura Hexagonal (Ports & Adapters) | Testabilidad excelente, dominio aislado | Curva de aprendizaje alta, código adapter abundante | ❌ El equipo no domina el patrón a profundidad. El tiempo de aprendizaje habría comprometido la entrega. |

### Trade-offs aceptados conscientemente

Toda arquitectura tiene costos. Identificamos y mitigamos los nuestros:

| Trade-off | Riesgo | Mitigación aplicada |
|---|---|---|
| MVC puede degenerar en "fat controllers" | Lógica de negocio termina en el controlador | Toda persistencia vive en `Model`/DAOs; controladores solo orquestan |
| DAOs sin ORM completo generan código repetitivo | Métodos `listar()`, `eliminar()` se repiten | `QueryBuilder` encadenable + `Model` abstracto con `all/find/where/create/update/delete` |
| PHP nativo sin contenedor DI | Difícil testar con mocks | Aceptado para esta versión; refactorizable a futuro con PHP-DI |
| Hosting compartido sin background jobs | No podemos correr tareas asíncronas | Tiempo real resuelto con Pusher (sin servidor WebSocket propio) |

### Por qué MVC + DAO es la opción **correcta** para este proyecto

1. **Encaje natural**: 8 entidades CRUD se mapean 1:1 con el patrón Modelo-Vista-Controlador.
2. **Productividad del equipo**: el equipo conoce MVC desde DAW I, lo que permite invertir el tiempo en features y no en aprender arquitectura.
3. **Infraestructura destino compatible**: hosting compartido + Apache + PHP nativo, sin requerimientos especiales.
4. **Doble salida desde un solo backend**: el mismo `RecursoApiController` puede devolver JSON (API REST) y HTML (panel admin) reutilizando los modelos. Esto es difícil de lograr con arquitecturas más rígidas.
5. **Riesgos conocidos y mitigados**: no elegimos MVC por defecto, lo elegimos sabiendo sus debilidades y construyendo defensas (Core Framework propio).

---
## Criterio 1 — Organización del Código y Estándares de Estructura (PSR-4)

### Situación inicial
El proyecto usaba un autoloader artesanal (`aplicacion/core/Autoload.php`) basado en `spl_autoload_register`. Esto corresponde al **Nivel 3 (Logrado)** de la rúbrica.

### Cambio aplicado
Se declaró formalmente PSR-4 en `composer.json`:

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

Se ejecutó `php composer.phar dump-autoload -o` para generar el classmap optimizado (179 clases indexadas).

### Por qué esto cumple el Nivel 4 (Avanzado)
- El autoloading ya no depende de código artesanal: Composer resuelve las clases siguiendo el estándar PSR-4 oficialmente declarado.
- El classmap optimizado (`-o`) mejora el rendimiento al evitar búsquedas en disco en producción.
- Todos los nombres de clase siguen PSR-1 (StudlyCaps): `UserLogin`, `UserDAO`, `InicioDAO`, `RecursoApiController`, etc.

---

## Criterio 2 — Documentación Técnica

Ver `docs/ARCHITECTURE.md` para el diagrama de capas y `docs/CHANGELOG.md` para el walkthrough antes/después de cada cambio.

---

## Criterio 3 — Capa de Datos (Core Framework)

### Problema original
Cada DAO repetía la misma inicialización PDO y construía queries SQL a mano sin ninguna abstracción reutilizable.

### Solución implementada

#### `aplicacion/core/QueryBuilder.php`
Query builder encadenable que encapsula la construcción de SQL con PDO:

```php
// Ejemplo de uso encadenado
(new QueryBuilder())
    ->table('recursos')
    ->where('categoria', '=', 'predica')
    ->orderBy('fecha_creacion', 'DESC')
    ->limit(10)
    ->get();
```

Métodos disponibles: `table()`, `select()`, `where()`, `orderBy()`, `limit()`, `get()`, `first()`, `insert()`, `update()`, `delete()`.

#### `aplicacion/core/Model.php`
Clase base abstracta que usa `QueryBuilder` internamente:

```php
abstract class Model {
    protected static string $tabla = '';

    public static function all(): array { ... }
    public static function find(int $id): ?array { ... }
    public static function where(string $col, string $op, mixed $val): QueryBuilder { ... }
    public static function create(array $data): int { ... }
    public static function update(array $data, array $where): bool { ... }
    public static function delete(array $where): bool { ... }
}
```

Los modelos concretos solo declaran su tabla:

```php
class Recurso extends Model {
    protected static string $tabla = 'recursos';
}

// Uso inmediato sin SQL adicional:
Recurso::all();
Recurso::find(5);
Recurso::where('tipo', '=', 'pdf')->get();
```

---

## Criterio 4 — Controladores y Separación de Responsabilidades

### Problema original
La lógica de autenticación estaba dispersa en archivos de proceso (`procesos/auth/procesar_login.php`, `logout.php`) que mezclaban autoloading, sesiones, lógica de negocio y redirecciones en un solo bloque procedural.

### Solución: `AuthController`

```
procesos/auth/procesar_login.php   →  2 líneas (wrapper)  →  AuthController::login()
procesos/auth/logout.php           →  2 líneas (wrapper)  →  AuthController::logout()
procesos/registros/procesar_reguistro.php → 2 líneas     →  AuthController::registrar()
```

`AuthController` centraliza:
- Inicio y regeneración de sesión segura (`session_regenerate_id(true)`)
- Validación de credenciales con `password_verify()`
- Redirección por rol
- URL base en una sola constante (`URL_BASE`)

---

## Criterio 5 — API REST (Recursos)

### Endpoints implementados

| Verbo  | Ruta                   | Método               | Código HTTP |
|--------|------------------------|----------------------|-------------|
| GET    | `/api/recursos`        | `index()`            | 200         |
| GET    | `/api/recursos/{id}`   | `show()`             | 200 / 404   |
| POST   | `/api/recursos`        | `store()`            | 201 / 422   |
| PUT    | `/api/recursos/{id}`   | `update()`           | 200 / 404 / 422 |
| DELETE | `/api/recursos/{id}`   | `destroy()`          | 200 / 404   |

### Capas involucradas en cada request API

```
HTTP Request
    └─► public/index.php  (detecta /api/, instancia Router)
            └─► Router::dispatch()  (parsea URI, extrae {id})
                    └─► RecursoApiController::método()
                            ├─► Middleware::apiAuth()     (401/403 si no hay sesión)
                            ├─► Validator::make()         (422 si campos inválidos)
                            ├─► Recurso::find/all/create  (Model → QueryBuilder → PDO)
                            └─► Response::success/created/notFound  (JSON + exit)
```

### Validación de entrada (POST y PUT)

```php
$v = Validator::make($data, [
    'titulo'    => 'required|max:200',
    'categoria' => 'required|max:100',
    'tipo'      => 'required|in:pdf,img,vid,doc,yt',
]);

if ($v->fails()) {
    Response::unprocessable($v->errors());  // HTTP 422
}
```

### Soporte de formatos de entrada
El controlador acepta tanto `application/json` como `application/x-www-form-urlencoded`, lo que permite probarlo con `curl` o desde formularios HTML.

---

## Resumen de Niveles Alcanzados

| Criterio | Descripción                          | Nivel alcanzado |
|----------|--------------------------------------|-----------------|
| 1        | PSR-4 vía Composer                   | **4 — Avanzado** |
| 2        | Documentación técnica                | **4 — Avanzado** |
| 3        | Capa de datos con QueryBuilder/Model | **4 — Avanzado** |
| 4        | AuthController centralizado          | **4 — Avanzado** |
| 5        | API REST completa con validación     | **4 — Avanzado** |
