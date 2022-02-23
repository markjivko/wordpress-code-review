<?php
/**
 * Potrivit - Cache data set
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Cache_Data extends Widget {
    
    // Cache keys
    const CACHE_TAGS         = 'tags';
    const CACHE_AUTHORS      = 'authors';
    const CACHE_INFO         = 'info';
    const CACHE_RATING       = 'rating';
    const CACHE_ARCHIVE_SIZE = 'archiveSize';
    const CACHE_SEO_DESC     = 'desc';
    const CACHE_SEO_TEXTS    = 'texts';
    
    /**
     * Associative array of pluginSlug => Cache_Data
     * 
     * @var Cache_Data[]
     */
    protected static $_instances = [];
    
    /**
     * Cached information
     * 
     * @var array
     */
    protected $_cache = [];
    
    /**
     * Plugin slug
     * 
     * @var string
     */
    protected $_slug;
    
    /**
     * Path to cached data file on disk
     * 
     * @var string
     */
    protected $_path;
    
    /**
     * Get a Singleton instance of Cache_Data
     * 
     * @return Cache_Data
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
     * Widget data
     * 
     * @return Cache_Data
     */
    protected function _getData() {
        return $this;
    }
    
    /**
     * Cache data
     * 
     * @param string $pluginSlug Plugin slug
     */
    protected function __construct($pluginSlug) {
        // Store the sanitized slug
        $this->_slug = preg_replace('%[^\w\-]+%i', '', $pluginSlug);
        
        // Store the path
        $this->_path = Temp::getPath(Temp::FOLDER_CACHE) . '/' . $this->getSlug() . '.json';
                
        // Load the cache data
        if (is_file($this->getPath())) {
            $this->_cache = @json_decode(file_get_contents($this->getPath()), true);
            
            if (!is_array($this->_cache)) {
                $this->_cache = [];
            }
        }
    }
    
    /**
     * Save the cache data to disk
     * 
     * @return boolean
     */
    public function fileSave() {
        return !!file_put_contents(
            $this->getPath(), 
            $this->__toString()
        );
    }
    
    /**
     * Delete the data from disk
     * 
     * @return boolean
     */
    public function fileDelete() {
        return is_file($this->getPath()) ? unlink($this->getPath()) : false;
    }
    
    /**
     * Get the plugin slug
     * 
     * @return string
     */
    public function getSlug() {
        return $this->_slug;
    }
    
    /**
     * Get the path the the cache data file
     * 
     * @return string
     */
    public function getPath() {
        return $this->_path;
    }
    
    /**
     * Set the plugin information
     * 
     * @param array $info Plugin information
     * @return Cache_Data
     */
    public function setInfo($info) {
        if (!is_array($info)) {
            $info = array();
        }
        
        // Don't store the same information twice
        unset($info[Test_1_About::DATA_PLUGIN_TAGS]);
        unset($info[Test_1_About::DATA_PLUGIN_CONTRIB]);
        
        // Update the local store
        $this->_cache[self::CACHE_INFO] = $info;
        return $this;
    }
    
    /**
     * Get the plugin data
     * 
     * @return array
     */
    public function getInfo() {
        return isset($this->_cache[self::CACHE_INFO])
            ? $this->_cache[self::CACHE_INFO]
            : [];
    }
    
    /**
     * Set the plugin tags
     * 
     * @param string $tags Tags
     * @return Cache_Data
     */
    public function setTags($tags) {
        $this->_cache[self::CACHE_TAGS] = $this->_sanitize($tags);
        return $this;
    }
    
    /**
     * Get the plugin tags
     * 
     * @return string[]
     */
    public function getTags() {
        return isset($this->_cache[self::CACHE_TAGS])
            ? $this->_cache[self::CACHE_TAGS]
            : [];
    }
    
    /**
     * Set the plugin authors
     * 
     * @param string[] $authors Authors
     * @return Cache_Data
     */
    public function setAuthors($authors) {
        $this->_cache[self::CACHE_AUTHORS] = $this->_sanitize($authors);
        return $this;
    }
    
    /**
     * Get the plugin authors
     * 
     * @return string[]
     */
    public function getAuthors() {
        return isset($this->_cache[self::CACHE_AUTHORS])
            ? $this->_cache[self::CACHE_AUTHORS]
            : [];
    }
    
    /**
     * Set the rating info
     * 
     * @param int[] $rating Total score and number of tests
     * @return Cache_Data
     */
    public function setRating($rating) {
        if (!is_array($rating)) {
            $rating = array(100, 1);
        } else {
            $rating = array_slice(array_map('intval', $rating), 0, 2);
        }
        
        $this->_cache[self::CACHE_RATING] = $rating;
        return $this;
    }
    
    /**
     * Get the rating
     * 
     * @return rating[]
     */
    public function getRating() {
        return isset($this->_cache[self::CACHE_RATING])
            ? $this->_cache[self::CACHE_RATING]
            : [100, 1];
    }
    
    /**
     * Set the archive size
     * 
     * @param int $archiveSize Archive size in bytes
     * @return Cache_Data
     */
    public function setArchiveSize($archiveSize) {
        $this->_cache[self::CACHE_ARCHIVE_SIZE] = (int) $archiveSize;
        return $this;
    }
    
    /**
     * Get the archive size in bytes
     * 
     * @return int
     */
    public function getArchiveSize() {
        return isset($this->_cache[self::CACHE_ARCHIVE_SIZE])
            ? $this->_cache[self::CACHE_ARCHIVE_SIZE]
            : 0;
    }
    
    /**
     * Set the plugin SEO description template
     * 
     * @param string $description SEO description template
     * @return Cache_Data
     */
    public function setSeoDescription($description) {
        $this->_cache[self::CACHE_SEO_DESC] = !is_string($description) 
            ? '' 
            : trim($description);
        
        return $this;
    }
    
    /**
     * Get the plugin SEO description template
     * 
     * @return string
     */
    public function getSeoDescription() {
        return isset($this->_cache[self::CACHE_SEO_DESC])
            ? $this->_cache[self::CACHE_SEO_DESC]
            : '';
    }
    
    /**
     * Get cached SEO text template
     * 
     * @param string $key   Text key
     * @param string $value Text template
     * @return Cache_Data
     */
    public function setSeoText($key, $value) {
        if (!isset($this->_cache[self::CACHE_SEO_TEXTS])) {
            $this->_cache[self::CACHE_SEO_TEXTS] = [];
        }
        $this->_cache[self::CACHE_SEO_TEXTS][$key] = $value;
        return $this;
    }
    
    /**
     * Get cached text value
     * 
     * @param string $key Text key
     * @return string|null
     */
    public function getSeoText($key) {
        return isset($this->_cache[self::CACHE_SEO_TEXTS]) && isset($this->_cache[self::CACHE_SEO_TEXTS][$key])
            ? $this->_cache[self::CACHE_SEO_TEXTS][$key]
            : null;
    }
    
    /**
     * String typecasting
     * 
     * @return string
     */
    public function __toString() {
        return json_encode([
            self::CACHE_ARCHIVE_SIZE => $this->getArchiveSize(),
            self::CACHE_TAGS         => $this->getTags(),
            self::CACHE_AUTHORS      => $this->getAuthors(),
            self::CACHE_INFO         => $this->getInfo(),
            self::CACHE_RATING       => $this->getRating(),
            self::CACHE_SEO_DESC     => $this->getSeoDescription(),
            self::CACHE_SEO_TEXTS    => isset($this->_cache[self::CACHE_SEO_TEXTS])
                ? $this->_cache[self::CACHE_SEO_TEXTS]
                : [],
        ], JSON_PRETTY_PRINT);
    }
    
    /**
     * Sanitize a list of items
     * 
     * @param string[] $items
     * @return string[] Sanitized items
     */
    protected function _sanitize($items) {
        if (!is_array($items)) {
            $items = array();
        }
        
        // Remove all non-word characters
        return array_values(
            array_filter(
                array_map(
                    function($item) {
                        return trim(preg_replace('%[^\w\-]+%i', '', strtolower($item)));
                    }, $items
                )
            )
        );
    }
}

/*EOF*/