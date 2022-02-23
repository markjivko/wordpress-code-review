<?php
/**
 * Potrivit - Test
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Test_2_Footprint extends Test_Case {
   
    // QA Parameters
    const MAX_BROWSER_NODES       = 25000;
    const MAX_BROWSER_MEMORY      = 75;    // MB
    const MAX_BROWSER_SCRIPT      = 1500;  // ms
    const MAX_BROWSER_LAYOUT      = 1500;  // ms
    
    const MAX_SERVER_MEMORY_TOTAL = 10;    // MB
    const MAX_SERVER_MEMORY_EXTRA = 5;     // MB
    const MAX_SERVER_TIME_TOTAL   = 500;   // ms
    const MAX_SERVER_TIME_EXTRA   = 200;   // ms
    
    const MAX_IO_SIZE = 25; // MB
    const MAX_DB_SIZE = 25; // MB
    
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
        
        // Grade install script
        $this->_runInstall($pluginData);
        
        // Grade server-side footprint
        $this->_runServerMetrics($pluginData, $benchmarkData);

        // Grade server-side storage
        $this->_runServerStorage($pluginData, $benchmarkData);
        
        // Grade browser footprint
        $this->_runBrowserMetrics($pluginData, $benchmarkData);
    }
    
    /**
     * Check for errors during installation
     */
    protected function _runInstall($pluginData) {
        $errors = [];
        
        // Error uninstalling
        if (count(Run_Test::$installLogs) && is_array(Run_Test::$installLogs[0][4])) {
            $reqError = Run_Test::$installLogs[0][4];
            
            // Append/overwrite the message
            $errors[] = Seo::text(Seo::TEXT_BENCH_FP_INSTALL)
                . '<ul>'
                    . '<li>&gt; <strong>' . Seo::getError($reqError['type']) . '</strong> in'
                    . ' <em>' . preg_replace('%^/var/www/wordpress/%', '', $reqError['file']) . '+' . $reqError['line'] . '</em>'
                . '</ul>'
                . '<blockquote class="error">' . $reqError['message'] . '</blockquote>';
        }
        
        $this->getScore()->grade(
            'Installer',
            Seo::text(Seo::TEXT_BENCH_FP_INSTALL_FIX_DESC),
            count($errors),
            1,
            Seo::text(Seo::TEXT_BENCH_FP_INSTALL_FIX_SUCCESS),
            Seo::text(Seo::TEXT_BENCH_FP_INSTALL_FIX_FAILURE)
                .'<ul><li>'
                    . implode('</li><li>', $errors)
                . '</li></ul>',
            Test_Score::WEIGHT_CRITICAL
        );
    }
    
    /**
     * Run test cases after uninstall
     */
    public function runInactive() {
        $errors = [];
        
        // Error uninstalling
        if (count(Run_Test::$uninstallLogs) && is_array(Run_Test::$uninstallLogs[0]) && is_array(Run_Test::$uninstallLogs[0][4])) {
            $reqError = Run_Test::$uninstallLogs[0][4];
            
            // Append/overwrite the message
            $errors[] = Seo::text(Seo::TEXT_BENCH_FP_UNINSTALL)
                . '<ul>'
                    . '<li>&gt; <strong>' . Seo::getError($reqError['type']) . '</strong> in'
                    . ' <em>' . preg_replace('%^/var/www/wordpress/%', '', $reqError['file']) . '+' . $reqError['line'] . '</em>'
                . '</ul>'
                . '<blockquote class="error">' . $reqError['message'] . '</blockquote>';
        }
        
        // Get the benchmark data
        $benchmarkData = Test_Benchmark::get($this->_pluginSlug)->data();
        
        // Filesystem info
        $fileSystem = $benchmarkData[Test_Benchmark::BENCH_UNINSTALLED][Test_Benchmark::DATA_DIFF][Test_Benchmark::DATA_DIFF_FS] ?? [];
        
        // Filesystem errors
        if (isset($fileSystem['plugins'])) {
            shuffle($fileSystem['plugins']['files']);
            $errors[] = Seo::text(
                    Seo::TEXT_BENCH_FP_UNINSTALL_IO,
                    '<b>' 
                        . number_format($fileSystem['plugins']['count']) . ' ' . (1 == $fileSystem['plugins']['count'] ? 'file' : 'files') 
                    . '</b> (' . number_format($fileSystem['plugins']['size'] / 1024 / 1024, 2) . 'MB)'
                )
                . '<ul><li>'
                    . implode(
                        '</li><li>', 
                        array_map(
                            function($item) {
                                return '(<b>' . $item[1] . '</b>) ' 
                                    . preg_replace('%^\/?wp-content\/plugins\/' . preg_quote($this->_pluginSlug) . '\/%', '', $item[0]);
                            }, 
                            $fileSystem['plugins']['files']
                        )
                    )
                . ($fileSystem['plugins']['count'] > 10 ? '<li>...</li>' : '')
                . '</li></ul>';
        }
        
        // Prepare the files after uninstall
        $fsAfter = [
            'count' => 0,
            'size' => 0,
        ];
        foreach ($fileSystem as $fsData) {
            $fsAfter['count'] += $fsData['count'];
            $fsAfter['size'] += $fsData['size'];
        }
        
        // Database info
        $dataBaseOrig = $benchmarkData[Test_Benchmark::BENCH_CLEAN][Test_Benchmark::DATA_DIFF][Test_Benchmark::DATA_DIFF_DB];
        $dataBase = $benchmarkData[Test_Benchmark::BENCH_UNINSTALLED][Test_Benchmark::DATA_DIFF][Test_Benchmark::DATA_DIFF_DB];
        $dbSizeDelta = ($dataBase[Test_Benchmark::DATA_DIFF_DB_SIZE] - $dataBaseOrig[Test_Benchmark::DATA_DIFF_DB_SIZE]);
        
        // Tables did not remove successfully
        if (isset($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES]) && count($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES]) > 0) {
            shuffle($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES]);
            $errors[] = Seo::text(
                    Seo::TEXT_BENCH_FP_UNINSTALL_DB_TABLES,
                    '<b>' 
                        . number_format(count($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES])) 
                        . ' ' . (1 == count($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES]) ? 'table' : 'tables') 
                    . '</b>'
                )
                . '<ul><li>'
                    . implode(
                        '</li><li>', 
                        array_slice($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES], 0, 10)
                    )
                . '</li>'
                . (count($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES]) > 10 ? '<li>...</li>' : '')
                . '</ul>';
        }
        
        // Options did not remove successfully
        if (isset($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS]) && count($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS]) > 0) {
            shuffle($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS]);
            $errors[] = Seo::text(
                    Seo::TEXT_BENCH_FP_UNINSTALL_DB_OPTIONS,
                    '<b>' 
                        . number_format(count($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS])) 
                        . ' ' . (1 == count($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS]) ? 'option' : 'options') 
                    . '</b>'
                )
                . '<ul><li>'
                    . implode(
                        '</li><li>', 
                        array_slice($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS], 0, 10)
                    )
                . '</li>'
                . (count($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS]) > 10 ? '<li>...</li>' : '')
                . '</ul>';
        }
        
        // Prepare the title suffix
        $titleSuffix = ' [IO: ' . ($fsAfter['count'] > 0 ? ('&#x25B2;' . number_format($fsAfter['size'] / 1024 / 1024, 2) . 'MB') : '&#9989;') . ']';
        $titleSuffix .= ' [DB: ' . ($dbSizeDelta > 0 ? '&#x25B2;' : '&#x25BC;') . number_format(abs($dbSizeDelta) / 1024 / 1024, 2) . 'MB]';
        
        $this->getScore()->grade(
            'Uninstaller' . $titleSuffix,
            Seo::text(Seo::TEXT_BENCH_FP_UNINSTALL_FIX_DESC),
            count($errors),
            4,
            Seo::text(Seo::TEXT_BENCH_FP_UNINSTALL_FIX_SUCCESS),
            Seo::text(Seo::TEXT_BENCH_FP_UNINSTALL_FIX_FAILURE)
                .'<ul><li>'
                    . implode('</li><li>', $errors)
                . '</li></ul>',
            Test_Score::WEIGHT_IMPORTANT
        );
    }
    
    /**
     * Grade server footprint
     * 
     * @param array $pluginData
     * @param array $benchmarkData
     */
    protected function _runServerMetrics($pluginData, $benchmarkData) {
        // Store errors
        $errors = [];
        $errorsMax = [];
        
        // Store differences
        $allDiffsMemory = [];
        $allDiffsTime = [];
        
        // Prepare the table
        $table = '<table class="stats">'
            . '<thead>'
                . '<tr>'
                    . '<th>Page</th>'
                    . '<th>Memory (MB)</th>'
                    . '<th>CPU Time (ms)</th>'
                . '</tr>'
            . '</thead>'
            . '<tbody>';
        foreach ($benchmarkData[Test_Benchmark::BENCH_INSTALLED][Test_Benchmark::DATA_PAGES] as $page => $pageData) {
            $metrics = current($pageData[Test_Benchmark::DATA_PAGES_SERVER_METRICS]);
            if (!is_array($metrics) || 5 !== count($metrics) || 'GET' !== $metrics[0]) {
                continue;
            }
            
            // Prepare the differences
            $diffMemory = '';
            $diffTime = '';
            $tdClass = [];
            
            // Compare to before plugin installation
            if (isset(Test_Benchmark::DEFAULT_PAGES[$page])) {
                $metricsOrig = current($benchmarkData[Test_Benchmark::BENCH_CLEAN][Test_Benchmark::DATA_PAGES][$page][Test_Benchmark::DATA_PAGES_SERVER_METRICS]);
                if (!is_array($metricsOrig) || 5 !== count($metricsOrig) || 'GET' !== $metricsOrig[0]) {
                    continue;
                }
                $metrics[2] = $metrics[2] < 0 ? 0 : $metrics[2];
                $metrics[3] = $metrics[3] < 0 ? 0 : $metrics[3];
                $metricsOrig[2] = $metricsOrig[2] < 0 ? 0 : $metricsOrig[2];
                $metricsOrig[3] = $metricsOrig[3] < 0 ? 0 : $metricsOrig[3];

                // Store the differences
                $allDiffsMemory[] = $metrics[3] - $metricsOrig[3];
                $diffMemory = ' '
                    . ($metrics[3] > $metricsOrig[3] ? '<span class="nok">&#x25B2;' : '<span class="ok">&#x25BC;') 
                        . number_format(abs($metrics[3] - $metricsOrig[3]) / 1024 / 1024, 2) 
                    . '</span>';
                $allDiffsTime[] = $metrics[2] - $metricsOrig[2];
                $diffTime = ' '
                    . ($metrics[2] > $metricsOrig[2] ? '<span class="nok">&#x25B2;' : '<span class="ok">&#x25BC;') 
                        . number_format(abs($metrics[2] - $metricsOrig[2]) * 1000, 2) 
                    . '</span>';
            } else {
                // Too much memory
                if ($metrics[3] / 1024 / 1024 >= self::MAX_SERVER_MEMORY_TOTAL) {
                    $tdClass['m'] = 'nok';
                    if (!isset($errorsMax['m']) || $metrics['m'] > $errorsMax['m']) {
                        $errors['m'] = '<b>RAM</b>: '
                            . Seo::text(
                                Seo::TEXT_BENCH_FP_SERVER_MEM_TOTAL,
                                number_format(self::MAX_SERVER_MEMORY_TOTAL) . 'MB',
                                number_format($metrics[3] / 1024 / 1024, 2) . 'MB',
                                $page
                            );
                    }
                }
                
                // Too much CPU
                if ($metrics[2] * 1000 >= self::MAX_SERVER_TIME_TOTAL) {
                    $tdClass['t'] = 'nok';
                    if (!isset($errorsMax['t']) || $metrics['t'] > $errorsMax['t']) {
                        $errors['t'] = '<b>CPU</b>: '
                            . Seo::text(
                                Seo::TEXT_BENCH_FP_SERVER_CPU_TOTAL,
                                number_format(self::MAX_SERVER_TIME_TOTAL, 2) . 'ms',
                                number_format($metrics[2] * 1000, 2) . 'ms',
                                $page
                            );
                    }
                }
            }
            $table .= '<tr>'
                . '<td title="' . htmlentities($page, ENT_QUOTES) . '">' 
                    . '<b>' . htmlentities($pageData[Test_Benchmark::DATA_PAGES_TITLE]) . '</b> ' . $page 
                . '</td>'
                . '<td' . (isset($tdClass['m']) ? ' class="' . $tdClass['m'] . '"' : '') . '>' 
                    . number_format($metrics[3] / 1024 / 1024, 2) . $diffMemory 
                . '</td>'
                . '<td' . (isset($tdClass['t']) ? ' class="' . $tdClass['t'] . '"' : '') . '>' 
                    . number_format($metrics[2] * 1000, 2) . $diffTime
                . '</td>'
            . '</tr>';
        }
        $table .= '</tbody></table>';
        
        // Bird's eye view of memory
        $extraMemory = count($allDiffsMemory) > 1
                ? ((array_sum($allDiffsMemory) - max($allDiffsMemory)) / (count($allDiffsMemory) - 1)) / 1024 / 1024
                : 0;
        if ($extraMemory > self::MAX_SERVER_MEMORY_EXTRA) {
            $errors['me'] = '<b>Extra RAM</b>: '
                . Seo::text(
                    Seo::TEXT_BENCH_FP_SERVER_MEM_EXTRA,
                    number_format(self::MAX_SERVER_MEMORY_EXTRA) . 'MB',
                    number_format($extraMemory, 2) . 'MB',
                    $page
                );
        }
        
        // Bird's eye view of time
        $extraTime = count($allDiffsTime) > 1
                ? ((array_sum($allDiffsTime) - max($allDiffsTime)) / (count($allDiffsTime) - 1)) * 1000
                : 0;
        if ($extraTime > self::MAX_SERVER_TIME_EXTRA) {
            $errors['te'] = '<b>Extra CPU</b>: '
                . Seo::text(
                    Seo::TEXT_BENCH_FP_SERVER_CPU_EXTRA,
                    number_format(self::MAX_SERVER_TIME_EXTRA, 2) . 'ms',
                    number_format($extraTime, 2) . 'ms',
                    $page
                );
        }
        
        // Prepare the title suffix
        $titleSuffix = count($allDiffsMemory)
            ? (
                ' <span>[RAM: '
                . ($extraMemory > 0 ? '&#x25B2;' : '&#x25BC;')
                    . number_format(abs($extraMemory), 2) . 'MB'
                . ']</span>'
            )
            : '';
        $titleSuffix .= count($allDiffsTime)
            ? (
                ' <span>[CPU: '
                . ($extraTime > 0? '&#x25B2;' : '&#x25BC;')
                    . number_format(abs($extraTime), 2) . 'ms'
                . ']</span>'
            )
            : '';
        
        // Grade server-side footprint
        $this->getScore()->grade(
            'Server metrics' . $titleSuffix, 
            Seo::text(Seo::TEXT_BENCH_FP_SERVER_FIX_DESC, $pluginData[Test_1_About::DATA_PLUGIN_NAME]),
            count($errors),
            4,
            Seo::text(Seo::TEXT_BENCH_FP_SERVER_FIX_SUCCESS) . $table,
            Seo::text(Seo::TEXT_BENCH_FP_SERVER_FIX_FAILURE) 
                . '<ul><li>'
                    . implode('</li><li>', $errors)
                . '</li></ul>' 
                . $table
        );
    }
    
    /**
     * Grade storage footprint
     * 
     * @param array $pluginData
     * @param array $benchmarkData
     */
    protected function _runServerStorage($pluginData, $benchmarkData) {
        $errors = [];
        
        // Filesystem info
        $fileSystem = $benchmarkData[Test_Benchmark::BENCH_INSTALLED][Test_Benchmark::DATA_DIFF][Test_Benchmark::DATA_DIFF_FS] ?? [];
        
        // Database info
        $dataBaseOrig = $benchmarkData[Test_Benchmark::BENCH_CLEAN][Test_Benchmark::DATA_DIFF][Test_Benchmark::DATA_DIFF_DB];
        $dataBase = $benchmarkData[Test_Benchmark::BENCH_INSTALLED][Test_Benchmark::DATA_DIFF][Test_Benchmark::DATA_DIFF_DB];
        $dbSizeDelta = ($dataBase[Test_Benchmark::DATA_DIFF_DB_SIZE] - $dataBaseOrig[Test_Benchmark::DATA_DIFF_DB_SIZE]);
        
        // Filesystem errors
        if (isset($fileSystem['other'])) {
            shuffle($fileSystem['other']['files']);
            $errors[] = Seo::text(
                    Seo::TEXT_BENCH_FP_STORAGE_OUTSIDE,
                    $fileSystem['other']['count'] . ' ' . (1 == $fileSystem['other']['count'] ? 'file' : 'files'),
                    number_format($fileSystem['other']['size'] / 1024, 2) . 'KB',
                    '"wp-content/plugins/' . $this->_pluginSlug . '/" and "wp-content/uploads/"'
                )
                . '<ul><li>'
                    . implode(
                        '</li><li>', 
                        array_map(
                            function($item) {
                                return '(<b>' . $item[1] . '</b>) ' . $item[0];
                            }, 
                            $fileSystem['other']['files']
                        )
                    )
                . ($fileSystem['other']['count'] > 10 ? '<li>...</li>' : '')
                . '</li></ul>';
        }
        
        // Plugin too large
        if ($fileSystem['plugins']['size'] / 1024 / 1024 > self::MAX_IO_SIZE) {
            $errors[] = Seo::text(
                Seo::TEXT_BENCH_FP_STORAGE_IO_SIZE,
                self::MAX_IO_SIZE . 'MB',
                number_format($fileSystem['plugins']['size'] / 1024 / 1024, 2) . 'MB'
            );
        }
        if ($dbSizeDelta / 1024 / 1024 > self::MAX_IO_SIZE) {
            $errors[] = Seo::text(
                Seo::TEXT_BENCH_FP_STORAGE_DB_SIZE,
                self::MAX_DB_SIZE . 'MB',
                number_format($dbSizeDelta / 1024 / 1024, 2) . 'MB'
            );
        }
        
        // Prepare the title suffix
        $titleSuffix = '';
        if (isset($fileSystem['plugins'])) {
            $titleSuffix .= ' [IO: &#x25B2;' . number_format($fileSystem['plugins']['size'] / 1024 / 1024, 2) . 'MB]';
        }
        $titleSuffix .= ' [DB: ' . ($dbSizeDelta > 0 ? '&#x25B2;' : '&#x25BC;') . number_format(abs($dbSizeDelta) / 1024 / 1024, 2) . 'MB]';
        
        // Prepare the table
        $table = '<div class="col">';
        
        // Add the filesystem radial
        $fsRadialValue = round(100 * ($fileSystem['plugins']['size'] / 1024 / 1024) / self::MAX_IO_SIZE, 2);
        if ($fsRadialValue > 100) {
            $fsRadialValue = 100;
        }
        $table .= '<div class="col col-sm-6">'
            . '<span data-role="radial"'
                . ' data-radial-hue="0"'
                . ' data-radial-value="' . $fsRadialValue . '"'
                . ' data-radial-max="100"'
                . ' data-radial-text="' . number_format($fileSystem['plugins']['size'] / 1024 / 1024, 2) . 'MB">'
                . ' Filesystem: ' . (
                    isset($fileSystem['plugins']) 
                        ? ('<b>' . number_format($fileSystem['plugins']['count']) . '</b> new ' . (1 == $fileSystem['plugins']['count'] ? 'file' : 'files'))
                        : 'no files'
                )
            . '</span>'
        . '</div>';
        
        // Add the database radial
        $dbRadialValue = round(100 * ($dbSizeDelta / 1024 / 1024) / self::MAX_IO_SIZE, 2);
        if ($dbRadialValue > 100) {
            $dbRadialValue = 100;
        }
        $table .= '<div class="col col-sm-6">'
            . '<span data-role="radial"'
                . ' data-radial-hue="0"'
                . ' data-radial-value="' . $dbRadialValue . '"'
                . ' data-radial-max="100"'
                . ' data-radial-text="' . number_format($dbSizeDelta / 1024 / 1024, 2) . 'MB">'
                . ' Database: ' . (
                    isset($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES]) && count($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES]) 
                        ? (
                            '<b>' . number_format(count($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES])) . '</b> new' 
                            . ' ' . (1 == count($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES]) ? 'table' : 'tables')
                        )
                        : 'no new tables'
                ) . ', ' . (
                    isset($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS]) && count($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS]) 
                        ? (
                            '<b>' . number_format(count($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS])) . '</b> new' 
                            . ' ' . (1 == count($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS]) ? 'option' : 'options')
                        )
                        : 'no new options'
                )
            . '</span>'
        . '</div>';
        
        // Close the table
        $table .= '</div>';
        
        // Append the new DB tables
        if (isset($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES]) && count($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES])) {
            $table .= '<table class="stats">'
                . '<thead>'
                    . '<tr>'
                        . '<th>New tables</th>'
                    . '</tr>'
                . '</thead>'
                . '<tbody>';
            shuffle($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES]);
            $indexTables = 0;
            foreach ($dataBase[Test_Benchmark::DATA_DIFF_DB_TABLES] as $tableName) {
                if ($indexTables++ >= 10) {
                    $table .= '<tr><td>...</td></tr>';
                    break;
                }
                $table .= '<tr><td>' . $tableName . '</td></tr>';
            }
            $table .= '</tbody></table>';
        }
        
        // Append the new options
        if (isset($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS]) && count($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS])) {
            $table .= '<table class="stats">'
                . '<thead>'
                    . '<tr>'
                        . '<th>New WordPress options</th>'
                    . '</tr>'
                . '</thead>'
                . '<tbody>';
            shuffle($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS]);
            $indexOptions = 0;
            foreach ($dataBase[Test_Benchmark::DATA_DIFF_DB_OPTIONS] as $optionName) {
                if ($indexOptions++ >= 10) {
                    $table .= '<tr><td>...</td></tr>';
                    break;
                }
                $table .= '<tr><td>' . $optionName . '</td></tr>';
            }
            $table .= '</tbody></table>';
        }
        
        // Add the grade
        $this->getScore()->grade(
            'Server storage' . $titleSuffix, 
            Seo::text(Seo::TEXT_BENCH_FP_STORAGE_FIX_DESC),
            count($errors),
            3,
            Seo::text(Seo::TEXT_BENCH_FP_STORAGE_FIX_SUCCESS) . $table,
            Seo::text(Seo::TEXT_BENCH_FP_STORAGE_FIX_FAILURE) 
                . '<ul><li>'
                    . implode('</li><li>', $errors)
                . '</li></ul>' 
                . $table
        );
    }

    /**
     * Grade browser footprint
     * 
     * @param array $pluginData
     * @param array $benchmarkData
     */
    protected function _runBrowserMetrics($pluginData, $benchmarkData) {
        $limits = [
            'Nodes'          => self::MAX_BROWSER_NODES, 
            'JSHeapUsedSize' => self::MAX_BROWSER_MEMORY,
            'ScriptDuration' => self::MAX_BROWSER_SCRIPT,
            'LayoutDuration' => self::MAX_BROWSER_LAYOUT
        ];
        
        // Store errors
        $errors = [];
        $errorsMax = [];
        
        // Prepare the table
        $table = '<table class="stats">'
            . '<thead>'
                . '<tr>'
                    . '<th>Page</th>'
                    . '<th>Nodes</th>'
                    . '<th>Memory (MB)</th>'
                    . '<th>Script (ms)</th>'
                    . '<th>Layout (ms)</th>'
                . '</tr>'
            . '</thead>'
            . '<tbody>';
        foreach ($benchmarkData[Test_Benchmark::BENCH_INSTALLED][Test_Benchmark::DATA_PAGES] as $page => $pageData) {
            $metrics = $pageData[Test_Benchmark::DATA_PAGES_BROWSER_METRICS];
            $diffMemory = '';
            $diffNodes = '';
            $diffScriptD = '';
            $diffLayoutD = '';
            $tdClass = [];
            
            // Compare to before plugin installation
            if (isset(Test_Benchmark::DEFAULT_PAGES[$page])) {
                $metricsOrig = $benchmarkData[Test_Benchmark::BENCH_CLEAN][Test_Benchmark::DATA_PAGES][$page][Test_Benchmark::DATA_PAGES_BROWSER_METRICS];
                $diffNodes = ' '
                    . ($metrics['Nodes'] > $metricsOrig['Nodes'] ? '<span class="nok">&#x25B2;' : '<span class="ok">&#x25BC;') 
                        . number_format(abs($metrics['Nodes'] - $metricsOrig['Nodes'])) 
                    . '</span>';
                $diffMemory = ' '
                    . ($metrics['JSHeapUsedSize'] > $metricsOrig['JSHeapUsedSize'] ? '<span class="nok">&#x25B2;' : '<span class="ok">&#x25BC;') 
                        . number_format(abs($metrics['JSHeapUsedSize'] - $metricsOrig['JSHeapUsedSize']) / 1024 / 1024, 2) 
                    . '</span>';
                $diffScriptD = ' '
                    . ($metrics['ScriptDuration'] > $metricsOrig['ScriptDuration'] ? '<span class="nok">&#x25B2;' : '<span class="ok">&#x25BC;') 
                        . number_format(abs($metrics['ScriptDuration'] - $metricsOrig['ScriptDuration']) * 1000, 2) 
                    . '</span>';
                $diffLayoutD = ' '
                    . ($metrics['LayoutDuration'] > $metricsOrig['LayoutDuration'] ? '<span class="nok">&#x25B2;' : '<span class="ok">&#x25BC;') 
                        . number_format(abs($metrics['LayoutDuration'] - $metricsOrig['LayoutDuration']) * 1000, 2) 
                    . '</span>';
            } else {
                foreach ($limits as $blKey => $blValue) {
                    // Prepare the adjusted value
                    $bmValue = $metrics[$blKey];
                    switch ($blKey) {
                        case 'JSHeapUsedSize':
                            $bmValue /= (1024 * 1024);
                            break;
                        
                        case 'ScriptDuration':
                        case 'LayoutDuration':
                            $bmValue *= 1000;
                            break;
                    }
                    if ($bmValue >= $blValue) {
                        $tdClass[$blKey] = 'nok';
                        if (!isset($errorsMax[$blKey]) || $metrics[$blKey] > $errorsMax[$blKey]) {
                            $errorsMax[$blKey] = $metrics[$blKey];
                            switch ($blKey) {
                                case 'Nodes':
                                    $errors[$blKey] = '<b>Nodes</b>: '
                                        . Seo::text(
                                            Seo::TEXT_BENCH_FP_BROWSER_NODES,
                                            number_format($blValue),
                                            number_format($bmValue),
                                            $page
                                        );
                                    break;
                                
                                case 'JSHeapUsedSize':
                                    $errors[$blKey] = '<b>Memory</b>: '
                                        . Seo::text(
                                            Seo::TEXT_BENCH_FP_BROWSER_MEMORY,
                                            number_format($blValue, 2) . 'MB',
                                            number_format($bmValue, 2) . 'MB',
                                            $page
                                        );
                                    break;
                                
                                case 'ScriptDuration':
                                    $errors[$blKey] = '<b>Script duration</b>: '
                                        . Seo::text(
                                            Seo::TEXT_BENCH_FP_BROWSER_SCRIPT,
                                            number_format($blValue, 2) . 'ms',
                                            number_format($bmValue, 2) . 'ms',
                                            $page
                                        );
                                    break;
                                
                                case 'LayoutDuration':
                                    $errors[$blKey] = '<b>Layout duration</b>:'
                                        . Seo::text(
                                            Seo::TEXT_BENCH_FP_BROWSER_LAYOUT,
                                            number_format($blValue, 2) . 'ms',
                                            number_format($bmValue, 2) . 'ms',
                                            $page
                                        );
                                    break;
                            }
                        }
                    }
                }
            }
            
            $table .= '<tr>'
                . '<td title="' . htmlentities($page, ENT_QUOTES) . '">' 
                    . '<b>' . htmlentities($pageData[Test_Benchmark::DATA_PAGES_TITLE]) . '</b> ' . $page 
                . '</td>'
                . '<td' . (isset($tdClass['Nodes']) ? ' class="' . $tdClass['Nodes'] . '"' : '') . '>' 
                    . number_format($metrics['Nodes']) . $diffNodes 
                . '</td>'
                . '<td' . (isset($tdClass['JSHeapUsedSize']) ? ' class="' . $tdClass['JSHeapUsedSize'] . '"' : '') . '>' 
                    . number_format($metrics['JSHeapUsedSize'] / 1024 / 1024, 2) . $diffMemory 
                . '</td>'
                . '<td' . (isset($tdClass['ScriptDuration']) ? ' class="' . $tdClass['ScriptDuration'] . '"' : '') . '>' 
                    . number_format($metrics['ScriptDuration'] * 1000, 2) . $diffScriptD 
                . '</td>'
                . '<td' . (isset($tdClass['LayoutDuration']) ? ' class="' . $tdClass['LayoutDuration'] . '"' : '') . '>' 
                    . number_format($metrics['LayoutDuration'] * 1000, 2) . $diffLayoutD 
                . '</td>'
            . '</tr>';
        }
        $table .= '</tbody></table>';
        
        // Grade footprint
        $this->getScore()->grade(
            'Browser metrics', 
            Seo::text(Seo::TEXT_BENCH_FP_BROWSER_FIX_DESC, $pluginData[Test_1_About::DATA_PLUGIN_NAME]),
            count($errors),
            4,
            Seo::text(Seo::TEXT_BENCH_FP_BROWSER_FIX_SUCCESS) . $table,
            Seo::text(Seo::TEXT_BENCH_FP_BROWSER_FIX_FAILURE) 
                .'<ul><li>'
                    . implode('</li><li>', $errors)
                . '</li></ul>' 
                . $table
        );
    }
}

/*EOF*/