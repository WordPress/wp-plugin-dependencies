<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit96992adf3e5e5eda71d94259c5b3afc1
{
    public static $files = array (
        '98a0c93b23cc12bd6783b5318e594b01' => __DIR__ . '/..' . '/afragen/add-plugin-dependency-api/add-plugin-dependency-api.php',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit96992adf3e5e5eda71d94259c5b3afc1::$classMap;

        }, null, ClassLoader::class);
    }
}
