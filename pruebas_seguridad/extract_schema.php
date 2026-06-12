<?php
$sql = file_get_contents('c:/xampp/htdocs/IglesiaDelNazarenoBagua/IglesiaDelNazarenoBagua/iglesiadelnazareno.sql');
$tables = ['miembros', 'noticias', 'discipulado_grupos', 'transmisiones'];
foreach ($tables as $t) {
    if (preg_match('/CREATE TABLE `' . $t . '`[^;]+;/s', $sql, $m)) {
        echo $m[0] . "\n\n";
    }
}
