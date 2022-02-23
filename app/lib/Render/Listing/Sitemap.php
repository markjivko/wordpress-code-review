<?php
/**
 * Potrivit - Render/Listing/JSON Search
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Render_Listing_Sitemap {
    
    const URLS_PER_SITEMAP = 45000;
    
    /**
     * Prepare the XML sitemap files
     */
    public static function run() {
        $xmlDir = Config::get()->outputPath() . '/' . Render_Listing::FOLDER_MAIN . '/xml';
        if (!is_dir($xmlDir)) {
            mkdir($xmlDir, 0755, true);
        } else {
            shell_exec("rm -rf '$xmlDir/*'");
        }
        
        // Prepare the index
        $index = 0;
        
        // Prepare the cache
        $xmlData = array();
        
        // Store the front page
        $addedFrontPage = false;
        
        // Go through the plugins
        foreach (Cache_Search::getAll() as $pluginSlug) {
            $fileName = 'sitemap-' . (intval($index++ / self::URLS_PER_SITEMAP) + 1);
            if (!isset($xmlData[$fileName])) {
                $xmlData[$fileName] = '';
            }
            
            // Store the front page
            if (!$addedFrontPage) {
                $xmlData[$fileName] .= '<url>'
                    . '<loc>https://' . Config::get()->domainLive() . '/</loc>' 
                    . '<changefreq>daily</changefreq>'
                    . '<priority>1</priority>'
                . '</url>';
                $addedFrontPage = true;
            }
            
            // Store the URL definition
            $xmlData[$fileName] .= '<url>'
                . '<loc>https://' . Config::get()->domainLive() . '/' . Render_Listing::FOLDER_PLUGIN . '/' . $pluginSlug . '/</loc>' 
                . '<changefreq>daily</changefreq>'
            . '</url>';
        }
        
        // Store the sitemaps files
        foreach ($xmlData as $fileName => $urlDef) {
            file_put_contents(
                $xmlDir . '/' . $fileName . '.xml', 
                '<?xml version="1.0" encoding="UTF-8"?>'
                . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
                    . $urlDef
                . '</urlset>'
            );
        }
        
        // Create sitemap.xml
        file_put_contents(
            Config::get()->outputPath() . '/sitemap.xml', 
            '<?xml version="1.0" encoding="UTF-8"?>'
                . '<sitemapindex'
                    . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
                    . ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd"'
                    . ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
                        . implode(
                            '', 
                            array_map(
                                function($fileName) {
                                    return '<sitemap>'
                                        . '<loc>https://' . Config::get()->domainLive() . '/' . Render_Listing::FOLDER_MAIN . '/xml/' . $fileName . '.xml</loc>'
                                    . '</sitemap>';
                                }, 
                                array_keys($xmlData)
                            )
                        )
                . '</sitemapindex>'
        );
    }
}

/*EOF*/