<?php
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Potrivit - Benchmark utility
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Test_Benchmark {
    
    // Benchmark checkpoints
    const BENCH_CLEAN       = 'clean';
    const BENCH_INSTALLED   = 'installed';
    const BENCH_UNINSTALLED = 'uninstalled';
    
    // Checkpoint pages
    const DATA_PAGES                 = 'pages';
    const DATA_PAGES_TITLE           = 'title';
    const DATA_PAGES_BROWSER_METRICS = 'browserMetrics';
    const DATA_PAGES_BROWSER_LOGS    = 'browserLogs';
    const DATA_PAGES_SERVER_METRICS  = 'serverMetrics';
    
    // Checkpoint diffs
    const DATA_DIFF                  = 'diff';
    const DATA_DIFF_FS               = 'diffFs';
    const DATA_DIFF_DB               = 'diffDb';
    const DATA_DIFF_DB_TABLES        = 'diffDbTables';
    const DATA_DIFF_DB_OPTIONS       = 'diffDbOptions';
    const DATA_DIFF_DB_SIZE          = 'diffDbSize';
    
    const DEFAULT_PAGES = [
        '/'                      => 'Home',
        '/wp-admin'              => 'Dashboard',
        '/wp-admin/edit.php'     => 'Posts',
        '/wp-admin/post-new.php' => 'Add New Post',
        '/wp-admin/upload.php'   => 'Media Library',
    ];
    
    /**
     * Store the benchmark data for individual URLs
     * 
     * @var array
     */
    protected $_data = [];
    
    /**
     * Singleton instances
     * 
     * @var Test_Benchmark[]
     */
    protected static $_instance = [];
    
    /**
     * Plugin slug
     * 
     * @var string
     */
    protected $_pluginSlug = null;
    
    /**
     * Chrome driver
     * 
     * @var Remote\ChromeDriver
     */
    protected $_driver = null;
    
    /**
     * Get a singleton instance
     * 
     * @param string $pluginSlug Plugin slug
     * @return Test_Benchmark
     */
    public static function get($pluginSlug) {
        if (!isset(self::$_instance[$pluginSlug])) {
            self::$_instance[$pluginSlug] = new self($pluginSlug);
        }
        return self::$_instance[$pluginSlug];
    }
    
    /**
     * Test_Benchmark
     * 
     * @param string $pluginSlug
     */
    protected function __construct($pluginSlug) {
        $this->_pluginSlug = $pluginSlug;
    }
    
    /**
     * Get the benchmark data
     * 
     * @return array
     */
    public function data() {
        return $this->_data;
    }
    
    /**
     * Run the benchmark tools at a certain checkpoint
     * 
     * @param string $checkPoint
     * @return Test_Benchmark
     */
    public function run($checkPoint) {
        Console::log('Benchmark <' . $checkPoint . '>');

        // Launch the browser
        $this->_driver = ChromeDriver::start(
            DesiredCapabilities::chrome()
                ->setCapability(
                    ChromeOptions::CAPABILITY,
                    (new ChromeOptions())->addArguments([
                        'headless'
                    ])
                )
                ->setCapability(
                    WebDriverCapabilityType::JAVASCRIPT_ENABLED, 
                    true
                )
                ->setCapability(
                    WebDriverCapabilityType::LOGGING_PREFS, 
                    ['browser' => 'ALL']
                )
        );
        
        // Initialize the checkpoint data
        if (!isset($this->_data[$checkPoint])) {
            $this->_data[$checkPoint] = [
                self::DATA_PAGES => [],
                self::DATA_DIFF  => [],
            ];
        }
        
        // Get the list of pages to test
        $pagesToTest = self::DEFAULT_PAGES;

        // Append extra pages
        switch ($checkPoint) {
            case self::BENCH_INSTALLED:
            case self::BENCH_UNINSTALLED:
                $pagesToTest += array_slice(Run_Plugin::getPages(false, true), 0, 10, true);
                break;
        }
        
        // Load the admin
        $this->_driver->get('http://' . Config::get()->domainWp() . '/wp-login.php');

        // Set the user and password
        $this->_driver
            ->findElement(WebDriverBy::id('user_login'))
            ->sendKeys(Config::get()->siteUser());
        $this->_driver
            ->findElement(WebDriverBy::id('user_pass'))
            ->sendKeys(Config::get()->siteUser());
        
        // Log in
        $this->_driver
            ->findElement(WebDriverBy::id('wp-submit'))
            ->click();
        $this->_driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::id('adminmenumain')
            )
        );
        
        // Visit each page
        $pageKey = 0;
        foreach ($pagesToTest as $page => $pageTitle) {
            Console::log(' - BM: ' . ($pageKey + 1) . '/' . count($pagesToTest) . '. Loading page "' . $page . '"');
            
            // Clean-up the logs
            AccessLog::clear();
            
            // Enable performance monitor
            $this->_driver->getDevTools()->execute('Performance.enable');
        
            // Visit page
            $this->_driver->navigate()->to('http://' . Config::get()->domainWp() . '/' . trim($page, '/\\'));
            
            // Wait for things to settle; more for custom plugin pages (wait for JS bugs)
            usleep(($pageKey > 4 ? 1500 : 250) * 1000);
            
            // Pepare the metrics
            $browserMetrics = [];
            $devToolsMetrics = $this->_driver->getDevTools()->execute('Performance.getMetrics');
            if (is_array($devToolsMetrics) && isset($devToolsMetrics['metrics'])) {
                foreach ($devToolsMetrics['metrics'] as $item) {
                    $browserMetrics[$item['name']] = $item['value'];
                }
            }
            
            // Get performance metrics
            $this->_data[$checkPoint][self::DATA_PAGES][$page] = [
                self::DATA_PAGES_TITLE           => $pageTitle,
                self::DATA_PAGES_BROWSER_METRICS => $browserMetrics,
                self::DATA_PAGES_BROWSER_LOGS    => $this->_driver->manage()->getLog('browser'),
                self::DATA_PAGES_SERVER_METRICS  => AccessLog::getAll($page),
            ];
            
            // Disable performance metrics
            $this->_driver->getDevTools()->execute('Performance.disable');
            
            // Load a static page to prevent AJAX requests from spilling in the logs before the next navigation
            $this->_driver->navigate()->to('http://' . Config::get()->domainTest() . '/robots.txt');
            
            // Increment the page key
            $pageKey++;
        }
        
        // Remove the driver
        $this->_driver->quit();
        unset($this->_driver);
        
        // Clean-up the logs
        AccessLog::clear();
        
        // Prepare empty folders for git
        Folder::markEmptyFolders();
        passthru('git -C /var/www/wordpress add -A');
        
        // Parse the diff text
        $diffs = preg_replace(
            '%modified:\s*wp\-admin\/wp\-api.php%i', 
            '', 
            shell_exec('git -C /var/www/wordpress status')
        );
        
        // Prepare the filesystem data
        $this->_data[$checkPoint][self::DATA_DIFF][self::DATA_DIFF_FS] = [];
        if (preg_match_all('%\b(new file|modified|deleted)\b\s*:\s*(.*?)$%ims', $diffs, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // Prepare the file key
                $key = 'other';
                
                // Our plugin or the uploads folder
                if (0 === strpos($match[2], 'wp-content/plugins/' . $this->_pluginSlug . '/')) {
                    $key = 'plugins';
                } elseif (0 === strpos($match[2], 'wp-content/uploads/')) {
                    $key = 'uploads';
                }
                
                // Initialize the data set
                if (!isset($this->_data[$checkPoint][self::DATA_DIFF][self::DATA_DIFF_FS][$key])) {
                    $this->_data[$checkPoint][self::DATA_DIFF][self::DATA_DIFF_FS][$key] = [
                        'count' => 0,
                        'size'  => 0,
                        'files' => []
                    ];
                }
                
                // Store the file info
                $this->_data[$checkPoint][self::DATA_DIFF][self::DATA_DIFF_FS][$key]['count']++;
                $this->_data[$checkPoint][self::DATA_DIFF][self::DATA_DIFF_FS][$key]['size'] += @filesize('/var/www/wordpress/' . $match[2]);
                if (count($this->_data[$checkPoint][self::DATA_DIFF][self::DATA_DIFF_FS][$key]['files']) < 10) {
                    $this->_data[$checkPoint][self::DATA_DIFF][self::DATA_DIFF_FS][$key]['files'][] = [$match[2], $match[1]];
                }
            }
        }
        
        // Export the new SQL db
        $sqlPath = '/var/www/wordpress.sql';
        $sqlPathPlugin = '/var/www/wordpress-plugin.sql';
        passthru('mysqldump -u ' . Config::get()->dbUser() . ' -p' . Config::get()->dbPass() . ' ' . Config::get()->dbName() . ' > ' . escapeshellarg($sqlPathPlugin) . ' 2>&1');
        passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' ' . escapeshellarg($sqlPathPlugin));
        passthru('printf "%s%s" "-- " "$(cat \'' . $sqlPathPlugin . '\')" > \'' . $sqlPathPlugin . '\'');
        
        // Get the new tables
        $oldTables = [];
        if (preg_match_all('%^create table\s*`([^`]+)`%im', shell_exec("grep -i 'create table' '$sqlPath'"), $matches)) {
            $oldTables = $matches[1];
        }
        
        // Get the new tables
        $newTables = [];
        if (preg_match_all('%^create table\s*`([^`]+)`%im', shell_exec("grep -i 'create table' '$sqlPathPlugin'"), $matches)) {
            $newTables = $matches[1];
        }
        shuffle($newTables);
        
        // Store the new tables and database size difference
        $this->_data[$checkPoint][self::DATA_DIFF][self::DATA_DIFF_DB] = [
            self::DATA_DIFF_DB_TABLES  => array_diff($newTables, $oldTables),
            self::DATA_DIFF_DB_OPTIONS => Run_Plugin::getOptions(false, true),
            self::DATA_DIFF_DB_SIZE    => filesize($sqlPathPlugin) - filesize($sqlPath),
        ];
        unlink($sqlPathPlugin);
        
        return $this;
    }
}

/*EOF*/