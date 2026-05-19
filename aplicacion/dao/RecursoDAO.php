<?php
namespace aplicacion\dao;

use aplicacion\config\Conexion;

class RecursoDAO {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
        $this->_migrarRutaThumb();
        $this->_migrarRutaThumbPapelera();
    }

    private function _migrarRutaThumb(): void {
        try {
            $this->pdo->query("SELECT ruta_thumb FROM recursos LIMIT 1");
        } catch (\PDOException $e) {
            $this->pdo->exec("ALTER TABLE recursos ADD COLUMN ruta_thumb VARCHAR(500) DEFAULT NULL");
        }
    }

    private function _migrarRutaThumbPapelera(): void {
        try {
            $this->pdo->query("SELECT ruta_thumb FROM recursos_papelera LIMIT 1");
        } catch (\PDOException $e) {
            $this->pdo->exec("ALTER TABLE recursos_papelera ADD COLUMN ruta_thumb VARCHAR(500) DEFAULT NULL");
        }
    }

    public function listar(): array {
        $sql  = "SELECT r.*, u.username AS creado_por_nombre
                 FROM recursos r
                 LEFT JOIN usuarios u ON r.creado_por = u.id
                 ORDER BY r.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function insertar(array $datos): int {
        $sql  = "INSERT INTO recursos
                    (titulo, descripcion, categoria, tipo, ruta_archivo, enlace_youtube, creado_por)
                 VALUES
                    (:titulo, :descripcion, :categoria, :tipo, :ruta_archivo, :enlace_youtube, :creado_por)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':titulo'         => $datos['titulo'],
            ':descripcion'    => $datos['descripcion'],
            ':categoria'      => $datos['categoria'],
            ':tipo'           => $datos['tipo'],
            ':ruta_archivo'   => $datos['ruta_archivo'],
            ':enlace_youtube' => $datos['enlace_youtube'],
            ':creado_por'     => $datos['creado_por'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function guardarThumb(int $id, string $ruta): bool {
        $stmt = $this->pdo->prepare("UPDATE recursos SET ruta_thumb = :ruta WHERE id = :id");
        return $stmt->execute([':ruta' => $ruta, ':id' => $id]);
    }

    public function generarYGuardarThumb(int $id, string $ruta_archivo, string $tipo, string $enlace_youtube = ''): void {
        $thumb = null;

        if (!empty($enlace_youtube)) {
            $thumb = $this->_thumbYoutube($enlace_youtube);
        }

        if ($thumb === null) {
            $abs = !empty($ruta_archivo)
                ? ($_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/' . $ruta_archivo)
                : '';

            switch ($tipo) {
                case 'img':
                    $resized = !empty($abs) ? $this->_thumbImg($abs, $id) : null;
                    $thumb   = $resized ?? (!empty($ruta_archivo) ? $ruta_archivo : null);
                    break;
                case 'pdf':
                    $thumb = !empty($abs) ? $this->_thumbPdf($abs, $id) : null;
                    break;
                case 'doc':
                    $thumb = !empty($abs) ? $this->_thumbDoc($abs, $id) : null;
                    break;
                case 'vid':
                    $thumb = !empty($abs) ? $this->_thumbVid($abs, $id) : null;
                    break;
            }
        }

        $this->guardarThumb($id, $thumb ?? '');
    }

    private function _thumbYoutube(string $url): ?string {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s#]+)/', $url, $m)) {
            return 'https://img.youtube.com/vi/' . $m[1] . '/mqdefault.jpg';
        }
        return null;
    }

    private function _thumbPdf(string $abs, int $id): ?string {
        $dir  = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/public/web/imagenes/thumbs/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $dest = $dir . 'thumb_' . $id . '.jpg';

        foreach (['gswin64c', 'gswin32c', 'gs'] as $gs) {
            $cmd = $gs
                 . ' -dNOPAUSE -dBATCH -dSAFER -sDEVICE=jpeg -r96'
                 . ' -dFirstPage=1 -dLastPage=1'
                 . ' -sOutputFile=' . escapeshellarg($dest)
                 . ' ' . escapeshellarg($abs) . ' 2>NUL';
            exec($cmd, $out, $code);
            if ($code === 0 && file_exists($dest)) {
                return 'public/web/imagenes/thumbs/thumb_' . $id . '.jpg';
            }
        }

        if (extension_loaded('imagick')) {
            try {
                $im = new \Imagick();
                $im->setResolution(150, 150);
                $im->readImage($abs . '[0]');
                $im->setImageFormat('jpeg');
                $im->thumbnailImage(400, 300, true);
                $im->writeImage($dest);
                $im->destroy();
                if (file_exists($dest)) return 'public/web/imagenes/thumbs/thumb_' . $id . '.jpg';
            } catch (\Exception $e) { }
        }

        if (extension_loaded('gd')) {
            $texto = $this->_extraerTextoPdf($abs);
            if ($texto === '') {
                $texto = str_replace(['_', '-'], ' ', pathinfo($abs, PATHINFO_FILENAME));
            }
            return $this->_textoAImagen($texto, $dir, $id, 'pdf');
        }

        return null;
    }

    private function _extraerTextoPdf(string $abs): string {
        $raw = @file_get_contents($abs, false, null, 0, 65536);
        if (!$raw) return '';
        $text = '';
        if (preg_match_all('/BT[\s\S]+?ET/', $raw, $blocks)) {
            foreach ($blocks[0] as $b) {
                preg_match_all('/\(([^()]+)\)\s*Tj/', $b, $tjs);
                foreach ($tjs[1] as $s) {
                    $clean = preg_replace('/[^\x20-\x7E]/', '', $s);
                    if (strlen(trim($clean)) > 1) $text .= $clean . ' ';
                }
            }
        }
        return mb_substr(trim($text), 0, 400);
    }

    private function _thumbDoc(string $abs, int $id): ?string {
        $dir = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/public/web/imagenes/thumbs/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $cmd = 'soffice --headless --convert-to png --outdir ' . escapeshellarg($dir) . ' ' . escapeshellarg($abs) . ' 2>NUL';
        exec($cmd, $out, $code);
        if ($code === 0) {
            $png = $dir . pathinfo($abs, PATHINFO_FILENAME) . '.png';
            if (file_exists($png)) {
                $dest = $dir . 'thumb_' . $id . '.png';
                rename($png, $dest);
                return 'public/web/imagenes/thumbs/thumb_' . $id . '.png';
            }
        }

        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        if (in_array($ext, ['docx', 'odt', 'xlsx', 'pptx']) && class_exists('ZipArchive') && extension_loaded('gd')) {
            $texto = $this->_extraerTextoDocx($abs);
            if ($texto !== '') {
                return $this->_textoAImagen($texto, $dir, $id, $ext);
            }
        }

        if (extension_loaded('gd')) {
            $texto = str_replace(['_', '-'], ' ', pathinfo($abs, PATHINFO_FILENAME));
            return $this->_textoAImagen($texto, $dir, $id, $ext ?: 'doc');
        }

        return null;
    }

    private function _extraerTextoDocx(string $abs): string {
        $zip = new \ZipArchive();
        if ($zip->open($abs) !== true) return '';

        if ($zip->locateName('word/document.xml') !== false) {
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            if (!$xml) return '';
            $text = strip_tags(str_replace(['</w:p>', '</w:br>'], "\n", $xml));
        } elseif ($zip->locateName('content.xml') !== false) {
            $xml = $zip->getFromName('content.xml');
            $zip->close();
            if (!$xml) return '';
            $text = strip_tags(str_replace(['</text:p>'], "\n", $xml));
        } elseif ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $xml = $zip->getFromName('xl/sharedStrings.xml');
            $zip->close();
            if (!$xml) return '';
            $text = strip_tags(str_replace('</si>', "\n", $xml));
        } else {
            $parts = [];
            for ($s = 1; $s <= 3; $s++) {
                $entry = "ppt/slides/slide{$s}.xml";
                if ($zip->locateName($entry) !== false) {
                    $parts[] = $zip->getFromName($entry);
                }
            }
            $zip->close();
            if (empty($parts)) return '';
            $text = strip_tags(str_replace(['</a:p>', '</a:br>'], "\n", implode("\n", $parts)));
        }

        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", trim($text));
        return mb_substr($text, 0, 400);
    }

    private function _textoAImagen(string $texto, string $dir, int $id, string $ext = 'doc'): ?string {
        if (!extension_loaded('gd')) return null;
        $w = 400; $h = 280;
        $img    = imagecreatetruecolor($w, $h);
        $blanco = imagecolorallocate($img, 255, 255, 255);
        $tinta  = imagecolorallocate($img, 28,  38,  56);
        $gris   = imagecolorallocate($img, 140, 150, 165);
        $acento = match ($ext) {
            'pdf'   => imagecolorallocate($img, 239,  68,  68),
            'xlsx'  => imagecolorallocate($img,  34, 197,  94),
            'pptx'  => imagecolorallocate($img, 249, 115,  22),
            'odt'   => imagecolorallocate($img,  59, 130, 246),
            default => imagecolorallocate($img, 253, 224,  71),
        };
        imagefill($img, 0, 0, $blanco);
        imagefilledrectangle($img, 0, 0, $w, 5, $acento);
        $sz   = 2;
        $fW   = imagefontwidth($sz);
        $fH   = imagefontheight($sz);
        $marg = 16;
        $cols = max(1, intval(($w - $marg * 2) / $fW));
        $y    = $marg + 10;
        foreach (array_slice(explode("\n", wordwrap($texto, $cols, "\n", true)), 0, 16) as $linea) {
            if ($y + $fH > $h - $marg) break;
            imagestring($img, $sz, $marg, $y, $linea, $tinta);
            $y += $fH + 3;
        }
        imagestring($img, 1, $marg, $h - 16, strtoupper($ext), $gris);
        $dest = $dir . 'thumb_' . $id . '.jpg';
        imagejpeg($img, $dest, 88);
        imagedestroy($img);
        return file_exists($dest) ? 'public/web/imagenes/thumbs/thumb_' . $id . '.jpg' : null;
    }

    private function _thumbImg(string $abs, int $id): ?string {
        if (!extension_loaded('gd') || !file_exists($abs)) return null;
        $info = @getimagesize($abs);
        if (!$info || $info[0] <= 600) return null;
        $src = match($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($abs),
            IMAGETYPE_PNG  => imagecreatefrompng($abs),
            IMAGETYPE_GIF  => imagecreatefromgif($abs),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($abs) : null,
            default        => null,
        };
        if (!$src) return null;
        $newW = 600;
        $newH = (int)round($info[1] * 600 / $info[0]);
        $dst  = imagecreatetruecolor($newW, $newH);
        if ($info[2] === IMAGETYPE_PNG) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $info[0], $info[1]);
        imagedestroy($src);
        $dir  = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/public/web/imagenes/thumbs/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $dest = $dir . 'thumb_' . $id . '.jpg';
        imagejpeg($dst, $dest, 85);
        imagedestroy($dst);
        return file_exists($dest) ? 'public/web/imagenes/thumbs/thumb_' . $id . '.jpg' : null;
    }

    private function _thumbVid(string $abs, int $id): ?string {
        $dir  = $_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/public/web/imagenes/thumbs/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $dest = $dir . 'thumb_' . $id . '.jpg';
        $cmd  = 'ffmpeg -y -i ' . escapeshellarg($abs) . ' -ss 00:00:03 -vframes 1 ' . escapeshellarg($dest) . ' 2>NUL';
        exec($cmd, $out, $code);
        if ($code === 0 && file_exists($dest)) {
            return 'public/web/imagenes/thumbs/thumb_' . $id . '.jpg';
        }
        return null;
    }

    public function actualizar(array $datos): bool {
        $sql  = "UPDATE recursos SET
                    titulo         = :titulo,
                    descripcion    = :descripcion,
                    categoria      = :categoria,
                    tipo           = :tipo,
                    ruta_archivo   = :ruta_archivo,
                    enlace_youtube = :enlace_youtube
                 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':titulo'         => $datos['titulo'],
            ':descripcion'    => $datos['descripcion'],
            ':categoria'      => $datos['categoria'],
            ':tipo'           => $datos['tipo'],
            ':ruta_archivo'   => $datos['ruta_archivo'],
            ':enlace_youtube' => $datos['enlace_youtube'],
            ':id'             => $datos['id'],
        ]);
    }

    public function moverAPapelera(int $id): bool {
        $sql  = "SELECT * FROM recursos WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $recurso = $stmt->fetch();

        if (!$recurso) return false;

        $sql2 = "INSERT INTO recursos_papelera
                    (recurso_id, titulo, descripcion, categoria, tipo, ruta_archivo, enlace_youtube, eliminado_por, ruta_thumb)
                 VALUES
                    (:recurso_id, :titulo, :descripcion, :categoria, :tipo, :ruta_archivo, :enlace_youtube, :eliminado_por, :ruta_thumb)";
        $stmt2 = $this->pdo->prepare($sql2);
        $stmt2->execute([
            ':recurso_id'    => $recurso['id'],
            ':titulo'        => $recurso['titulo'],
            ':descripcion'   => $recurso['descripcion'],
            ':categoria'     => $recurso['categoria'],
            ':tipo'          => $recurso['tipo'],
            ':ruta_archivo'  => $recurso['ruta_archivo'],
            ':enlace_youtube'=> $recurso['enlace_youtube'],
            ':eliminado_por' => $_SESSION['usuario_id'] ?? null,
            ':ruta_thumb'    => $recurso['ruta_thumb'] ?? null,
        ]);

        $sql3 = "DELETE FROM recursos WHERE id = :id";
        $stmt3 = $this->pdo->prepare($sql3);
        return $stmt3->execute([':id' => $id]);
    }

    public function listarPapelera(): array {
        $sql  = "SELECT * FROM recursos_papelera ORDER BY fecha_eliminacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function restaurar(int $id): bool {
        $sql  = "SELECT * FROM recursos_papelera WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $recurso = $stmt->fetch();

        if (!$recurso) return false;

        $sql2 = "INSERT INTO recursos
                    (titulo, descripcion, categoria, tipo, ruta_archivo, enlace_youtube, creado_por)
                 VALUES
                    (:titulo, :descripcion, :categoria, :tipo, :ruta_archivo, :enlace_youtube, :creado_por)";
        $stmt2 = $this->pdo->prepare($sql2);
        $stmt2->execute([
            ':titulo'         => $recurso['titulo'],
            ':descripcion'    => $recurso['descripcion'],
            ':categoria'      => $recurso['categoria'],
            ':tipo'           => $recurso['tipo'],
            ':ruta_archivo'   => $recurso['ruta_archivo'],
            ':enlace_youtube' => $recurso['enlace_youtube'],
            ':creado_por'     => $recurso['eliminado_por'],
        ]);

        $sql3 = "DELETE FROM recursos_papelera WHERE id = :id";
        $stmt3 = $this->pdo->prepare($sql3);
        return $stmt3->execute([':id' => $id]);
    }

    public function eliminarDefinitivo(int $id): bool {
        $sql  = "DELETE FROM recursos_papelera WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function incrementarDescargas(int $id): bool {
        $sql  = "UPDATE recursos SET descargas = descargas + 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function vaciarPapelera(): bool {
        $stmt = $this->pdo->prepare("DELETE FROM recursos_papelera");
        return $stmt->execute();
    }

    public function obtenerPorId(int $id): ?array {
        $sql  = "SELECT * FROM recursos WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

}
