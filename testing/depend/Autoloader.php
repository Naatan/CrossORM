<?php

class Autoloader
{

    /**
     * Run the autoloader (require class file)
     *
     * @param   string          $className
     *
     * @return  void
     */
    public static function run($className)
    {
        $file = BASEDIR . self::getFileName($className);

        if ( ! file_exists($file))
        {
            echo 'File not found: ' . $file . PHP_EOL;
            return false;

        }

        require_once $file;
    }

    /**
     * Get file name based on class name
     *
     * @param   string          $className
     *
     * @return  string
     */
    public static function getFileName($className)
    {
        $fileParts = explode('\\', ltrim($className, '\\'));

        if (false !== strpos(end($fileParts), '_'))
        {
            array_splice($fileParts, -1, 1, explode('_', current($fileParts)));
        }

        return implode(DIRECTORY_SEPARATOR, $fileParts) . '.php';
    }

}

spl_autoload_register(array('Autoloader', 'run'));
