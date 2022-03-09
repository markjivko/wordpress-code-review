<?php
/**
 * Potrivit - Test CLI methods
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Run_Test {
    
    /**
     * Store install data
     * 
     * @var array
     */
    public static $installLogs = [];
    
    /**
     * Store uninstall data
     * 
     * @var array
     */
    public static $uninstallLogs = [];
    
    /**
     * Test a plugin; performs purge, install and activate actions
     * 
     * @param string $pluginSlug Plugin slug
     * @throws Exception
     */
    public static function run($pluginSlug = null) {
        Console::header('Test Runner for "' . $pluginSlug . '"');
        $startTime = microtime(true);
        if (0 === posix_getuid()) {
            throw new Exception('Must run this tool as yourself (not root)');
        }

        // Plugin slug not specified
        if (!is_string($pluginSlug)) {
            throw new Exception('Plugin slug is mandatory');
        }

        // Sanitize the slug
        $pluginSlug = preg_replace('%[^\w\-]+%i', '', strtolower($pluginSlug));

        // Invalid value provided
        if (!strlen($pluginSlug)) {
            throw new Exception('Invalid plugin slug');
        }

        do {
            // Skip this plugin
            if (!Cache_Live::check($pluginSlug)) {
                Console::log('Plugin is not live', false);
                Render_Listing::deletePlugin($pluginSlug);
                break;
            }

            try {
                // Get plugin info
                Cache_Fetch::get($pluginSlug)->information();
            } catch (Exception $exc) {
                Console::log($exc->getMessage(), false);
                Render_Listing::deletePlugin($pluginSlug);
                break;
            }

            // Remove all plugins
            Run_Plugin::purge();

            // Benchmark an empty website
            Test_Benchmark::get($pluginSlug)->run(Test_Benchmark::BENCH_CLEAN);

            // Clear the logs
            AccessLog::clear();
            try {
                // Install and activate the plugin
                Run_Plugin::install($pluginSlug);
            } catch (Exception $exc) {
                Console::log($exc->getMessage(), false);
            }

            // Store the uninstall logs
            self::$installLogs = AccessLog::getAll(
                '/wp-admin/wp-api.php', 
                AccessLog::REQUEST_TYPE_POST
            );

            // Benchmark after plugin installation
            Test_Benchmark::get($pluginSlug)->run(Test_Benchmark::BENCH_INSTALLED);

            // Run all tests
            Tester::run($pluginSlug);

            // Clear the logs
            AccessLog::clear();

            try {
                // Uninstall the plugin
                Run_Plugin::uninstall($pluginSlug);
            } catch (Exception $exc) {
                Console::log($exc->getMessage(), false);
                
                // Archive was corrupted
                if (preg_match('%\bplugin\s+not\s+found\b%i', $exc->getMessage())) {
                    Render_Listing::deletePlugin($pluginSlug);
                    break;
                }
            }

            // Store the uninstall logs
            self::$uninstallLogs = AccessLog::getAll(
                '/wp-admin/wp-api.php', 
                AccessLog::REQUEST_TYPE_POST
            );

            // Benchmark after plugin uninstall
            Test_Benchmark::get($pluginSlug)->run(Test_Benchmark::BENCH_UNINSTALLED);

            // Run the final uninstall check
            Tester::run($pluginSlug, false);

            // Render the new page
            Render_Listing::updatePlugin($pluginSlug);
        } while(false);
        
        Console::info('Tests executed in ' . round(microtime(true) - $startTime, 2) . 's');
    }
}

/* EOF */