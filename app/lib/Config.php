<?php
/**
 * Potrivit - Autoloader
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Config {

    /**
     * Data store; created from config/config.ini
     * 
     * @var array
     */
    protected $_data = array();

    /**
     * Configuration object
     * 
     * @var Config
     */
    protected static $_instance = null;

    /**
     * Singleton instance of Config object
     * 
     * @return Config
     */
    public static function get() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * Singleton constructor
     */
    protected function __construct() {
        $this->_data = parse_ini_file(dirname(__FILE__, 2) . '/config/config.ini') ?? array();
    }

    /**
     * Universal getter
     * 
     * @param string $methodName Method name; _ are replaced with .; ex: "version_main" becomes "version.main"
     * @return mixed|null Configuration value or null if none defined
     */
    protected function _get($methodName) {
        // Prepare the data key
        $dataKey = str_replace('_', '.', $methodName);

        // Get the associated value
        return isset($this->_data[$dataKey])
            ? $this->_data[$dataKey]
            : null;
    }

    /**
     * Plugin version
     * 
     * @return string
     */
    public function version() {
        return $this->_get(__FUNCTION__);
    }
    
    /**
     * Store downloaded archives in cache
     * 
     * @return boolean
     */
    public function cacheDownload() {
        return $this->_get(__FUNCTION__);
    }
    
    /**
     * Number of hours to cache fetched resources (SVN logs and CURL requests)
     * 
     * @return int
     */
    public function cacheFetch() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Plugin in production mode
     * 
     * @return boolean
     */
    public function production() {
        return !!$this->_get(__FUNCTION__);
    }

    /**
     * Log level; default <b>info</b>
     * 
     * @return string
     */
    public function logLevel() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Current user
     * 
     * @return string
     */
    public function user() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Current user group
     * 
     * @return string
     */
    public function group() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Test output path
     * 
     * @return string
     */
    public function outputPath() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Domain of the local testing WordPress install (alias of localhost)
     * 
     * @return string
     */
    public function domainWp() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Domain of the local testing WordPress install (alias of localhost)
     * 
     * @return string
     */
    public function domainTest() {
        return $this->_get(__FUNCTION__);
    }
    
    /**
     * Domain of the live static site
     * 
     * @return string
     */
    public function domainLive() {
        return $this->_get(__FUNCTION__);
    }
    
    /**
     * URI for the license
     * 
     * @return string
     */
    public function licenseUri() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Testing website name
     * 
     * @return string
     */
    public function siteName() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Testing website username and password
     * 
     * @return string
     */
    public function siteUser() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Testing website e-mail address
     * 
     * @return string
     */
    public function siteEmail() {
        return $this->_get(__FUNCTION__);
    }
    
    /**
     * UA-*** Google Analytics tag
     * 
     * @return string
     */
    public function googleAnalytics() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Database name
     * 
     * @return string
     */
    public function dbName() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Database username
     * 
     * @return string
     */
    public function dbUser() {
        return $this->_get(__FUNCTION__);
    }

    /**
     * Database password
     * 
     * @return string
     */
    public function dbPass() {
        return $this->_get(__FUNCTION__);
    }

}

/* EOF */