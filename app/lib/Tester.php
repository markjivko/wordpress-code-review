<?php
/**
 * Potrivit - Test
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Tester {
    
    // Data keys
    const BENCH          = 'b';
    const DATA_ACTIVE    = 'd';
    const DATA_INACTIVE  = 'i';
    const SCORE          = 's';
    
    /**
     * Plugin slug
     * 
     * @var string
     */
    protected static $_slug = '';
    
    /**
     * Tester data
     * 
     * @var array
     */
    protected static $_data = array(
        self::BENCH         => [],
        self::DATA_ACTIVE   => [],
        self::DATA_INACTIVE => [],
        self::SCORE         => [],
    );
    
    /**
     * Store instances of test cases
     * 
     * @var Test_Case[]
     */
    protected static $_tests = [];
    
    /**
     * Get gathered test data
     * 
     * @return array
     */
    public static function getData() {
        return self::$_data;
    }
    
    /**
     * Get current plugin slug
     * 
     * @return string
     */
    public static function getSlug() {
        return self::$_slug;
    }
    
    /**
     * Run a suite of tests on the current plugin
     * 
     * @param string $pluginSlug
     * @param boolean $active (optional) Run when the plugin is active; default <b>true</b>
     */
    public static function run($pluginSlug, $active = true) {
        // Make all folders accessible
        shell_exec('find /var/www/wordpress -type d -exec chmod 755 {} +');
        
        self::$_slug = $pluginSlug;
        if (!isset(self::$_tests[self::$_slug])) {
            self::$_tests[self::$_slug] = [];
        }
        
        // Prepare the tests
        $testClasses = array_map(
            function($item) {
                return 'Test_' . basename(dirname($item)) . '_' . basename($item, '.php');
            }, 
            glob(ROOT . '/lib/Test/*/*.php')
        );
            
        // Sort the test classes
        natsort($testClasses);
        
        // Prepare the data
        foreach ($testClasses as $testClass) {
            if (class_exists($testClass)) {
                if (!isset(self::$_tests[self::$_slug][$testClass])) {
                    self::$_tests[self::$_slug][$testClass] = new $testClass($pluginSlug);
                }
                
                if (self::$_tests[self::$_slug][$testClass] instanceof Test_Case) {
                    if ($active || method_exists(self::$_tests[self::$_slug][$testClass], 'runInactive')) {
                        Console::log('* ' . $testClass . '::' . ($active ? 'active' : 'inactive'));

                        // Store the data
                        self::$_data[$active ? self::DATA_ACTIVE : self::DATA_INACTIVE][$testClass] = $active
                            ? self::$_tests[self::$_slug][$testClass]->run()
                            : self::$_tests[self::$_slug][$testClass]->runInactive();

                        // Update the score
                        self::$_data[self::SCORE][$testClass] = self::$_tests[self::$_slug][$testClass]->getScore();
                    }
                }
            }
        }
    }
}

/*EOF*/