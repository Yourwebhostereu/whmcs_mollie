<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit85570e758e4a11600ab8a47f0fde8b93
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Mollie\\Api\\' => 11,
        ),
        'C' => 
        array (
            'Composer\\CaBundle\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Mollie\\Api\\' => 
        array (
            0 => __DIR__ . '/..' . '/mollie/mollie-api-php/src',
        ),
        'Composer\\CaBundle\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit85570e758e4a11600ab8a47f0fde8b93::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit85570e758e4a11600ab8a47f0fde8b93::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit85570e758e4a11600ab8a47f0fde8b93::$classMap;

        }, null, ClassLoader::class);
    }
}
