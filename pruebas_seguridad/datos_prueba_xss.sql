-- Archivo SQL para insertar datos de prueba para verificar las mitigaciones XSS
-- Instrucciones: Ejecuta este script en tu base de datos (por ejemplo, via phpMyAdmin) antes de realizar las pruebas XSS.

-- 1. Insertar Miembros
INSERT INTO `miembros` (`nombres`, `apellidos`, `telefono`, `estado`, `tipo_miembro_id`) VALUES
('D''Angelo', 'O''Brien', '123456789', 1, 1),
('<script>alert(''XSS'')</script>', 'Test', '123456789', 1, 1);

-- 2. Insertar Noticias
INSERT INTO `noticias` (`titulo`, `resumen`, `contenido`, `estado`) VALUES
('Noticia "especial" con comillas', 'Resumen de prueba', 'Contenido de prueba', 1),
('<img src=x onerror=alert(1)>', 'Resumen de prueba', 'Contenido de prueba', 1);

-- 3. Insertar Grupo de Discipulado
INSERT INTO `discipulado_grupos` (`nombre`, `nivel`) VALUES
('Grupo D''Angelo & Hermanos', 'I');

-- 4. Insertar Transmisiones
INSERT INTO `transmisiones` (`titulo`, `descripcion`) VALUES
('En vivo "hoy" <b>especial</b>', 'Descripcion de prueba');
