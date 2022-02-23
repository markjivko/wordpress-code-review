<?php
/**
 * Potrivit - Render/Listing/Index
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Render_Listing_Index {
    
    /**
     * Prepare the front page
     */
    public static function run() {
        $pages = [
            'index.phtml'    => 'index.html',
            'privacy.phtml'  => 'privacy-policy/index.html',
            'redirect.phtml' => '_redirects',
        ];
        
        // Create the index files
        foreach ($pages as $template => $page) {
            $parentDir = dirname(Config::get()->outputPath() . '/' . $page);
            if (!is_dir($parentDir)) {
                mkdir($parentDir, 0777, true);
            }
            
            ob_start();
            require ROOT . '/res/layout/assets/' . $template;
            $content = ob_get_clean();

            // Store the content
            file_put_contents(
                Config::get()->outputPath() . '/' . $page, 
                Config::get()->production()
                    ? Render_Helper::compressHtml($content)
                    : $content
            );
        }
    }
}