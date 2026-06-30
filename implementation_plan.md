# Implementación de RBAC (Role-Based Access Control)

Este plan detalla los pasos para aplicar los roles de **Pastor, Secretaria y Visitas** en todos los módulos del sistema, asegurando que cada rol tenga los accesos y restricciones solicitados.

## User Review Required

> [!IMPORTANT]
> **Asignación de Roles:** Asumo que los IDs de los roles en la base de datos son:
> - **1**: Admin
> - **2**: Pastor
> - **3**: Secretaria
> - **4**: Grupo de Visitas
> Si los IDs en tu base de datos son diferentes, por favor indícamelo en tu respuesta para ajustar el código antes de ejecutarlo.

> [!NOTE]
> **Accesos propuestos para Secretaria:** Por defecto, configuraré que la *Secretaria* tenga acceso a casi todos los módulos (Membresía, Reportes, Noticias, Visitas, Discipulado) **excepto** la gestión de Usuarios y Configuración avanzada del sistema (o eliminación definitiva de registros). ¿Estás de acuerdo con este nivel de acceso?

## Proposed Changes

---
### Núcleo (Core y Controladores)

#### [MODIFY] [Middleware.php](file:///c:/xampp/htdocs/IglesiaDelNazarenoBagua/aplicacion/core/Middleware.php)
- Ampliar la función `auth` y `apiAuth` para que acepte un arreglo dinámico de roles en lugar de solo `[1, 2]`.

#### [MODIFY] [VisitaController.php](file:///c:/xampp/htdocs/IglesiaDelNazarenoBagua/aplicacion/controladores/VisitaController.php)
- **listarConDetalles()**: Agregar el campo `registrado_por` en los resultados para saber quién registró cada visita.
- **guardarVisita()**: Si el rol es "Visitas" (4) y está editando (`visita_id > 0`), validar que el usuario actual sea el creador de la visita. Si no, rechazar la operación.
- **eliminarVisita()**: Validar que si el rol es "Visitas" (4), solo pueda eliminar (`estado = 0`) si él mismo fue quien registró la visita.

---
### Vistas y Navegación

#### [MODIFY] [dashboard.php](file:///c:/xampp/htdocs/IglesiaDelNazarenoBagua/aplicacion/vistas/admin/dashboard.php)
- Actualizar `Middleware::auth([1, 2, 3, 4]);` para permitir la entrada de los 4 roles al panel.
- Implementar un diccionario (array) de control de acceso por `$vistaInterna` y `$rol_id`.
  - **Admin (1) / Pastor (2)**: Acceso total.
  - **Secretaria (3)**: Acceso a módulos de gestión, sin gestión de usuarios.
  - **Visitas (4)**: Solo acceso a `inicioAdmin`, `visitasListar`, `visitasMap`.
- Si se intenta acceder a una vista no permitida, se mostrará la pantalla de "Acceso Denegado".

#### [MODIFY] [sidebar.php](file:///c:/xampp/htdocs/IglesiaDelNazarenoBagua/aplicacion/vistas/admin/includes/sidebar.php)
- Ocultar o mostrar las opciones del menú lateral dependiendo del `rol_id` de la sesión.
- El grupo de "Visitas" solo verá el menú de "Lista de Visitas" y "Mapa de Visitas" (y el Inicio).

#### [MODIFY] [visitasListar.php](file:///c:/xampp/htdocs/IglesiaDelNazarenoBagua/aplicacion/vistas/admin/contenidos/visitasListar.php)
- Ocultar los botones de **Editar** y **Eliminar** visita si el usuario tiene el rol "Visitas" (4) y la visita no fue registrada por él.
- Solo verá el botón de "Registrar" y los botones de acción para las visitas propias.

---
### Auditoría

#### [NEW] [auditoria_rbac.md](file:///c:/xampp/htdocs/IglesiaDelNazarenoBagua/auditoria_rbac.md)
- Crearé un documento detallado (fuera del código fuente, como un artefacto) con todo lo que se agregó y cambió para que tengas una guía clara y estructurada para tu exposición.

## Verification Plan

### Manual Verification
1. Iniciar sesión como **Pastor** y verificar acceso total a todos los módulos y menús.
2. Iniciar sesión como **Secretaria** y verificar acceso a los módulos operativos, y bloqueo a módulos administrativos (Usuarios).
3. Iniciar sesión como **Grupo de Visitas**. Verificar que el menú solo muestre "Visitas" e "Inicio".
4. Como **Grupo de Visitas**, intentar editar/eliminar una visita creada por otro usuario (debería ocultarse el botón y, si se intenta forzar por consola, el servidor lo rechazará).
5. Como **Grupo de Visitas**, crear una visita nueva, y verificar que esta sí se pueda editar o eliminar.
