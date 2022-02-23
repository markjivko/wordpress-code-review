<?php
/**
 * Potrivit - Cache Searc Utility
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Cache_Search {
    
    const ORDER_RATING = 'rating';
    const ORDER_SIZE   = 'size';
    const ORDER_ACTIVE = 'active';
    
    /**
     * Index of tags and contributors
     * 
     * @var array|null
     */
    protected static $_cacheIndex = null;
    
    /**
     * Ordered list of all known plugins
     * 
     * @var string[]|null
     */
    protected static $_cacheFiles = null;

    // Index keys
    const KEY_INDEX_TAGS         = 't';
    const KEY_INDEX_CONTRIBUTORS = 'c';
    
    /**
     * Get all the files in our cache
     * 
     * @return string[]
     */
    public static function getAll() {
        self::_buildCache();
        return self::$_cacheFiles;
    }
    
    /**
     * Fetch recently tested plugins
     * 
     * @param int $number Number of plugins
     * @return Cache_Data[] Associative list of plugins
     */
    public static function getRecent($number) {
        $result = [];
        $number = abs((int) $number);
        
        // Get the JSON files
        $pluginSlugTimes = [];
        foreach(glob(Temp::getPath(Temp::FOLDER_CACHE) . '/*.json') as $jsonPath) {
            $pluginSlugTimes[basename($jsonPath, '.json')] = @filemtime($jsonPath);
        }
        arsort($pluginSlugTimes);
        
        // Prepare the slugs only
        $pluginSlugs = array_keys($pluginSlugTimes);
        
        // Go through the list
        for ($i = 1; $i <= min($number, count($pluginSlugs)); $i++) {
            $result[$pluginSlugs[$i - 1]] = Cache_Data::get($pluginSlugs[$i - 1]);
        }
        return $result;
    }
    
    /**
     * Fetch plugins by specific parameters
     * 
     * @param string  $orderBy    (optional) Order by, see Cache_Search::ORDER_*; default <b>Cache_Search::ORDER_RATING</b>
     * @param int     $number     (optional) Number of plugins; default <b>12</b>
     * @param boolean $orderAsc   (optional) Order ascending; default <b>true</b>
     * @param int     $maxAgeDays (optional) Include only the plugins updated in the last N days; default <b>180</b>
     * @return Cache_Data[] Associative list of plugins
     */
    public static function getGroup($orderBy = self::ORDER_RATING, $number = 12, $orderAsc = true, $maxAgeDays = 180) {
        $time = time();
        $result = [];
        $number = abs((int) $number);
        $orderAsc = !!$orderAsc;
        $maxAgeDays = abs((int) $maxAgeDays);
        
        // Prepare the plugin slugs
        $pluginSlugs = [];
        foreach(glob(Temp::getPath(Temp::FOLDER_CACHE) . '/*.json') as $jsonPath) {
            $pluginSlug = basename($jsonPath, '.json');
            $pluginStats = Cache_Data::get($pluginSlug)->getInfo()[Test_1_About::DATA_PLUGIN_STATS];
            
            // Found the last modified date
            if (is_array($pluginStats) && isset($pluginStats[Cache_Fetch::DATE_LAST])
                && null !== $pluginStats[Cache_Fetch::DATE_LAST]) {
                if ($time - $pluginStats[Cache_Fetch::DATE_LAST] < $maxAgeDays * 86400) {
                    switch ($orderBy) {
                        case self::ORDER_SIZE:
                            $pluginSlugs[$pluginSlug] = (int) Cache_Data::get($pluginSlug)->getArchiveSize();
                            break;
                        
                        case self::ORDER_ACTIVE:
                            if (isset($pluginStats[Cache_Fetch::STAT_DOWN_ACTIVE]) && $pluginStats[Cache_Fetch::STAT_DOWN_ACTIVE] > 0) {
                                $pluginSlugs[$pluginSlug] = (int) $pluginStats[Cache_Fetch::STAT_DOWN_ACTIVE];
                            }
                            break;
                        
                        case self::ORDER_RATING:
                            $pluginSlugs[$pluginSlug] = Cache_Data::get($pluginSlug)->getRating()[0];
                            break;
                    }
                }
            }
        }
        
        // Order the results
        if ($orderAsc) {
            asort($pluginSlugs);
        } else {
            arsort($pluginSlugs);
        }
        
        // Prepare the slugs only
        $pluginSlugs = array_keys($pluginSlugs);
        
        // Go through the list
        for ($i = 1; $i <= min($number, count($pluginSlugs)); $i++) {
            $result[$pluginSlugs[$i - 1]] = Cache_Data::get($pluginSlugs[$i - 1]);
        }
        return $result;
    }
    
    /**
     * Fetch random plugins
     * 
     * @param int $number Number of plugins
     * @return Cache_Data[] Associative list of plugins
     */
    public static function getRandom($number) {
        $result = [];
        $number = abs((int) $number);
        
        // Build siblings cache
        self::_buildCache();
        
        // Prepare the random items
        for ($i = 1; $i <= min($number, count(self::$_cacheFiles));) {
            $index = mt_rand(0, count(self::$_cacheFiles) - 1);
            
            // Unique index
            if (!isset($result[self::$_cacheFiles[$index]])) {
                $result[self::$_cacheFiles[$index]] = Cache_Data::get(self::$_cacheFiles[$index]);
                $i++;
            }
        }
        
        return $result;
    }
    
    /**
     * Get similar plugins:
     * <ul>
     * <li>First count/2 are random "more from author"</li>
     * <li>Next count/2 are random "by tags"</li>
     * <li>If no results, populate with random plugins</li>
     * </ul>
     * 
     * @param string $pluginSlug Plugin slug
     * @param int    $count      Number of results; minimum 2; default <b>6</b>
     * @return Cache_Data[] Associative list of plugins
     */
    public static function getSimilar($pluginSlug, $count = 6) {
        $result = [];
        
        // Sanitize count
        $count = abs((int) $count);
        
        // Get plugin data
        $pluginData = Cache_Data::get($pluginSlug);
        
        // Plugin found
        if ($count > 1 && count($pluginData->getInfo())) {
            // More by same author(s)
            $result = count($pluginData->getAuthors())
                ? self::getByContributors(
                    $pluginData->getAuthors(),
                    $pluginData->getSlug(),
                    $count / 2,
                    true
                )
                : [];
            
            // Excluded slugs
            $excludedSlugs = array_keys($result);
            $excludedSlugs[] = $pluginSlug;
            
            // More with the same tag(s)
            $result += count($pluginData->getTags())
                ? self::getByTags(
                    $pluginData->getTags(),
                    $excludedSlugs,
                    $count - count($result),
                    true
                )
                : [];
        
            // Random filler needed
            if (count($result) < $count) {
                $random = self::getRandom($count);
                $itemCount = $count - count($result);
                
                // Append random items (if any new ones)
                while ($itemCount >= 1 && count($random)) {
                    /*@var $item Cache_Data*/
                    $item = array_shift($random);
                    
                    // Store unique items
                    if ($pluginSlug !== $item->getSlug() && !isset($result[$item->getSlug()])) {
                        $result[$item->getSlug()] = $item;
                        $itemCount--;
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get a plugin's siblings i.e. plugins before and after this one
     * 
     * @param string $pluginSlug Plugin slug
     * @return Cache_Data[]|null Numeric array of 2 elements,
     * the previous and the next plugin; null if plugin does not exist
     */
    public static function getSiblings($pluginSlug) {
        $result = null;
        
        do {
            $cacheData = Cache_Data::get($pluginSlug);
            if (!is_file($cacheData->getPath())) {
                break;
            }
            
            // Build siblings cache
            self::_buildCache();
            
            // Store the file count
            $fileCount = count(self::$_cacheFiles);
            
            // Get the file index
            $index = array_search($cacheData->getSlug(), self::$_cacheFiles);

            // Store the result
            $result = array(
                Cache_Data::get(
                    0 === $index 
                        ? self::$_cacheFiles[$fileCount - 1]
                        : self::$_cacheFiles[$index - 1],
                ),
                Cache_Data::get(
                    $fileCount - 1 === $index
                        ? self::$_cacheFiles[0]
                        : self::$_cacheFiles[$index + 1],
                ),
            );
        } while(false);
        
        return $result;
    }
    
    /**
     * Search by contributors
     * 
     * @param string|string[] $contributors Plugin contributor(s)
     * @param string          $exclSlug     (optional) Plugin(s) excluded from the result; default <b>null</b>
     * @param int             $limit        (optional) Limit; default <b>5</b>
     * @param boolean         $random       (optional) Shuffle the results; default <b>false</b>
     * @return Cache_Data[] Associative list of plugins
     */
    public static function getByContributors($contributors, $exclSlug = null, $limit = 5, $random = false) {
        if (!is_array($contributors)) {
            $contributors = array($contributors);
        }
        
        return self::_searchBy(
            $contributors, 
            self::KEY_INDEX_CONTRIBUTORS,
            $exclSlug,
            $limit, 
            $random
        );
    }
    
    /**
     * Search by tags
     * 
     * @param string|string[] $tags     Plugin tag(s)
     * @param string          $exclSlug (optional) Plugin(s) excluded from the result; default <b>null</b>
     * @param int             $limit    (optional) Limit; default <b>5</b>
     * @param boolean         $random   (optional) Shuffle the results; default <b>false</b>
     * @return Cache_Data[] Associative list of plugins
     */
    public static function getByTags($tags, $exclSlug = null, $limit = 5, $random = false) {
        if (!is_array($tags)) {
            $tags = array($tags);
        }

        return self::_searchBy(
            $tags, 
            self::KEY_INDEX_TAGS, 
            $exclSlug,
            $limit, 
            $random
        );
    }
    
    /**
     * Search through files by specified index key, building a cache in-memory (minimized IO operations)
     * 
     * @param string[] $searchTerms Array of search terms
     * @param string   $indexKey    Index key
     * @param string[] $exclSlugs   (optional) Plugin(s) excluded from the result; default <b>null</b>
     * @param string   $limit       (optional) Limit; default <b>5</b>
     * @param boolean  $random      (optional) Shuffle the results; default <b>false</b>
     * @return Cache_Data[] Associative list of plugins
     */
    protected static function _searchBy($searchTerms, $indexKey, $exclSlugs = null, $limit = 5, $random = false) {
        if (is_string($exclSlugs)) {
            $exclSlugs = [$exclSlugs];
        }
        $limit = abs((int) $limit);
        
        // Random mode on
        if ($random) {
            shuffle($searchTerms);
        }
        
        // Size limit for the search set
        $limitSet = $random ? (20 * $limit) : $limit;
        
        // Cache miss
        if (null === self::$_cacheIndex) {
            // Initialize the index
            self::$_cacheIndex = array(
                self::KEY_INDEX_TAGS         => [],
                self::KEY_INDEX_CONTRIBUTORS => [],
            );
            
            // Go through the files one by one
            foreach (
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        Temp::getPath(Temp::FOLDER_CACHE) . '/', 
                        RecursiveDirectoryIterator::SKIP_DOTS
                    ), 
                    RecursiveIteratorIterator::CHILD_FIRST
                ) as /*@var $item SplFileInfo*/ $item
            ) {
                if ($item->isFile() && preg_match('%\.json%', $item)) {
                    $pluginSlug = basename($item->getFilename(), '.json');
                    
                    // Load a Cache Data instance
                    $data = Cache_Data::get($pluginSlug);
                    
                    // Store the tags
                    foreach ($data->getTags() as $tagName) {
                        self::$_cacheIndex[self::KEY_INDEX_TAGS][$tagName][] = $data->getSlug();
                    }
                    foreach ($data->getAuthors() as $contributor) {
                        self::$_cacheIndex[self::KEY_INDEX_CONTRIBUTORS][$contributor][] = $data->getSlug();
                    }
                    
                    // Garbage collection
                    Cache_Data::gc($pluginSlug);
                }
            }
        }
        
        // Prepare the result
        $pluginSlugs = array();
        foreach ($searchTerms as $term) {
            if (isset(self::$_cacheIndex[$indexKey][$term])) {
                // Append to the result
                $pluginSlugs = array_unique(
                    array_merge(
                        $pluginSlugs, 
                        null === $exclSlugs
                            ? self::$_cacheIndex[$indexKey][$term]
                            : array_filter(
                                self::$_cacheIndex[$indexKey][$term],
                                function($item) use($exclSlugs) {
                                    return !in_array($item, $exclSlugs);
                                }
                            )
                    )
                );
                
                // Limit reached
                if (count($pluginSlugs) >= $limitSet) {
                    break;
                }
            }
        }
        
        // Shuffle the result
        if ($random) {
            shuffle($pluginSlugs);
        }
        $pluginSlugs = array_slice($pluginSlugs, 0, $limit);
        
        // Load the data
        return array_combine(
            $pluginSlugs, 
            array_map(
                function($pluginSlug) {
                    return Cache_Data::get($pluginSlug);
                }, 
                $pluginSlugs
            )
        );
    }
    
    /**
     * Build a cache of all files sorted in ascending order
     */
    protected static function _buildCache() {
        // Build siblings cache
        if (null === self::$_cacheFiles) {
            // Prepare the directory iterator
            self::$_cacheFiles = array_map(
                function($item) {
                    return basename($item, '.json');
                }, 
                glob(Temp::getPath(Temp::FOLDER_CACHE) . '/*.json')
            );

            // Sort the files
            sort(self::$_cacheFiles);
        }
    }
}   

/*EOF*/