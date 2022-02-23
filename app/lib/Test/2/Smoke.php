<?php
/**
 * Potrivit - Test
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Test_2_Smoke extends Test_Case {
   
    /**
     * Run a test case
     * 
     * @return mixed Test result
     * @throws Exception
     */
    public function run() {
        // Get the plugin data
        $pluginData = Tester::getData()[Tester::DATA_ACTIVE][Test_1_About::class];
        
        // Get the benchmark data
        $benchmarkData = Test_Benchmark::get($this->_pluginSlug)->data();
        
        // Check the logs
        $this->_runSmokeTest($pluginData, $benchmarkData);
    }
    
    /**
     * Check logs and perform the smoke test
     * 
     * @param array $pluginData
     * @param array $benchmarkData
     */
    protected function _runSmokeTest($pluginData, $benchmarkData) {
        // Errors - server-side
        $errorsServerSide = [];
        $errorsServerSideTimes = [];
        
        // Errors - user-side
        $errorsUserSide = [];
        $errorsUserSideTimes = [];
        
        // Go through the pages
        foreach ($benchmarkData[Test_Benchmark::BENCH_INSTALLED][Test_Benchmark::DATA_PAGES] as $page => $pageData) {
            if (isset(Test_Benchmark::DEFAULT_PAGES[$page])) {
                continue;
            }

            // User-side errors
            foreach ($pageData[Test_Benchmark::DATA_PAGES_BROWSER_LOGS] as $reqError) {
                $errFile = 'unknown';
                $errLine = '';
                $errMessage = strip_tags($reqError['message']);
                
                // Console-level error
                if (preg_match('%^http:\/\/' . Config::get()->domainWp() . '\/(.*?)\s+(\d+:\d+)\s*[\'"](.*)[\'"]$%ims', $errMessage, $matches)) {
                    list(, $errFile, $errLine, $errMessage) = $matches;
                }
                
                // Network error
                if ('network' === $reqError['source']) {
                    $errFile = '';
                    $errMessage = preg_replace('%^http:\/\/' . Config::get()->domainWp() . '\/%', '', $errMessage);
                }
                $errorKey = $reqError['source'] . ':' . $reqError['level'] . '/' . md5($errMessage);
                
                // Count error appearances
                if (!isset($errorsUserSideTimes[$errorKey])) {
                    $errorsUserSideTimes[$errorKey] = 0;
                }
                $errorsUserSideTimes[$errorKey]++;

                // Append/overwrite the message
                $errorsUserSide[$errorKey] = (
                        $errorsUserSideTimes[$errorKey] > 1 
                            ? (' <b>' . $errorsUserSideTimes[$errorKey] . '</b> occurences, only the last one shown') 
                            : ''
                    )
                    . '<ul>'
                        . '<li>&gt; GET request to <b>' . $page . '</b></li>'
                        . '<li>&gt; <strong>' . ucfirst($reqError['source']) . ' (' . strtolower($reqError['level']) . ')</strong>' . (strlen($errFile) ? ' in ' : '')
                        . ' <em>' . htmlentities($errFile) . (strlen($errLine) ? ('+' . $errLine) : '') . '</em></li>'
                    . '</ul>'
                    . '<blockquote class="error">' 
                        . htmlentities(
                            preg_replace(
                                '%https?:\/\/' . preg_quote(Config::get()->domainWp()) . '%',
                                '',
                                $errMessage
                            )
                        )
                    . '</blockquote>';
            }
            
            // Server-side errors
            foreach ($pageData[Test_Benchmark::DATA_PAGES_SERVER_METRICS] as $requestData) {
                if (!is_array($requestData) || 5 !== count($requestData)) {
                    continue;
                }
                
                // Get the request information
                list($reqType, $reqPage, $reqTime, $reqMemory, $reqError) = $requestData;
                
                // An error was caught
                if (is_array($reqError)) {
                    // Prepare the error key
                    $errorKey = $reqError['type'] . '/' . md5($reqError['message']);
                    
                    // Count error appearances
                    if (!isset($errorsServerSideTimes[$errorKey])) {
                        $errorsServerSideTimes[$errorKey] = 0;
                    }
                    $errorsServerSideTimes[$errorKey]++;
                    
                    // Append/overwrite the message
                    $errorsServerSide[$errorKey] = (
                            $errorsServerSideTimes[$errorKey] > 1 
                                ? (' <b>' . $errorsServerSideTimes[$errorKey] . '</b> occurences, only the last one shown') 
                                : ''
                        ) 
                        . '<ul>'
                            . '<li>&gt; GET request to <b>' . $page  . '</b></li>'
                            . (
                                'GET' !== $reqType && $page !== $reqPage 
                                    ? ('<li>&gt; ' . $reqType . ' request to <b>' . $reqPage . '</b></li>')
                                    : ''
                            )
                            . '<li>&gt; <strong>' . Seo::getError($reqError['type']) . '</strong> in'
                            . ' <em>' . preg_replace('%^/var/www/wordpress/%', '', $reqError['file']) . '+' . $reqError['line'] . '</em>'
                        . '</ul>'
                        . '<blockquote class="error">' . $reqError['message'] . '</blockquote>';
                }
            }
        }
        
        // Grade server-side errors
        $this->getScore()->grade(
            'Server-side errors', 
            Seo::text(Seo::TEXT_BENCH_SMOKE_SERVER_FIX_DESC),
            intval(count($errorsServerSide) > 0),
            1,
            Seo::text(Seo::TEXT_BENCH_SMOKE_SERVER_FIX_SUCCESS),
            Seo::text(Seo::TEXT_BENCH_SMOKE_SERVER_FIX_FAILURE)
                .'<ul><li>'
                    . implode('</li><li>', $errorsServerSide)
                . '</li></ul>',
            Test_Score::WEIGHT_ELEVATED
        );
        
        // SRP
        $errorsSrp = $this->_checkSrp();
        $this->getScore()->grade(
            'SRP', 
            Seo::text(Seo::TEXT_BENCH_SMOKE_SRP_FIX_DESC),
            count($errorsSrp),
            2,
            Seo::text(Seo::TEXT_BENCH_SMOKE_SRP_FIX_SUCCESS),
            Seo::text(Seo::TEXT_BENCH_SMOKE_SRP_FIX_FAILURE) 
                . '<ul><li>'
                    . implode('</li><li>', $errorsSrp)
                . '</li></ul>',
            Test_Score::WEIGHT_ELEVATED
        );
        
        // Grade user-side errors
        $this->getScore()->grade(
            'User-side errors', 
            Seo::text(Seo::TEXT_BENCH_SMOKE_USER_FIX_DESC),
            intval(count($errorsUserSide) > 0),
            1,
            Seo::text(Seo::TEXT_BENCH_SMOKE_USER_FIX_SUCCESS),
            Seo::text(Seo::TEXT_BENCH_SMOKE_USER_FIX_FAILURE) 
                . '<ul><li>'
                    . implode('</li><li>', $errorsUserSide)
                . '</li></ul>',
            Test_Score::WEIGHT_ELEVATED
        );
    }
    
    /**
     * Check single responsibility principle<ul>
     * <li>File output</li>
     * <li>Server-side errors</li>
     * </ul>
     */
    protected function _checkSrp() {
        $errors = [];
        $listMaxSize = 10;
        
        // Reset the logs
        file_put_contents($logErrors = '/var/log/apache2/error.log', '');
        
        // Store files with output
        $errOutput = [];
        foreach (Folder::getIterator("/var/www/wordpress/wp-content/plugins/{$this->_pluginSlug}/") as /*@var $item SplFileInfo*/ $item) {
            if ($item->isFile()) {
                if ('php' === strtolower(pathinfo($item->getPathname(), PATHINFO_EXTENSION))) {
                    $subPath = str_replace('/var/www/wordpress/', '', $item->getPathname());
                    
                    // Get the result
                    if (strlen(@file_get_contents('http://' . Config::get()->domainWp() . '/' . $subPath))) {
                        $errOutput[] = '<li>&gt; <b>/' . $subPath . '</b></li>';
                    }
                    
                    // Sleep for 1ms between requests to avoid apache issues
                    usleep(1000);
                }
            }
        }
        if (count($errOutput)) {
            shuffle($errOutput);
            $errors[] = '<b>' . count($errOutput) . '</b>&times; ' 
                . Seo::text(Seo::TEXT_BENCH_SMOKE_SRP_OUTPUT)
                . (count($errOutput) > $listMaxSize ? ' (only ' . $listMaxSize . ' are shown)' : '') . ':'
                . '<ul>' . implode('', array_slice($errOutput, 0, $listMaxSize)) . '</ul>';
        }
        
        // Get the errors log
        $err500 = file($logErrors);
        if (is_array($err500) && count($err500)) {
            $err500 = array_map(
                function($errorLine) {
                    return '<li>&gt; ' . preg_replace(
                        [
                            '%(?:^.*?\[client.*?]\s+|stack trace.*$|\\\n)%i', 
                            '%\/var\/www\/wordpress\/(.*?\.php(?:\:\d+)?)%i',
                            '%(^.*?):(.*)%i',
                        ],
                        [
                            '', 
                            '<em>$1</em>',
                            '<b>$1</b><blockquote class="error">$2</blockquote>',
                        ],
                        htmlentities($errorLine)
                    ) . '</li>';
                }, 
                array_filter(
                    $err500, 
                    function($item) {
                        return preg_match('%\[pid\s+\d+\]\s*\[client\s*127\.0\.0\.1\:\d+\]%i', $item);
                    }
                )
            );
            if (count($err500)) {
                shuffle($err500);
                $errors[] = '<b>' . count($err500) . '</b>&times; '
                    . Seo::text(Seo::TEXT_BENCH_SMOKE_SRP_500)
                    . (count($err500) > $listMaxSize ? ' (only ' . $listMaxSize . ' are shown)' : '') . ':'
                    . '<ul>'  . implode('', array_slice($err500, 0, $listMaxSize)) . '</ul>';
            }
        }
        return $errors;
    }
}

/*EOF*/