<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf1e72444ede421a01eb15316b7d1e1b1
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'setasign\\Fpdi\\' => 14,
        ),
        'S' => 
        array (
            'Svg\\' => 4,
            'Sabberworm\\CSS\\' => 15,
        ),
        'P' => 
        array (
            'PhpOffice\\PhpWord\\' => 18,
        ),
        'M' => 
        array (
            'Masterminds\\' => 12,
        ),
        'L' => 
        array (
            'Laminas\\Escaper\\' => 16,
        ),
        'F' => 
        array (
            'FontLib\\' => 8,
            'Fawno\\FPDF\\' => 11,
            'FPDF\\Scripts\\' => 13,
        ),
        'D' => 
        array (
            'Dompdf\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'setasign\\Fpdi\\' => 
        array (
            0 => __DIR__ . '/..' . '/setasign/fpdi/src',
        ),
        'Svg\\' => 
        array (
            0 => __DIR__ . '/..' . '/dompdf/php-svg-lib/src/Svg',
        ),
        'Sabberworm\\CSS\\' => 
        array (
            0 => __DIR__ . '/..' . '/sabberworm/php-css-parser/src',
        ),
        'PhpOffice\\PhpWord\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpoffice/phpword/src/PhpWord',
        ),
        'Masterminds\\' => 
        array (
            0 => __DIR__ . '/..' . '/masterminds/html5/src',
        ),
        'Laminas\\Escaper\\' => 
        array (
            0 => __DIR__ . '/..' . '/laminas/laminas-escaper/src',
        ),
        'FontLib\\' => 
        array (
            0 => __DIR__ . '/..' . '/dompdf/php-font-lib/src/FontLib',
        ),
        'Fawno\\FPDF\\' => 
        array (
            0 => __DIR__ . '/..' . '/fawno/fpdf/src',
        ),
        'FPDF\\Scripts\\' => 
        array (
            0 => __DIR__ . '/..' . '/fawno/fpdf/scripts',
        ),
        'Dompdf\\' => 
        array (
            0 => __DIR__ . '/..' . '/dompdf/dompdf/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Dompdf\\Cpdf' => __DIR__ . '/..' . '/dompdf/dompdf/lib/Cpdf.php',
        'FPDF' => __DIR__ . '/..' . '/fawno/fpdf/fpdf/fpdf.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf1e72444ede421a01eb15316b7d1e1b1::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf1e72444ede421a01eb15316b7d1e1b1::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitf1e72444ede421a01eb15316b7d1e1b1::$classMap;

        }, null, ClassLoader::class);
    }
}
