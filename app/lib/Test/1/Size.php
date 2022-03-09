<?php
/**
 * Potrivit - Test
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Test_1_Size extends Test_Case {
    // QA parameters
    const MAX_COMPRESSION_SAVINGS = 15; //%
    
    /**
     * Run a test case
     * 
     * @return mixed Test result
     * @throws Exception
     */
    public function run() {
        // Get original image sizes
        $sizesOrig = [];
        foreach (Folder::getIterator("/var/www/wordpress/wp-content/plugins/{$this->_pluginSlug}/") as /*@var $item SplFileInfo*/ $item) {
            if ($item->isFile()) {
                if ('png' === strtolower(pathinfo($item->getPathname(), PATHINFO_EXTENSION))) {
                    $sizesOrig[$item->getPathname()] = filesize($item->getPathname());
                }
            }
        }
        $sizesOrigTotal = array_sum($sizesOrig);
        
        // Prepare the optimized sizes
        $sizesOptim = [];
        
        // Invalid files
        $invalidPngFiles = [];
        
        // Select a maximum of 5 files
        $sizesFilePaths = array_keys($sizesOrig);
        if (count($sizesOrig) > 5) {
            for ($i = 1; $i <= 5; $i++) {
                do {
                    $filePath = $sizesFilePaths[mt_rand(0, count($sizesOrig) - 1)];
                    if (!isset($sizesOptim[$filePath])) {
                        break;
                    }
                } while(true);
                
                if (false === $optimizedSize = $this->_optimize($filePath)) {
                    $invalidPngFiles[] = basename(dirname($filePath)) . '/' . basename($filePath);
                    $optimizedSize = 0;
                }
                
                $sizesOptim[$filePath] = [
                    $sizesOrig[$filePath], 
                    $optimizedSize
                ];
            }
        } else {
            shuffle($sizesFilePaths);
            foreach ($sizesFilePaths as $filePath) {
                if (false === $optimizedSize = $this->_optimize($filePath)) {
                    $invalidPngFiles[] = basename(dirname($filePath)) . '/' . basename($filePath);
                    $optimizedSize = 0;
                }
                
                $sizesOptim[$filePath] = [
                    $sizesOrig[$filePath], 
                    $optimizedSize
                ];
            }
        }

        // Get the size difference
        $sizeSavings = array_map(
            function($item) {
                return $item[0] <= $item[1]
                    ? 0
                    : round(100 * ($item[0] - $item[1]) / $item[0], 2);
            }, 
            $sizesOptim
        );
        $sizeSavingsAvg = count($sizeSavings)
            ? round(array_sum($sizeSavings) / count($sizeSavings), 2)
            : 0;

        // Prepare the savings table
        $html = Seo::text(Seo::TEXT_INFO_SIZE_NO_IMAGES); 
        if (count($sizeSavings)) {
            // Total image sizes after optimization
            $sizeSavingsTotal = round($sizesOrigTotal * $sizeSavingsAvg / 100, 0);
            
            // Prepare the HTML
            $html = '<b>' . count($sizesOrig) . '</b>' . ($sizeSavingsAvg > self::MAX_COMPRESSION_SAVINGS ? '' : ' compressed') . ' PNG ' . (1 == count($sizesOrig) ? 'file' : 'files')
                . ' ' . (1 == count($sizesOrig) ? 'occupies' : 'occupy') . ' <b>' . (number_format($sizesOrigTotal / 1024 / 1024, 2)) . '</b>MB'
                . (
                    $sizeSavingsAvg > self::MAX_COMPRESSION_SAVINGS 
                        ? (' with <b>' . (number_format($sizeSavingsTotal / 1024 / 1024, 2)) . '</b>MB in potential savings')
                        : ''
                )
                . '<div data-role="comp" data-comp="' . $sizeSavingsAvg . '">Potential savings</div>'
                . '<table class="stats">'
                    . '<thead>'
                        . '<tr>'
                            . '<th colspan="4">Compression of ' . count($sizeSavings) . ' random PNG ' . (1 == count($sizeSavings) ? 'file' : 'files') . ' using pngquant</th>'
                        . '</tr>'
                        . '<tr>'
                            . '<th>File</th>'
                            . '<th>Size - original</th>'
                            . '<th>Size - compressed</th>'
                            . '<th>Savings</th>'
                        . '</tr>'
                    . '</thead>'
                    . '<tbody>';
            foreach ($sizeSavings as $filePath => $savingsPercent){
                $relativePath = preg_replace('%^\/var\/www\/wordpress\/wp-content\/plugins\/' . preg_quote($this->_pluginSlug) . '\/%', '', $filePath);
                $html .= '<tr' . (0 === $sizesOptim[$filePath][1] ? ' class="error"' : '') . '>'
                    . '<td title="' . htmlentities($relativePath) . '">' . (0 === $sizesOptim[$filePath][1] ? '(invalid) ' : '') . $relativePath . '</td>'
                    . '<td>' . number_format($sizesOptim[$filePath][0]/1024, 2) . 'KB</td>'
                    . '<td>' . number_format($sizesOptim[$filePath][1]/1024, 2) . 'KB</td>'
                    . '<td>' . (
                            $savingsPercent > 0.5 
                                ? ('<span class="ok">&#x25BC; ' . number_format($savingsPercent, 2) . '%</span>')
                                : (number_format($savingsPercent, 2) . '%')
                        ) . '</td>'
                . '</tr>';
            }
            $html .= '</tbody></table>';
        }
        
        $testsTotal = 2;
        $testsFailed = 0;
        
        // Not compressed enough
        if ($sizeSavingsAvg > self::MAX_COMPRESSION_SAVINGS && $sizesOrigTotal / 1024 > 500) {
            $testsFailed++;
        }
        
        // Invalid PNG files
        if (count($invalidPngFiles)) {
            $testsFailed++;
        }
        
        // Grade user-side errors
        $this->getScore()->grade(
            'Image compression', 
            Seo::text(Seo::TEXT_INFO_SIZE_FIX_DESC),
            $testsFailed,
            $testsTotal,
            $html,
            $html
        );
    }
    
    /**
     * Optimize a PNG file and return the final size in bytes
     * 
     * @param string $filePath Path to PNG file
     * @return int|boolean File size after optimization or false if invalid PNG file
     */
    protected function _optimize($filePath) {
        $outputPath = Temp::getPath(Temp::FOLDER_IMAGES) . '/file.png';
        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0777, true);
        }
        
        // Minimize the file
        shell_exec("pngquant --force --output '$outputPath' --strip -- '$filePath'");
        
        // Store the file size
        $result = (is_file($outputPath) ? filesize($outputPath) : false);
        
        // Remove the temporary file
        is_file($outputPath) && @unlink($outputPath);
        
        return $result;
    }

}

/*EOF*/