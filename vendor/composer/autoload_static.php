<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit47d0240601dfea4fa1309385ea3475e1
{
    public static $prefixLengthsPsr4 = array (
        'K' => 
        array (
            'Kamal\\DummyDataGenerator\\' => 25,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Kamal\\DummyDataGenerator\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit47d0240601dfea4fa1309385ea3475e1::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit47d0240601dfea4fa1309385ea3475e1::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit47d0240601dfea4fa1309385ea3475e1::$classMap;

        }, null, ClassLoader::class);
    }
}
