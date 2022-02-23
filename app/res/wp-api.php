<?php

/**
 * ThemeWarlock-WordPress API - This file should not be used directly
 * It is meant to be injected into the WordPress website, executed via a POST request and deleted immediately
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * No cookie required for our admin
 * 
 * @return boolean
 */
function wp_validate_auth_cookie() {
    return true;
}

// Access to the current user
global $current_user, $pagenow, $_wp_submenu_nopriv;

// Set the environment
$current_user = json_decode(json_encode(array('ID' => 1)));
$pagenow = 'admin.php';
$_wp_submenu_nopriv = array();

do {
    // Don't load admin for fresh installs
    if (isset($_POST) && isset($_POST['method']) && 'siteInstall' === $_POST['method']) {
        break;
    }

    /** WordPress Administration Bootstrap */
    require_once (dirname(__FILE__) . '/admin.php');
} while(false);

class Potrivit_Wp_Api {
    
    /**
     * Known WordPress admin menu pages
     */
    protected $_knownPages = [];
    
    /**
     * Known WordPress options
     */
    protected $_knownOptions = [];
    
    /**
     * Potrivit API Instance
     * 
     * @var Potrivit_Wp_Api 
     */
    protected static $_instance;

    /**
     * WordPress Temporary API
     * 
     * @global type $current_user
     */
    protected function __construct() {
        do {
            // Don't init wp-admin if site is not yet installed
            if (!is_file('/var/www/wordpress/wp-config.php')) {
                break;
            }
            
            // Known pages cache not built
            if (!is_file($getPagesCache = '/var/www/wp-admin.json')) {
                file_put_contents(
                    $getPagesCache, 
                    json_encode(
                        [
                            array_keys($this->pluginGetPages()),
                            array_keys(wp_load_alloptions())
                        ],
                        JSON_PRETTY_PRINT
                    )
                );
            }

            // Get known WordPress Admin area pages
            if (!count($this->_knownPages) || !count($this->_knownOptions)) {
                list($this->_knownPages, $this->_knownOptions) = json_decode(
                    file_get_contents($getPagesCache), 
                    true
                );
            }
        } while(false);
    }

    /**
     * WordPress Temporary API
     * 
     * @return Potrivit_Wp_Api
     */
    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Try to execute the requested method
     */
    protected function _executeMethod() {
        if (!isset($_POST)) {
            throw new Exception('No POST data provided');
        }
        if (!isset($_POST['method'])) {
            throw new Exception('No method provided');
        }

        // Get the method name
        $methodName = trim($_POST['method']);

        // Validate the method
        if (!method_exists($this, $methodName)) {
            throw new Exception('Method "' . $methodName . '" not implemented');
        }

        // Get the method arguments
        $methodArguments = isset($_POST['arguments']) ? (array) $_POST['arguments'] : array();

        // Prepare the result
        return call_user_func_array(array($this, $methodName), $methodArguments);
    }

    /**
     * Visit a page with CURL
     * 
     * @param string $url  URL to visit
     * @param array  $post (optional) POST Data
     */
    protected function _visit($url, $post = array()) {
        // User agent
        $userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:90.0) Gecko/20100101 Firefox/90.0';

        // Prepare the headers
        $curlHeaders = array (
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent: ' . $userAgent,
            'Cache-Control: no-cache',
        );

        // Prepare the options
        $options = array(
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => http_build_query($post),
            CURLOPT_USERAGENT       => $userAgent,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false,
            CURLOPT_HTTPHEADER      => $curlHeaders,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_ALL,
            CURLOPT_AUTOREFERER     => true,
            CURLOPT_CONNECTTIMEOUT  => 5,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_URL             => $url,
        );

        // Initialize the CURL
        $ch = curl_init();

        // Set the options
        curl_setopt_array($ch, $options);

        // Get the result
        $curlResult = curl_exec($ch);
        
        // Close
        curl_close($ch);

        // All went well
        return $curlResult;
    }

    /**
     * Install the site
     */
    public function siteInstall() {
        require_once '{ROOT}/lib/Config.php';

        // Prepare database credentials
        $dbName = Config::get()->dbName();
        $dbUser = Config::get()->dbUser();
        $dbPass = Config::get()->dbPass();

        // Prepare the db object
        $mysqli = new mysqli('localhost', $dbUser, $dbPass);

        // Recreate the database
        if (!$mysqli->connect_error) {            
            $mysqli->query("DROP DATABASE IF EXISTS `$dbName`;");
            $mysqli->query("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;");
        }

        // Prepare the configuration file
        $this->_visit(
            'http://' . Config::get()->domainWp() . '/wp-admin/setup-config.php?step=2', 
            array(
                'dbname'  => $dbName,
                'uname'   => $dbUser,
                'pwd'     => $dbPass,
                'dbhost'   => 'localhost',
                'prefix'   => 'wp_',
                'language' => '',
            )
        );

        // Start the DB install
        ob_start();
        require_once 'upgrade.php';
        wp_install(
            Config::get()->siteName(), 
            Config::get()->siteUser(), 
            Config::get()->siteEmail(), 
            1, 
            null, 
            wp_slash(Config::get()->siteUser()), 
            'en_US'
        );
        ob_end_clean();
        
        // Enable debugging and logging
        $config = file_get_contents($configPath = dirname(__DIR__) . '/wp-config.php');
        $configLogging = <<<'LOG'
$potrivitSsgStart = microtime(true);
register_shutdown_function(function($startTime) {
    file_put_contents(
        '/var/www/access.log',
        json_encode([
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            round(microtime(true) - $startTime, 5),
            memory_get_peak_usage(),
            error_get_last()
        ]) . PHP_EOL,
        FILE_APPEND
    );
}, $potrivitSsgStart);
set_error_handler(function($errNo, $errStr, $errFile, $errLine) use ($potrivitSsgStart){
    file_put_contents(
        '/var/www/access.log',
        json_encode([
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            round(microtime(true) - $potrivitSsgStart, 5),
            memory_get_peak_usage(),
            [
                'type'    => $errNo,
                'message' => $errStr,
                'file'    => $errFile,
                'line'    => $errLine
            ]
        ]) . PHP_EOL,
        FILE_APPEND
    );
});
LOG;
        file_put_contents(
            $configPath,
            preg_replace(
                array(
                    "%['\"]WP_DEBUG['\"]\s*,\s*false\b%i",
                    '%<\?php%'
                ),
                array(
                    "'WP_DEBUG', true",
                    '<?php' . PHP_EOL . $configLogging . PHP_EOL
                ),
                $config
            )
        );
    }

    /**
     * Enable the theme
     * 
     * @param string $themeName Theme name
     * @return boolean
     * @throws Exception
     */
    public function themeEnable($themeName = null) {
        // Theme name not provided
        if (null == $themeName) {
            throw new Exception('Theme name is mandatory');
        }

        /* @var $theme WP_Theme */
        $theme = wp_get_theme($themeName);

        // All done
        if (!is_object($theme) || !$theme->exists()) {
            throw new Exception('Theme "' . $themeName . '" does not exist');
        }

        // Get the current theme
        $currentTheme = wp_get_theme();

        // Switch the theme
        if ($currentTheme->get_stylesheet() != $theme->get_stylesheet()) {
            switch_theme($theme->get_stylesheet());
        }
    }

    /**
     * Uninstall a plugin
     * 
     * @param string $pluginSlug Plugin slug
     * @return boolean|null True if a plugin's uninstall.php file has been found and included. Null otherwise.
     * @throws Exception
     */
    public function pluginUninstall($pluginSlug = null) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        
        // Prepare the plugin data
        $pluginFound = false;
        foreach (get_plugins() as $pluginKey => $pluginData) {
            if (0 === strpos($pluginKey, "$pluginSlug/")) {
                $pluginFound = true;
                break;
            }
        }

        // Plugin not found
        if (!$pluginFound) {
            throw new Exception('Plugin not found');
        }

        // Activate_plugin
        echo 'Uninstalling plugin "' . $pluginData['Name'] . '" by ' . $pluginData['Author'];
        deactivate_plugins($pluginKey);
        return delete_plugins([$pluginKey]);
    }
    
    /**
     * Activate a WordPress plugin
     * 
     * @param string $pluginSlug Plugin slug
     * @return boolean
     * @throws Exception
     */
    public function pluginActivate($pluginSlug = null) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        
        // Prepare the plugin data
        $pluginFound = false;
        foreach (get_plugins() as $pluginKey => $pluginData) {
            if (0 === strpos($pluginKey, "$pluginSlug/")) {
                $pluginFound = true;
                break;
            }
        }

        // Plugin not found
        if (!$pluginFound) {
            throw new Exception('Plugin not found');
        }

        // Activate_plugin
        echo 'Activated plugin "' . $pluginData['Name'] . '" by ' . $pluginData['Author'];
        return null === activate_plugin($pluginKey);
    }
    
    /**
     * Deactivate a WordPress plugin
     * 
     * @param string $pluginSlug Plugin slug
     * @return boolean
     * @throws Exception
     */
    public function pluginDeactivate($pluginSlug = null) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        
        // Prepare the plugin data
        $pluginFound = false;
        foreach (get_plugins() as $pluginKey => $pluginData) {
            if (0 === strpos($pluginKey, "$pluginSlug/")) {
                $pluginFound = true;
                break;
            }
        }

        // Plugin not found
        if (!$pluginFound) {
            throw new Exception('Plugin not found');
        }

        // Activate_plugin
        echo 'Deactivated plugin "' . $pluginData['Name'] . '" by ' . $pluginData['Author'];
        deactivate_plugins($pluginKey);
    }
    
    /**
     * Get extra options
     * 
     * @return array
     */
    public function pluginGetOptions() {
        return array_filter(
            array_diff(
                array_keys(wp_load_alloptions()),
                $this->_knownOptions
            ),
            function($option) {
                return 0 !== strpos($option, '_transient')
                    && !in_array($option, ['finished_updating_comment_type', 'recently_activated']);
            }
        );
    }
    
    /**
     * Get admin pages created by this plugin
     * 
     * @return array
     * @throws Exception
     */
    public function pluginGetPages() {
        global $submenu;
        $result = [];

        // Go through the menu items
        foreach ($submenu as $menuType => $menuItems) {
            foreach ($menuItems as $menuItem) {
                // Ignore remote links
                if (preg_match('%^(?:\/\/|\w+\:\/\/)%i', $menuItem[2])) {
                    continue;
                }
                
                // Prepare the URL
                $menuUrl = '/wp-admin/' 
                    . (
                        preg_match('%^([\w\-]+\.php)\b%i', $menuItem[2])
                            ? $menuItem[2]
                            : (
                                preg_match('%^([\w\-]+\.php)\b%i', $menuType)
                                    ? ($menuType . (false !== strpos($menuType, '?') ? '&' : '?') . 'page=' . $menuItem[2])
                                    : ('admin.php?page=' . $menuItem[2])
                            )
                    );
                
                // Not one of the default WP pages
                if (in_array($menuUrl, $this->_knownPages)) {
                    continue;
                }
                
                // Store the menu URL and title
                $result[$menuUrl] = trim(
                    html_entity_decode(
                        strip_tags(
                            $menuItem[0]
                        )
                    )
                );
            }
        }
        
        return $result;
    }

    /**
     * Run the tool
     */
    public function run($local = false) {
        // Prepare the result
        $status = true;
        $result = null;

        // Start the buffer
        ob_start();
        try {
            $result = $this->_executeMethod();
        } catch (Exception $ex) {
            $result = $ex->getMessage();
            $status = false;
        }

        // Positive result
        if (null === $result) {
            $result = true;
        }

        // Get the output
        $content = ob_get_clean();

        // Prepare the data
        $data = array(
            'status' => $status,
            'result' => $result,
            'content' => $content,
        );

        // Local mode
        if ($local) {
            return $data;
        }

        // All done
        echo json_encode($data);
    }

}

// Execute the request
Potrivit_Wp_Api::getInstance()->run();