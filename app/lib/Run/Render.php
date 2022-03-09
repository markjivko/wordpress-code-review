<?php
/**
 * Potrivit - Test CLI methods
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Run_Render {
    
    /**
     * Purge references
     * 
     * @var boolean
     */
    protected static $_purge = false;
    
    /**
     * Purge references
     * 
     * @return boolean
     */
    public static function getOptionPurge() {
        return self::$_purge;
    }
    
    /**
     * Regenerate listings
     * 
     * @param boolean $purgeReferences (optional) Whether or not to purge references; default <b>null</b>
     * @throws Exception
     */
    public static function run($purgeReferences = false) {
        // Store the options
        self::$_purge = in_array($purgeReferences, ['purge']);
        
        // Start the timer
        $start = microtime(true);
        
        // Get problematic plugins
        $pluginsList = self::_getProblematic();
        Console::log('Found ' . count($pluginsList) . ' problematic plugin' . (1 == count($pluginsList) ? '' : 's') . ' (no title/version)');
        foreach ($pluginsList as $pluginSlug) {
            Console::log(' * ' . $pluginSlug);
            Render_Listing::deletePlugin($pluginSlug, true);
        }
        
        // Get totals
        $numOfPlugins = count(glob(Temp::getPath(Temp::FOLDER_CACHE) . '/*.json'));
        Console::header("Regenerating $numOfPlugins listings...");
        
        // Re-render listings
        Render_Listing::render();
        
        // Log the result
        Console::info('Finished in '. round(microtime(true) - $start, 3) . 's');
    }
    
    /**
     * Find plugins that have no name or incorrect contributors
     * 
     * @return string[] Plugin slugs
     */
    protected static function _getProblematic() {
        $result = [];
        foreach(glob(Temp::getPath(Temp::FOLDER_CACHE) . '/*.json') as $jsonPath) {
            $pluginSlug = basename($jsonPath, '.json');
            
            // Get the cached data
            $pluginData = Cache_Data::get($pluginSlug)->getInfo();
            
            // Plugin data not found or plugin name is incorrect
            if (!is_array($pluginData) || !isset($pluginData[Test_1_About::DATA_PLUGIN_NAME]) 
                || !strlen($pluginData[Test_1_About::DATA_PLUGIN_NAME])
                || !isset($pluginData[Test_1_About::DATA_PLUGIN_VERSION])
                || !strlen($pluginData[Test_1_About::DATA_PLUGIN_VERSION])) {
                $result[] = $pluginSlug;
            }
        }
        return $result;
    }
}

/* EOF */