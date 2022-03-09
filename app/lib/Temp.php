<?php
/**
 * Potrivit - Temporary file handler
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Temp {
    
    // Folders
    const FOLDER_LIVE         = 'live';
    const FOLDER_LOGS         = 'logs';
    const FOLDER_DOWN         = 'down';
    const FOLDER_IMAGES       = 'images';
    const FOLDER_CACHE        = 'cache';
    const FOLDER_CACHE_REMOTE = 'cache/remote';
    
    /**
     * Allowed folders
     * 
     * @var string[]
     */
    const FOLDERS = [
        self::FOLDER_LIVE,
        self::FOLDER_LOGS,
        self::FOLDER_DOWN,
        self::FOLDER_IMAGES,
        self::FOLDER_CACHE,
        self::FOLDER_CACHE_REMOTE,
    ];
    
    /**
     * Get the path to a temporary folder
     * 
     * @param string $folder Temporary folder 
     * @throws Exception
     */
    public static function getPath($folder) {
        if (!in_array($folder, self::FOLDERS)) {
            throw new Exception('Invalid temporary folder "' . $folder . '"');
        }
        
        // Prepare the data path
        $dataPath = dirname(dirname(ROOT)) . '/potrivit-data';
        if (!is_dir($path = $dataPath . '/' . $folder)) {
            mkdir($path, 0777, true);
        }
        
        return $path;
    }
}

/*EOF*/