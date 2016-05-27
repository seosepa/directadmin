<?php
/**
 * Autoloader.php
 *
 * @package Directadmin\Codeigniter
 */

/**
 * @author Stefan Konig <github@seosepa.net>
 */

class DA_Autoloader
{
    /**
     * @param string $class_name
     */
    public static function autoload ($class_name)
    {
        if (strpos($class_name, "DA_") === 0)
        {
            $file_name = str_replace("_", "/", $class_name);
            $file_name = realpath(dirname(__FILE__) . "/../{$file_name}.php");

            if ($file_name !== false)
            {
                require $file_name;
            }
        }
    }

    /**
     * @return bool
     */
    public static function register ()
    {
        return spl_autoload_register(array(__CLASS__, "autoload"));
    }

    /**
     * @return bool
     */
    public static function unregister ()
    {
        return spl_autoload_unregister(array(__CLASS__, "autoload"));
    }
}
DA_Autoloader::register();
