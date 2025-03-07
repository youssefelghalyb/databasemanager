<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit77cd54945c3ff61c94aeba9776b0d571
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit77cd54945c3ff61c94aeba9776b0d571', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit77cd54945c3ff61c94aeba9776b0d571', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit77cd54945c3ff61c94aeba9776b0d571::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
