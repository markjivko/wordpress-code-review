<?php
/**
 * Potrivit - Batch CLI methods
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Run_Batch {
    
    /**
     * Test new plugins
     * 
     * @param int $itemsTotal (optional) Total number of items
     */
    public static function new($itemsTotal = null) {
        Console::header("Batch: New plugins", '###', '=');
        self::_test(
            self::_getNew(abs((int) $itemsTotal)),
            'New'
        );
    }
    
    /**
     * Test updated plugins
     * 
     * @param int $itemsTotal (optional) Total number of items
     */
    public static function updates($itemsTotal = null) {
        Console::header("Batch: Updated plugins", '###', '=');
        self::_test(
            self::_getUpdates(abs((int) $itemsTotal)), 
            'Updates'
        );
    }
    
    /**
     * Test local plugins, prioritizing those with missing cache data
     * 
     * @param int $itemsTotal (optional) Total number of items
     */
    public static function local($itemsTotal = null) {
        Console::header("Batch: Local plugins", '###', '=');
        self::_test(
            self::_getLocal(abs((int) $itemsTotal)), 
            'Local'
        );
    }
    
    /**
     * Run tests for the provided list of plugins
     * @param string[] $pluginSlugs List of plugin slugs
     * @param string   $batchName   Batch name
     */
    protected static function _test($pluginSlugs, $batchName) {
        do {
            if (!is_array($pluginSlugs)) {
                Console::log("B:$batchName - Invalid plugins list", false);
                break;
            }
            
            // Set the start time
            $batchStart = microtime(true);
            
            // Sanitize the input
            $pluginSlugs = array_values($pluginSlugs);
            $pluginSlugsCount = count($pluginSlugs);
            
            // Go through the list
            foreach ($pluginSlugs as $key => $pluginSlug) {
                Console::header(($key + 1) . "/$pluginSlugsCount. B:$batchName <$pluginSlug>", '##', '=');
                $runPath = dirname(ROOT) . '/run.php';
                passthru("php -f '$runPath' test run $pluginSlug");
            }
            
            // Log the end
            if (!count($pluginSlugs)) {
                Console::info('[' . $batchName . '] none available');
            } else {
                Console::info('Batch finished in ' . number_format(microtime(true) - $batchStart, 3) . 's');
            }
        } while(false);
    }
    
    /**
     * Get local packages
     * 
     * @param int     $itemsTotal Total number of plugins
     * @param boolean $random     (optional) Randomize the list; default <b>true</b>
     * @return string[] Array of  plugin slugs
     */
    protected static function _getLocal($itemsTotal, $random = true) {
        (0 === $itemsTotal) && ($itemsTotal = 50000);
        
        Console::log("Searching for local plugins: $itemsTotal total, " . ($random ? 'randomized' : 'in order'));
        $startTime = microtime(true);
        
        // Get all live packages
        $slugsLive = array_map(
            'basename', 
            glob(
                Config::get()->outputPath() . '/' . Render_Listing::FOLDER_PLUGIN . '/*', 
                GLOB_ONLYDIR
            )
        );
        
        // Get all the cache files
        $slugsCache = $pluginSlugs = array_map(
            function($item) {
                return basename($item, '.json');
            }, 
            glob(Temp::getPath(Temp::FOLDER_CACHE) . '/*.json')
        );
            
        // Randomize entries
        if ($random) {
            shuffle($slugsLive);
            shuffle($slugsCache);
        }
        
        // Missing files
        $slugsMissing = array_diff($slugsLive, $slugsCache);
        
        // Prepare the plugins list
        $result = array_unique(
            array_merge(
                // Prioritize plugins with missing cache data
                $slugsMissing, 
                // Append the rest of the items
                $slugsCache
            )
        );

        Console::log(
            "Found " . count($result) 
            . (
                count($slugsMissing)
                    ? ' (' . count($slugsMissing) . ' missing)' 
                    : ''
            )
            . ' in ' . number_format(microtime(true) - $startTime, 3) . 's'
        );
        return array_slice($result, 0, $itemsTotal);
    }
    
    /**
     * Get new packages
     * 
     * @param int     $itemsTotal Total number of plugins
     * @param boolean $random     (optional) Randomize the list; default <b>true</b>
     * @return string[] Array of plugin slugs
     */
    protected static function _getNew($itemsTotal, $random = true) {
        (0 === $itemsTotal) && ($itemsTotal = 50000);
        
        Console::log("Searching for new plugins: $itemsTotal total, " . ($random ? 'randomized' : 'in order'));
        $startTime = microtime(true);
        
        // Get all the cache files
        $slugsCache = $pluginSlugs = array_map(
            function($item) {
                return basename($item, '.json');
            }, 
            glob(Temp::getPath(Temp::FOLDER_CACHE) . '/*.json')
        );
    
        // Get the cached statuses
        $slugsLiveCache = Cache_Live::getAll();

        // Get all live plugins
        $slugsLive = array_filter(
            array_map(
                function($path) {
                    return trim($path, '\/\\ ');
                }, 
                preg_split(
                    '%[\r\n]+%', 
                    `svn list https://plugins.svn.wordpress.org/`
                )
            ),
            function($slug) use ($slugsLiveCache) {
                // Don't relist
                if (isset($slugsLiveCache[$slug]) && !$slugsLiveCache[$slug]) {
                    return false;
                }
                
                // Validate form
                return preg_match('%^(?:[a-z\d]+\-)*[a-z\d]+$%', $slug);
            }
        );

        // Prepare the final list
        $result = array_diff($slugsLive, $slugsCache);
        
        // Randomize
        if ($random) {
            shuffle($result);
        }
        
        Console::log(
            "Found " . count($result)
            . ' in ' . number_format(microtime(true) - $startTime, 3) . 's'
        );

        return array_slice($result, 0, $itemsTotal);
    }
    
    /**
     * Get updates for local packages
     * 
     * @param int     $itemsTotal   Total number of plugins
     * @param int     $itemsPerPage (optional) Number of plugins to include in the request to the WP API; default <b>1000</b>
     * @param boolean $random       (optional) Randomize the list; default <b>true</b>
     * @return string[] Array of  plugin slugs
     */
    protected static function _getUpdates($itemsTotal, $itemsPerPage = 1000, $random = true) {
        (0 === $itemsTotal) && ($itemsTotal = 50000);
        
        Console::log("Searching for updates: $itemsTotal total, $itemsPerPage/page, " . ($random ? 'randomized' : 'in order'));
        $startTime = microtime(true);
        
        // Get the plugins list
        $pluginFiles = glob(Temp::getPath(Temp::FOLDER_CACHE) . '/*.json') ?? [];
        if ($random) {
            shuffle($pluginFiles);
        }
        $pluginSlugs = array_map(
            function($item) {
                return basename($item, '.json');
            }, 
            $pluginFiles
        );
        
        /**
         * Get updates for plugins in our cache
         * 
         * @param int $offset Plugin cache list offset
         * @param int $size Batch size
         * @return string[]|null Plugin slugs or null when offset out of bounds
         */
        $getUpdates = function($offset, $size) use($pluginSlugs) {
            // Assume out of bounds
            $result = null;
            
            // Prepare the request list
            $requestList = [
                "plugins" => [], 
                "active" => [],
            ];
            
            // Get the plugin files
            foreach (array_slice($pluginSlugs, $offset, $size, true) as $pluginSlug) {
                $pluginData = Cache_Data::get($pluginSlug);
                
                // Valid info provided
                if (count($pluginData->getInfo())) {
                    $requestList['plugins'][$pluginSlug . '/' . $pluginData->getInfo()[Test_1_About::DATA_PLUGIN_FILE]] = [
                        'Version' => $pluginData->getInfo()[Test_1_About::DATA_PLUGIN_VERSION]
                    ];
                }
                
                // Garbage collection
                Cache_Data::gc($pluginSlug);
            }
            
            // Valid list of plugins
            if (count($requestList['plugins'])) {
                $result = [];

                // Initialize the request
                $curlHandle = curl_init('http://api.wordpress.org/plugins/update-check/1.1/');
                curl_setopt_array(
                    $curlHandle,
                    array(
                        CURLOPT_POST           => true,
                        CURLOPT_POSTFIELDS     => http_build_query([
                            'plugins'      => json_encode($requestList),
                            'translations' => '[]',
                            'locale'       => '[]',
                        ]),
                        CURLOPT_VERBOSE        => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FAILONERROR    => true,
                        CURLOPT_CONNECTTIMEOUT => 5,
                        CURLOPT_USERAGENT      => 'WordPress/' . Run_Plugin::getWpVersion() . '; https://' . Config::get()->domainLive(),
                        CURLOPT_HTTPHEADER     => array(
                            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Accept-Language: en-US,en;q=0.5',
                            'Connection: keep-alive',
                            'Upgrade-Insecure-Requests: 1',
                        ),
                    )
                );

                // Get the CURL response
                $response = curl_exec($curlHandle);
                $error = curl_error($curlHandle);
                curl_close($curlHandle);

                if (!$response || strlen($error)) {
                    throw new Exception("CURL failed for 'update-check': $error");
                }

                // Prepare the package updates
                $jsonData = @json_decode($response, true);
                if (is_array($jsonData) && isset($jsonData['plugins'])) {
                    foreach ($jsonData['plugins'] as $pluginData) {
                        $result[] = $pluginData['slug'];
                    }
                }
            }
            return $result;
        };
        
        // Prepare the result
        $result = [];
        $offset = 0;
        do {
            // Get the updates
            if (!is_array($updates = $getUpdates($offset * $itemsPerPage, $itemsPerPage))) {
                break;
            }
            
            // Append the updates
            if (count($updates)) {
                $result = array_unique(
                    array_merge($result, $updates)
                );
            }
            
            // Limit reached
            if (count($result) >= $itemsTotal) {
                break;
            }
            
            // Next batch
            $offset++;
        } while(true);
        
        Console::log(
            "Found " . count($result) . ' update' . (count($result) ? '' : 's') 
            . ' for the ' . count($pluginSlugs) . ' plugins in cache'
            . ' in ' . number_format(microtime(true) - $startTime, 3) . 's'
        );

        return array_slice($result, 0, $itemsTotal);
    }
}

/*EOF*/