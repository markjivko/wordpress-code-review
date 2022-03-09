<?php
/**
 * Potrivit - Render/Listing/JSON Search
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Render_Listing_JsonSearch {
    
    /**
     * Prepare the JSON Search files
     */
    public static function run() {
        $jsonDir = Config::get()->outputPath() . '/' . Render_Listing::FOLDER_MAIN . '/json';
        if (!is_dir($jsonDir)) {
            mkdir($jsonDir, 0755, true);
        } else {
            shell_exec("rm -rf '$jsonDir/*'");
        }
        
        // Prepare the cache
        $letters = array();
        foreach (Cache_Search::getAll() as $pluginSlug) {
            if (!isset($letters[$pluginSlug[0]])) {
                $letters[$pluginSlug[0]] = [];
            }
            $letters[$pluginSlug[0]][] = $pluginSlug;
        }
        
        // Store the JSON files
        foreach ($letters as $letter => $pluginSlugs) {
            file_put_contents(
                $jsonDir . '/' . $letter . '.json', 
                json_encode($pluginSlugs)
            );
        }
    }
}

/*EOF*/