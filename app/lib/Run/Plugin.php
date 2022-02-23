<?php
/**
 * Potrivit - Plugin CLI methods
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Run_Plugin {

    /**
     * Sanitize plugin slug
     * 
     * @param &$pluginSlug Plugin slug
     * @throws Exception
     */
    protected static function _sanitizeSlug(&$pluginSlug = null) {
        if (0 === posix_getuid()) {
            throw new Exception('Must run this tool as yourself (not root)');
        }

        // Plugin slug not specified
        if (!is_string($pluginSlug)) {
            throw new Exception('Plugin slug is mandatory');
        }

        // Sanitize the slug
        $pluginSlug = preg_replace('%[^\w\-]+%i', '', strtolower($pluginSlug));

        // Invalid value provided
        if (!strlen($pluginSlug)) {
            throw new Exception('Invalid plugin slug');
        }
    }

    /**
     * Remove all plugins and restore WordPress to a clean install
     */
    public static function purge() {
        Console::log('Plugin: Clean All');
        
        // Make all folders accessible
        shell_exec('find /var/www/wordpress -type d -exec chmod 755 {} +');
        if (0 === posix_getuid()) {
            throw new Exception('Must run this tool as yourself (not root)');
        }

        Console::log(' - Resetting the repo...');
        foreach (glob('/var/www/wordpress/wp-content/plugins/*', GLOB_ONLYDIR) as $pluginPath) {
            shell_exec("rm -rf '$pluginPath'");
        }
        
        // Prepare empty folders for Git
        Folder::markEmptyFolders();
        shell_exec('git -C /var/www/wordpress add -A');
        shell_exec('git -C /var/www/wordpress reset --hard');
        
        // Connect
        $mysqli = new mysqli('localhost', Config::get()->dbUser(), Config::get()->dbPass());
        if ($mysqli->connect_error) {
            throw new Exception($mysqli->connect_error);
        }

        Console::log(' - Resetting the database...');
        $dbName = Config::get()->dbName();

        // Delete all table
        $mysqli->query("DROP DATABASE IF EXISTS `$dbName`;");
        $mysqli->query("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;");

        // Repopulate the database
        $mysqli->select_db($dbName);
        if (is_file($sqlPath = '/var/www/wordpress.sql')) {
            $mysqli->multi_query(file_get_contents($sqlPath));
        }
    }

    /**
     * Install and activate a plugin from the repository
     * 
     * @param string $pluginSlug Plugin slug
     * @throws Exception
     */
    public static function install($pluginSlug = null) {
        Console::log('Plugin: Install');
        self::_sanitizeSlug($pluginSlug);        

        // Get the remote statistics
        $remoteInfo = Cache_Fetch::get($pluginSlug)->information();
        
        // Archive clean-up
        $archivePath = Temp::getPath(Temp::FOLDER_DOWN) . '/' . basename($remoteInfo[Cache_Fetch::STAT_DOWN_URL]);
        !Config::get()->cacheDownload() && is_file($archivePath) && unlink($archivePath);
        
        Console::log(' - Downloading plugin...');
        if (!Config::get()->cacheDownload() || !is_file($archivePath)) {
            passthru('wget -q --show-progress "' . $remoteInfo[Cache_Fetch::STAT_DOWN_URL] . '" -P "' . Temp::getPath(Temp::FOLDER_DOWN) . '"');
        }
        
        // Plugin removed
        if (!is_file($archivePath)) {
            throw new Exception('Plugin not found');
        }
        
        // Store the file size
        Cache_Data::get($pluginSlug)->setArchiveSize(filesize($archivePath));
        
        // Clean-up
        if (is_dir($pluginPath = "/var/www/wordpress/wp-content/plugins/$pluginSlug")) {
            Console::log(' - Cleaning up...');
            shell_exec("rm -rf '$pluginPath'");
        }
        
        Console::log(' - Extracting archive...');
        passthru("unzip -o -qq '$archivePath' -d '/var/www/wordpress/wp-content/plugins'");
        !Config::get()->cacheDownload() && is_file($archivePath) && unlink($archivePath);
        
        // Git folders not allowed
        foreach (Folder::getIterator("/var/www/wordpress/wp-content/plugins/$pluginSlug") as /*@var $item SplFileInfo*/ $item) {
            if ($item->isDir() && '.git' === $item->getBasename()) {
                // Mark the presence of Git folders
                Test_1_About::$gitFolderFound = true;
                
                // Prepare the path
                if (false !== $pluginGitPath = $item->getRealPath()) {
                    Console::log(" - Removing git folder '$pluginGitPath'...");
                    shell_exec("rm -rf '$pluginGitPath'");
                }
            }
        }
        
        // Try to activate
        self::activate($pluginSlug);
    }

    /**
     * Uninstall a plugin
     * 
     * @param string $pluginSlug Plugin slug
     * @throws Exception
     */
    public static function uninstall($pluginSlug = null) {
        Console::log('Plugin: Uninstall');
        self::_sanitizeSlug($pluginSlug); 

        // Uninstall the plugin
        return Api::run('pluginUninstall', array($pluginSlug));
    }
    
    /**
     * Activate an installed plugin
     * 
     * @param string $pluginSlug Plugin slug
     * @throws Exception
     */
    public static function activate($pluginSlug = null) {
        Console::log('Plugin: Activate');
        self::_sanitizeSlug($pluginSlug); 

        // Activate the plugin
        return Api::run('pluginActivate', array($pluginSlug));
    }

    /**
     * Deactivate an installed plugin
     * 
     * @param string $pluginSlug Plugin slug
     * @throws Exception
     */
    public static function deactivate($pluginSlug = null) {
        Console::log('Plugin: Deactivate');
        self::_sanitizeSlug($pluginSlug); 

        // Activate the plugin
        return Api::run('pluginDeactivate', array($pluginSlug));
    }

    /**
     * Get all extra admin pages
     * 
     * @param boolean $silent    (optional) Do not list the items; default <b>false</b>
     * @param boolean $randomize (optional) Randomize the list; default <b>false</b>
     * @throws Exception
     * @return array Associative array of {url} => {title}
     */
    public static function getPages($silent = false, $randomize = false) {
        Console::log('Plugin: Get Pages');

        // Activate the plugin
        list(, $result) = Api::run('pluginGetPages');
        
        // Log the result
        if (!$silent) {
            if (is_array($result) && count($result)) {
                Console::log('Found ' . count($result) . ' new admin page' . (1 === count($result) ? '' : 's'));
                
                // Log each entry
                foreach ($result as $url => $title) {
                    Console::log(' * ' . $title . ' - ' . $url);
                }
                
                // Randomize results
                if ($randomize) {
                    Arrays::shuffle($result);
                }
            } else {
                Console::log('No new admin pages');
            }
        }
        
        // Keep the first 10 (prevents abuse)
        return is_array($result) ? $result : [];
    }
    
    /**
     * Get all extra options
     * 
     * @param boolean $silent    (optional) Do not list the items; default <b>false</b>
     * @param boolean $randomize (optional) Randomize the list; default <b>false</b>
     * @return string[] Numeric array of option names
     */
    public static function getOptions($silent = false, $randomize = false) {
        Console::log('Plugin: Get Options');

        // Activate the plugin
        list(, $result) = Api::run('pluginGetOptions');
        
        // Log the result
        if (!$silent) {
            if (is_array($result) && count($result)) {
                Console::log('Found ' . count($result) . ' new option' . (1 === count($result) ? '' : 's'));
                
                // Log each entry
                foreach ($result as $optionName) {
                    Console::log(' * ' . $optionName);
                }
                
                if ($randomize) {
                    Arrays::shuffle($result);
                }
            } else {
                Console::log('No new options');
            }
        }
        
        // Keep the first 10 (prevents abuse)
        return is_array($result) ? $result : [];
    }
    
    /**
     * Get the tested WordPress version
     * 
     * @return string
     */
    public static function getWpVersion() {
        require '/var/www/wordpress/wp-includes/version.php';
        return $wp_version;
    }
}

/* EOF */