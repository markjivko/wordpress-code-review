<?php
/**
 * Potrivit - Test Case
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
abstract class Test_Case {
    
    // Category class prefixes
    const PREFIX_OPTIMIZATIONS  = 'Test_1_';
    const PREFIX_BENCHMARKS     = 'Test_2_';
    
    /**
     * Current test case plugin slug
     * 
     * @var string
     */
    protected $_pluginSlug = null;
    
    /**
     * Current test case score object
     * 
     * @var Test_Score
     */
    protected $_pluginScore = null;
    
    /**
     * Run a test case
     * 
     * @return mixed Test result
     * @throws Exception
     */
    abstract public function run();
    
    /**
     * Get the plugin slug
     * 
     * @return string Plugin slug
     */
    public function getSlug() {
        return $this->_pluginSlug;
    }
    
    /**
     * Get the plugin score object
     * 
     * @return Test_Score
     */
    public function getScore() {
        return $this->_pluginScore;
    }
    
    /**
     * Test Case
     * 
     * @param string $pluginSlug Plugin slug
     */
    public function __construct($pluginSlug) {
        $this->_pluginSlug  = $pluginSlug;
        $this->_pluginScore = new Test_Score($this);
    }
}

/*EOF*/