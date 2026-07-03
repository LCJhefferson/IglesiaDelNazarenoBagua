<?php
['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
 = new PDO('mysql:host=localhost;dbname=iglesiadelnazareno', 'root', '');
 = ->query('SELECT id, ruta_archivo FROM recursos ORDER BY id DESC LIMIT 5');
 = ->fetchAll(PDO::FETCH_ASSOC);

foreach ( as ) {
     = realpath(['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/' . ['ruta_archivo']);
     = realpath(['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/admin/imagenes/recursos/');
    echo "ID {['id']} - ruta_db: {['ruta_archivo']}\n";
    echo "ruta_abs: " . var_export(, true) . "\n";
    echo "base_dir: " . var_export(, true) . "\n";
    if ( && ) {
        echo "str_starts_with: " . var_export(str_starts_with(, ), true) . "\n";
    }
    echo "--------------------------\n";
}
