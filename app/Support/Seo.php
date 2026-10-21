<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Seo
{

    private const STOP = [
        'the', 'and', 'for', 'that', 'with', 'this', 'from', 'have', 'your', 'will', 'are', 'was', 'were',
        'been', 'not', 'but', 'you', 'all', 'can', 'has', 'had', 'its', 'our', 'their', 'they', 'them',
        'what', 'when', 'which', 'who', 'how', 'why', 'about', 'into', 'than', 'then', 'there', 'also',
        'more', 'most', 'some', 'such', 'very', 'just', 'like', 'over', 'only', 'other', 'these', 'those',
        'would', 'could', 'should',
    ];

 
    public static function title(string $title): string
    {
        $title = trim(preg_replace('/\s+/u', ' ', strip_tags($title)));
        $withSite = $title . ' | ' . config('seo.site_name');

        if (mb_strlen($withSite) <= 60) {
            return $withSite;                 // "My Trip to Ella | MyBlog"
        }

        return self::cut($title, 60);       
    }


    public static function description(string $plain, string $fallback = ''): string
    {
        $plain = trim($plain) !== '' ? trim($plain) : trim($fallback);

        if (mb_strlen($plain) > 155) {
            $slice = mb_substr($plain, 0, 155);

            if (preg_match('/^(.{80,}[.!?])(\s|$)/us', $slice, $m)) {
                return $m[1];
            }
        }

        return self::cut($plain, 155);
    }


    public static function keywords(string $title, string $plain, array $tags = [], ?string $category = null, int $max = 10): string
    {
        $out = [];

        $add = function (string $word) use (&$out, $max) {
            $word = mb_strtolower(trim($word));
            if ($word !== '' && count($out) < $max && ! in_array($word, $out, true)) {
                $out[] = $word;
            }
        };

        foreach ($tags as $tag) {
            $add($tag);
        }
        if ($category) {
            $add($category);
        }



        $freq = [];
        $count = function (string $text, int $weight) use (&$freq) {
            preg_match_all('/\p{L}[\p{L}\p{M}\p{N}\-]{3,}/u', mb_strtolower($text), $m);
            foreach ($m[0] as $w) {
                if (! in_array($w, self::STOP, true)) {
                    $freq[$w] = ($freq[$w] ?? 0) + $weight;
                }
            }
        };
        $count($title, 3);
        $count($plain, 1);

        arsort($freq);                      
        foreach (array_keys($freq) as $word) {
            $add((string) $word);
        }


        $text = implode(', ', $out);
        while (mb_strlen($text) > 255 && $out) {
            array_pop($out);
            $text = implode(', ', $out);
        }

        return $text;
    }

  
    public static function image(?string $featured, ?string $old = null): ?string
    {

        if ($old && str_starts_with($old, 'meta/')) {
            Storage::disk('public')->delete($old);
        }

        if (! $featured || ! extension_loaded('gd')) {
            return null;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($featured)) {
            return null;
        }

        $full = $disk->path($featured);
        $info = @getimagesize($full);                   


        if (! $info || $info[0] * $info[1] > 25_000_000) {
            return null;
        }

        $src = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($full),
            IMAGETYPE_PNG => @imagecreatefrompng($full),
            IMAGETYPE_GIF => @imagecreatefromgif($full),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($full) : false,
            default => false,
        };
        if (! $src) {
            return null;
        }


        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $angle = [3 => 180, 6 => -90, 8 => 90][@exif_read_data($full)['Orientation'] ?? 1] ?? 0;
            if ($angle) {
                $src = imagerotate($src, $angle, 0);
            }
        }

        $w = imagesx($src);
        $h = imagesy($src);

        // "cover" crop: 1200x630 පිරෙන්න scale කරලා, වැඩිපුර කොටස් මැදින් කපනවා
        $targetW = 1200;
        $targetH = 630;
        $scale = max($targetW / $w, $targetH / $h);
        $cropW = (int) round($targetW / $scale);
        $cropH = (int) round($targetH / $scale);
        $srcX = (int) floor(($w - $cropW) / 2);
        $srcY = (int) floor(($h - $cropH) / 2);

        $dst = imagecreatetruecolor($targetW, $targetH);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));   // PNG transparent කොටස් සුදු කරනවා
        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $targetW, $targetH, $cropW, $cropH);

        ob_start();
        imagejpeg($dst, null, 85);                                        // quality 85
        $jpg = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        $path = 'meta/' . Str::random(32) . '.jpg';
        $disk->put($path, $jpg);

        return $path;
    }


    private static function cut(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max);
        $space = mb_strrpos($cut, ' ');

        if ($space !== false && $space > $max * 0.6) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, " ,;:.-–—") . '…';
    }
}