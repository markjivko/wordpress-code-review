<?php

class Cache_Fetch {
    
    // Statistics keys
    const STAT_ICON         = 'icon';
    const STAT_MODIFIED     = 'modified';
    const STAT_RATING_VALUE = 'ratingValue';
    const STAT_RATING_COUNT = 'ratingCount';
    const STAT_DOWN_URL     = 'downUrl';
    const STAT_DOWN_ACTIVE  = 'downActive';
    const STAT_DOWN_TOTAL   = 'downTotal';
    
    // Date keys
    const DATE_FIRST = 'dateFirst';
    const DATE_LAST  = 'dateLast';
    
    // Commit keys
    const COMMIT_AUTHORS   = 'commAuthors';
    const COMMIT_COUNT     = 'commCount';
    const COMMIT_DATES     = 'commDates';
    const COMMIT_FREQUENCY = 'commFrequency';
    
    /**
     * Associative array of pluginSlug => Cache_Remote
     * 
     * @var Cache_Fetch[]
     */
    protected static $_instances = array();
    
    /**
     * Plugin slug
     * 
     * @var string
     */
    protected $_slug;
    
    /**
     * Cache remote files
     * 
     * @param string $pluginSlug Plugin slug
     */
    protected function __construct($pluginSlug) {
        // Store the sanitized slug
        $this->_slug = preg_replace('%[^\w\-]+%i', '', $pluginSlug);
    }
    
    /**
     * Get a list of all plugin assets
     * 
     * @param int $cacheHours (optional) Cache life; default <b>null, Config::get()->cacheFetch()</b>
     * @return string[]
     * @throws Exception
     */
    public function assets($cacheHours = null) {
        Console::log(' - Fetching assets...');
        
        $result = array();

        if (
            preg_match_all(
                '%<a[^<>]*?href\s*=\s*[\'"](\w.*?\.\w+)[\'"]%is', 
                $this->_curl(
                    "http://plugins.svn.wordpress.org/{$this->_slug}/assets/",
                    'assets',
                    $cacheHours
                ), 
                $matches
            )
        ) {
            $result = $matches[1];
        }
        
        return $result;
    }
    
    /**
     * Get plugin statistics
     * 
     * @param int $cacheHours (optional) Cache life; default <b>null, Config::get()->cacheFetch()</b>
     * @return array
     * @throws Exception
     */
    public function information($cacheHours = null) {
        Console::log(' - Fetching plugin info...');
        
        // Get the listing page content
        $pageContents = $this->_curl(
            "https://wordpress.org/plugins/{$this->_slug}/",
            'listing',
            $cacheHours
        );
            
        // Get the JSON info
        try {
            $jsonContents = $this->_curl(
                "https://api.wordpress.org/plugins/info/1.0/{$this->_slug}.json?fields=-compatibility,-description,-donate_link,-homepage,-requires,-requires_php,-sections,-short_description,-tags,-tested,-banners",
                'info-json',
                $cacheHours
            );
        } catch (Exception $exc) {
            if (preg_match('%error\s*\:\s*404\b%i', $exc->getMessage())) {
                Render_Listing::deletePlugin($this->_slug, true);
            }
            throw $exc;
        }
        
        // Prepare the Ld+Json data
        $ldJson = null;
        if (
            preg_match(
                '%\bapplication\/ld\+json[^>]*?>\s*(.*?)\s*<\s*\/\s*script\b%ims', 
                $pageContents, 
                $matches
            )
        ) {    
            $ldJson = @json_decode($matches[1], true);
        }

        // Something went wrong
        if (!is_array($ldJson) || !is_array($ldJson[0]) || !isset($ldJson[0]['dateModified']) || !is_string($ldJson[0]['dateModified'])) {
            throw new Exception('Invalid LD+JSON for "' . $this->_slug . '"');
        }
        
        // Prepare the active installs
        $activeInstalls = 0;
        if (preg_match('%\bactive\s+(?:installations|installs)\s*\:(.*?)</%im', $pageContents, $aiMatches)) {
            $activeInstalls = (int) preg_replace(
                array('%\bmillions?\b%i', '%\D+%'), 
                array('000000', ''), 
                strip_tags($aiMatches[1])
            );
        }
            
        // Prepare the JSON data
        $jsonData = @json_decode($jsonContents, true);
        
        // Sanitize the rating
        $ratingValue = isset($ldJson[0]['aggregateRating']) && isset($ldJson[0]['aggregateRating']['ratingValue']) 
                ? $ldJson[0]['aggregateRating']['ratingValue']
                : 5;
        $ratingCount = isset($ldJson[0]['aggregateRating']) && isset($ldJson[0]['aggregateRating']['ratingCount']) 
                ? $ldJson[0]['aggregateRating']['ratingCount']
                : 1;
        
        // Get the data
        return array(
            self::DATE_FIRST        => is_array($jsonData) ? strtotime($jsonData['added']) : null,
            self::DATE_LAST         => is_array($jsonData) ? strtotime($jsonData['last_updated']) : null,
            self::STAT_ICON         => isset($ldJson[0]['image'][0]) 
                ? preg_replace(
                    '%.*\/(\w.*?\.\w+)(\?.*)%',
                    '$1',
                    $ldJson[0]['image'][0]
                )
                : null,
            self::STAT_MODIFIED     => strtotime($ldJson[0]['dateModified']),
            self::STAT_RATING_VALUE => $ratingValue,
            self::STAT_RATING_COUNT => $ratingCount,
            self::STAT_DOWN_URL     => trim($ldJson[0]['downloadUrl']),
            self::STAT_DOWN_ACTIVE  => $activeInstalls,
            self::STAT_DOWN_TOTAL   => $ldJson[0]['interactionStatistic']['userInteractionCount'],
        );
    }
    
    /**
     * Get commit statistics on this plugin
     * 
     * @param int $cacheHours (optional) Cache life; default <b>null, Config::get()->cacheFetch()</b>
     */
    public function commits($cacheHours = null) {
        Console::log(' - Fetching SVN Logs...');
        if (null === $cacheHours) {
            $cacheHours = Config::get()->cacheFetch();
        }
        
        // Sanitize the cache life
        $cacheHours = abs((int) $cacheHours);
        
        // Prepare the cache folder
        if (!is_dir($cacheFolder = Temp::getPath(Temp::FOLDER_CACHE_REMOTE))) {
            mkdir($cacheFolder, 0777, true);
        }
        
        // Prepare the file path
        $filePath = $cacheFolder . '/' . $this->_slug . '.commits.html';
        $svnLog = '';
        do {
            // File already download in the last 30 days
            if ($cacheHours > 0 && is_file($filePath) && (time() - filemtime($filePath) < $cacheHours * 3600)) {
                $svnLog = file_get_contents($filePath);
                break;
            }
            
            // Get the result
            $command = "svn log -q --stop-on-copy https://plugins.svn.wordpress.org/{$this->_slug}";
            $svnLog = shell_exec($command);
            
            // Store the result
            file_put_contents($filePath, $svnLog);
        } while(false);
        
        // Prepare the result
        $result = [
            self::COMMIT_AUTHORS   => [],
            self::COMMIT_DATES     => [],
            self::COMMIT_COUNT     => 1,
            self::COMMIT_FREQUENCY => 365
        ];
        
        // Valid SVN Log format
        if (preg_match_all('%^\s*r\d+\s*\|\s*(.*?)\s*\|\s*(\d{4}\-\d{2}\-\d{2}).*?\(%ims', $svnLog, $matches)) {
            // Store the number of commits
            $result[self::COMMIT_COUNT] = count($matches[0]);
            
            // Store the authors
            $topAuthors = array_filter(
                array_map(
                    function($item) use($result) {
                        $result = round(100 * ($item + 1) / $result[self::COMMIT_COUNT], 2);
                        return $result > 100 ? 100 : $result;
                    },
                    array_count_values($matches[1])
                ), function($item) {
                    return $item >= 1;
                }
            );
            unset($topAuthors['plugin-master']);
            arsort($topAuthors);
            
            // Store top 5 authors only
            $result[self::COMMIT_AUTHORS] = array_slice($topAuthors, 0, 5);
            
            // Get the dates in asc order
            $timestamps = array_map('strtotime', $matches[2]);
            sort($timestamps);
            
            // Prepare the current time
            $currentTime = time();
            $datesCount = array_count_values(
                array_filter(
                    $timestamps, 
                    function($time) use($currentTime) {
                        return $currentTime - $time <= 364 * 86400;
                    }
                )
            );
            $firstTimestamp = current(array_keys($datesCount));
            $result[self::COMMIT_DATES] = [
                date('Ymd', $firstTimestamp),
                array_combine(
                    array_map(
                        function($time) use($firstTimestamp) {
                            return round(($time - $firstTimestamp) / 86400, 0);
                        }, 
                        array_keys($datesCount)
                    ), 
                    $datesCount
                )
            ];
            
            // Store the commit frequency
            $result[self::COMMIT_FREQUENCY] = count($timestamps) >= 2
                ? round(
                    ($timestamps[count($timestamps) - 1] - $timestamps[0]) / count($timestamps) / 86400, 
                    2
                )
                : 0;
        }
        
        return $result;
    }
    
    /**
     * Get a Singleton instance of Cache_Remote
     * 
     * @return Cache_Fetch
     */
    public static function get($pluginSlug) {
        if (!isset(self::$_instances[$pluginSlug])) {
            self::$_instances[$pluginSlug] = new static($pluginSlug);
        }
        
        return self::$_instances[$pluginSlug];
    }
    
    /**
     * Garbage collector - remove the instance stored in memory
     * 
     * @param string $pluginSlug
     */
    public static function gc($pluginSlug) {
        unset(self::$_instances[$pluginSlug]);
    }
    
    /**
     * Get the HTML from any url
     * 
     * @param string $url       URL to download
     * @param string $cacheId   Cache ID
     * @param int    $cacheHours (optional) Cache life; default <b>null, Config::get()->cacheFetch()</b>
     * @return string
     * @throws Exception
     */
    protected function _curl($url, $cacheId, $cacheHours = null) {
        if (null === $cacheHours) {
            $cacheHours = Config::get()->cacheFetch();
        }
        
        // Sanitize the cache life
        $cacheHours = abs((int) $cacheHours);
        
        // Prepare the cache folder
        if (!is_dir($cacheFolder = Temp::getPath(Temp::FOLDER_CACHE_REMOTE))) {
            mkdir($cacheFolder, 0777, true);
        }
        
        // Prepare the file path
        $filePath = $cacheFolder . '/' . $this->_slug . '.' . $cacheId. '.html';
        
        $result = '';
        do {
            // File already download in the last 30 days
            if ($cacheHours > 0 && is_file($filePath) && (time() - filemtime($filePath) < $cacheHours * 3600)) {
                $result = file_get_contents($filePath);
                break;
            }
            
            // Initialize the request
            $ch = curl_init($url);

            curl_setopt_array(
                $ch,
                array(
                    CURLOPT_VERBOSE        => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FAILONERROR    => true,
                    CURLOPT_USERAGENT      =>'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0',
                    CURLOPT_ENCODING       =>'gzip, deflate',
                    CURLOPT_HTTPHEADER     => array(
                        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'Accept-Language: en-US,en;q=0.5',
                        'Connection: keep-alive',
                        'Upgrade-Insecure-Requests: 1',
                    ),
                )
            );

            $result = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if (!$result || strlen($error)) {
                throw new Exception("CURL failed for '$url': $error");
            }
            
            file_put_contents($filePath, $result);
        } while (false);
        
        return $result;
    }
    
    /**
     * Remove fetched files
     */
    public function clean() {
        if (count($remoteFiles = glob(Temp::getPath(Temp::FOLDER_CACHE_REMOTE) . '/' . $this->_slug . '.*.html'))) {
            foreach ($remoteFiles as $remoteFile) {
                is_file($remoteFile) && unlink($remoteFile);
            }
        }
    }
}

/*EOF*/