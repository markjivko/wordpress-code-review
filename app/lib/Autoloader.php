<?php

// Composer autoloader
require_once ROOT . '/vendor/composer/autoload_real.php';

/**
 * Potrivit - Autoloader
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Autoloader {

    /**
     * Autoloader instance
     * 
     * @var Autoloader
     */
    protected static $_instance;

    /**
     * Singleton
     * 
     * @return Autoloader
     */
    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Private constructor
     */
    protected function __construct() {
        // Increase the memory
        ini_set('memory_limit', '4096M');
        ini_set('pcre.backtrack_limit', '1024M');

        // No time limit
        set_time_limit(0);

        // Time limit
        ini_set('max_execution_time', 9999999999);
        ini_set('max_input_time', 9999999999);
        
        // Set the timezone
        date_default_timezone_set('Europe/Bucharest');

        // Composer autoloader
        ComposerAutoloaderInit::getLoader();
        
        // Do nothing
        spl_autoload_register(array($this, '_findClass'));
    }

    /**
     * Locate and include a class by name
     * 
     * @param string $className Class name
     */
    protected function _findClass($className = '') {
        // Prepare the class path
        $classPath = str_replace(' ', '/', ucwords(str_replace('_', ' ', $className)));
        if (file_exists($classFileName = ROOT . '/lib/' . $classPath . '.php')) {
            require_once $classFileName;
        }
    }

}

/* EOF */