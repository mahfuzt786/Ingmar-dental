<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3bc16c3b31f99f1d41fdb431fc8bf181
{
    public static $files = array (
        '6124b4c8570aa390c21fafd04a26c69f' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/deep_copy.php',
    );

    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'DeepCopy\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'DeepCopy\\' => 
        array (
            0 => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3bc16c3b31f99f1d41fdb431fc8bf181::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3bc16c3b31f99f1d41fdb431fc8bf181::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit3bc16c3b31f99f1d41fdb431fc8bf181::$classMap;

        }, null, ClassLoader::class);
    }
}
