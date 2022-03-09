<?php
/**
 * Potrivit - Test
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Render_Listing {
    
    const FOLDER_MAIN   = 'main';
    const FOLDER_PLUGIN = 'plugin';
    
    /**
     * Re-render all common assets and listings and refresh indexes
     */
    public static function render() {
        Console::log('- Regenerating assets...');
        shell_exec('rm -rf ' . escapeshellarg($outputPath = Config::get()->outputPath() . '/' . self::FOLDER_MAIN));
        mkdir($outputPath, 0755, true);
        
        // Prepare the favicon
        copy(ROOT . '/res/layout/assets/favicon.ico', Config::get()->outputPath() . '/favicon.ico');
        
        // Prepare the copyright info
        $copyYear = date('Y');
        $copyVersion = Config::get()->version();
        $copyDomain  = Config::get()->domainLive();
        $copyrightInfo = <<<"COPY"
/**
 * @copyright (c) $copyYear, $copyDomain
 * @author    Mark Jivko (//markjivko.com)
 * @package   potrivit-ssg
 * @version   $copyVersion
 * @license   //gnu.org/licenses/gpl-3.0.txt
 */
COPY;
        
        /**
         * Replace placeholders
         * 
         * @param string $fileContents File contents
         * @return string
         */
        $setPlaceholders = function($fileContents) use($copyVersion, $copyDomain){
            return str_replace(
                array(
                    '__VERSION__',
                    '__DOMAIN__',
                    '__FOLDER_MAIN__',
                    '__FOLDER_PLUGIN__',
                ), 
                array(
                    $copyVersion,
                    $copyDomain,
                    Render_Listing::FOLDER_MAIN,
                    Render_Listing::FOLDER_PLUGIN,
                ), 
                $fileContents
            );
        };
        
        // Minify assets
        foreach (['css/main.css', 'js/main.js'] as $relPath) {
            $relPathDir = basename(dirname($relPath));
            if (!is_dir($outputPath . '/' . $relPathDir)) {
                mkdir($outputPath . '/' . $relPathDir, 0755, true);
            }
            
            // Get the file contents
            $fileContents = $setPlaceholders(
                file_get_contents(ROOT . '/res/layout/assets/' . $relPath)
            );
            
            // Write to output
            file_put_contents(
                $outputPath . '/' . $relPath, 
                $copyrightInfo 
                . PHP_EOL 
                . (Config::get()->production()
                    ? preg_replace(
                        array(
                            '%(?:^\s*\/\/.*?[\r\n]+\s*|\/\*.*?\*\/)%ims',
                            '%[\r\n]+\s*%ims',
                            '%\s{2,}%',
                        ), 
                        array(
                            '', 
                            '', 
                            ' ',
                        ), 
                        $fileContents
                    )
                    : $fileContents
                )
            );
        }
        
        // Prepare the PWA script
        file_put_contents(
            Config::get()->outputPath() . '/pwa.js',
            $setPlaceholders(
                file_get_contents(ROOT . '/res/layout/assets/js/pwa.js')
            )
        );
        
        // Prepare as-is resources
        foreach (['img'] as $folderName) {
            passthru('cp -r ' . escapeshellarg(ROOT . '/res/layout/assets/' . $folderName) . ' ' . escapeshellarg($outputPath));
        }
        
        // Create the robots file
        file_put_contents(
            Config::get()->outputPath() . '/robots.txt',
            'User-agent: *'
            . PHP_EOL . 'Allow: /'
            . PHP_EOL . PHP_EOL . 'Sitemap: https://' . Config::get()->domainLive() . '/sitemap.xml'
        );
        
        // Prepare the listing renderers
        $listingRenderers = array_filter(
            array_map(
                function($filePath) {
                    if (!method_exists($rendererClass = 'Render_Listing_' . basename($filePath, '.php'), 'run')) {
                        return null;
                    }

                    return $rendererClass;
                },
                glob(ROOT . '/lib/Render/Listing/*.php')
            )
        );
        natsort($listingRenderers);
        foreach ($listingRenderers as $rendererClass) {
            try {
                Console::log('- Running "' . $rendererClass . '"...');
                call_user_func(array($rendererClass, 'run'));
            } catch (Exception $exc) {
                Log::warning($exc->getMessage());
            }
        }
        
        Console::info('You can test the static site on on http://' . Config::get()->domainTest());
        Console::info('Remember to add "http://' . Config::get()->domainTest() . '" to chrome://flags/#unsafely-treat-insecure-origin-as-secure');
    }
    
    /**
     * Delete a plugin from the database
     * 
     * @param string  $pluginSlug    Plugin slug
     * @param boolean $markAsOffline (optional) Mark the plugin as offline so it's not re-tested; default <b>false</b>
     */
    public static function deletePlugin($pluginSlug, $markAsOffline = false) {
        Console::log("Removing plugin $pluginSlug...");
        
        // Mark as offline
        $markAsOffline && Cache_Live::mark($pluginSlug, false);
        
        // Remove rendered page
        if (is_dir($pluginPath = Config::get()->outputPath() . '/' . self::FOLDER_PLUGIN . '/' . $pluginSlug)) {
            shell_exec("rm -rf '$pluginPath'");
        }
        
        // Remove fetched data
        Cache_Fetch::get($pluginSlug)->clean();
        
        // Remove cached data
        Cache_Data::get($pluginSlug)->fileDelete();
        
        // Memory clean-up
        Cache_Data::gc($pluginSlug);
    }
    
    /**
     * Update a plugin's render
     * 
     * @param string $pluginSlug Plugin slug
     */
    public static function updatePlugin($pluginSlug) {
        Console::log("Rendering...");
        
        // Prepare the test layout HTML
        $resLayoutTests = array();
        
        // Go through the data sets
        foreach (Tester::getData()[Tester::DATA_ACTIVE] as $testClass => $data) {
            if ($data instanceof Exception) {
                Console::log(' - Failed test <' . $testClass . '>: ' . $data->getMessage(), false);
                self::deletePlugin($pluginSlug);
                return;
            }
            
            // Prepare the score instance
            $score = Tester::getData()[Tester::SCORE][$testClass];
                    
            // Layout file found
            if (is_file($filePath = ROOT . '/res/layout/' . strtolower(str_replace('_', '/', $testClass)) . '.phtml')) {
                try {
                    Console::log(' - Rendering fragment "test/' . basename(dirname($filePath)) . '/'. basename($filePath) .'"...');
                    
                    ob_start();
                    require $filePath;
                    $resLayoutTests[$testClass] = ob_get_clean();
                } catch (Exception $exc) {
                    ob_end_clean();
                    
                    Log::error($exc->getMessage(), $exc->getFile(), $exc->getLine());
                    Console::log(' - Failed rendering fragment "test/' . basename(dirname($filePath)) . '/'. basename($filePath) . '": ' . $exc->getMessage(), false);
                    return;
                }
            }
        }
        
        // Render
        ob_start();
        require ROOT . '/res/layout/test/index.phtml';
        $content = ob_get_clean();
        
        // Prepare the output folder
        if (!is_dir($outputPath = Config::get()->outputPath() . '/' . self::FOLDER_PLUGIN . '/' . $pluginSlug)) {
            mkdir($outputPath, 0755, true);
        }

        // Perserve the work of `wp render` references
        if (is_file($indexPath = $outputPath . '/index.html')) {
            if (preg_match_all('%<(aside|nav)>.*?</\1>%ims', file_get_contents($indexPath), $matches, PREG_SET_ORDER)) {
                foreach($matches as $match) {
                    $content = preg_replace(
                        '%<(' . preg_quote($match[1]) . ')>.*?</\1>%ims', 
                        $match[0], 
                        $content
                    );
                }
            }
        }
        
        // Save the render
        file_put_contents(
            $indexPath, 
            Config::get()->production()
                ? Render_Helper::compressHtml($content)
                : $content
        );
        
        // Finally store the rating for aggregators
        Cache_Data::get($pluginSlug)
            ->setRating(Render_Helper::getRating())
            ->fileSave();
        
        Console::info('Preview URL: http://' . Config::get()->domainTest() . '/' . self::FOLDER_PLUGIN . '/' . $pluginSlug . '/');
    }
}

/*EOF*/