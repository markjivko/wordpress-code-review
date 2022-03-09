<?php
/**
 * Potrivit - Cache skipped plugins
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Cache_Live {

    /**
     * Associative array of plugin slug => (boolean) Plugin has an "/assets" folder
     * 
     * @var array
     */
    protected static $_isLive = null;
    
    /**
     * Check whether a plugin has assets (it might be live)
     * 
     * @param string $pluginSlug Plugin slug
     * @return boolean True if the plugin is live
     */
    public static function check($pluginSlug) {
        // Load cached data
        self::_load();
        
        // Cache miss
        if (!isset(self::$_isLive[$pluginSlug])) {
            // Prepare the SVN test variables
            $svnOutput = '';
            $svnResult = 0;

            // Try to get the assets
            exec(
                "svn list https://plugins.svn.wordpress.org/$pluginSlug/assets 2>/dev/null", 
                $svnOutput, 
                $svnResult
            );
            
            // Store the result
            self::$_isLive[$pluginSlug] = (0 === $svnResult ? 1 : 0);
            
            // Save the result
            self::_save();
        }
        
        return !!self::$_isLive[$pluginSlug];
    }
    
    /**
     * Mark a plugin as live or offline for future tests
     * 
     * @param string $pluginSlug Plugin slug
     * @param boolean $live      (optional) Mark the plugin as live; default <b>true</b>
     */
    public static function mark($pluginSlug, $live = true) {
        // Load cached data
        self::_load();

        // Mark as skipped
        if (!isset(self::$_isLive[$pluginSlug]) 
            || !!self::$_isLive[$pluginSlug] !== !!$live) {
            Console::info('Marked ' . $pluginSlug . ' as ' . ($live ? 'live' : 'discontinued'));
            self::$_isLive[$pluginSlug] = $live ? 1 : 0;
            self::_save();
        }
    }
    
    /**
     * Get all plugins live status
     * 
     * @return Associative array of plugin slug => (boolean) Plugin has an "/assets" folder
     */
    public static function getAll() {
        return self::_load();
    }
    
    /**
     * Load cached data
     */
    protected static function _load() {
        // Not initialized
        if (!is_array(self::$_isLive)) {
            self::$_isLive = [];

            // Get the file
            if (is_file($filePath = self::_getFile())) {
                $data = @json_decode(file_get_contents($filePath), true);
                if (is_array($data)) {
                    self::$_isLive = $data;
                }
            }
        }

        return self::$_isLive;
    }
    
    /**
     * Save cached data
     */
    protected static function _save() {
        file_put_contents(
            self::_getFile(), 
            json_encode(
                self::_load(), 
                JSON_PRETTY_PRINT
            )
        );
    }
    
    /**
     * Get the path to the cached file
     * 
     * @return string
     */
    protected static function _getFile() {
        if (!is_dir($cacheDir = Temp::getPath(Temp::FOLDER_LIVE))) {
            mkdir($cacheDir, 0777, true);
        }
        
        return $cacheDir . '/live.json';
    }
}

/*EOF*/