# Seguimiento de Tareas de Seguridad

- [x] Corrección de RCE en subida de recursos (allowlist de extensiones + sanitización)
- [x] Mitigación de LFI / Path Traversal en `public/index.php` (allowlist de vistas)
- [x] Corrección de XSS inline en atributos onclick (escapado con `htmlspecialchars`)
- [x] Autenticación centralizada en `public/index.php`
