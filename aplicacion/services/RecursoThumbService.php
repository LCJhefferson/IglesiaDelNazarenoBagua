<?php
namespace aplicacion\services;

use aplicacion\modelos\Recurso;

/**
 * Servicio de generación de thumbnails para recursos.
 * Extraído de RecursoDAO — lógica intacta, convertida a métodos estáticos.
 * Guarda la ruta resultante usando Recurso::update() en lugar de SQL directo.
 */
class RecursoThumbService {

    public static function generar(int $id, string $ruta_archivo, string $tipo, string $enlace_youtube = ''): void {
        $thumb = null;

        if (!empty($enlace_youtube)) {
            $thumb = self::thumbYoutube($enlace_youtube);
        }

        if ($thumb === null) {
            $abs = !empty($ruta_archivo)
                ? ($_SERVER['DOCUMENT_ROOT'] . '/IglesiaDelNazarenoBagua/' . $ruta_archivo)
                : '';

            switch ($tipo) {
                case 'img':
                    $resized = !empty($abs) ? self::thumbImg($abs, $id) : null;
                    $thumb   = $resized ?? (!empty($ruta_archivo) ? $ruta_archivo : null);
                    break;
                case 'pdf':
                    $thumb = !empty($abs) ? self::thumbPdf($abs, $id) : null;
                    break;
                case 'doc':
                    $thumb = !empty($abs) ? self::thumbDoc($abs, $id) : null;
                    break;
                case 'vid':
                    $thumb = !empty($abs) ? self::thumbVid($abs, $id) : null;
                    break;
            }
        }

        Recurso::update(['ruta_thumb' => $thumb ?? ''], ['id' => $id]);
    }

    private static function thumbYoutube(string $url): ?string {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s#]+)/', $url, $m)) {
            return 'https://img.youtube.com/vi/' . $m[1] . '/mqdefault.jpg';
        }
        return null;
    }

    private static function thumbPdf(string $abs, int $id): ?string {
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
            $texto = self::extraerTextoPdf($abs);
            if ($texto === '') {
                $texto = str_replace(['_', '-'], ' ', pathinfo($abs, PATHINFO_FILENAME));
            }
            return self::textoAImagen($texto, $dir, $id, 'pdf');
        }

        return null;
    }

    private static function extraerTextoPdf(string $abs): string {
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

    private static function thumbDoc(string $abs, int $id): ?string {
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
            $texto = self::extraerTextoDocx($abs);
            if ($texto !== '') {
                return self::textoAImagen($texto, $dir, $id, $ext);
            }
        }

        if (extension_loaded('gd')) {
            $texto = str_replace(['_', '-'], ' ', pathinfo($abs, PATHINFO_FILENAME));
            return self::textoAImagen($texto, $dir, $id, $ext ?: 'doc');
        }

        return null;
    }

    private static function extraerTextoDocx(string $abs): string {
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

    private static function textoAImagen(string $texto, string $dir, int $id, string $ext = 'doc'): ?string {
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

    private static function thumbImg(string $abs, int $id): ?string {
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

    private static function thumbVid(string $abs, int $id): ?string {
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
}
