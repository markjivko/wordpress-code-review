<?php
/**
 * Potrivit - Render/Listing/References
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Render_Listing_References {
 
    /**
     * Inject references to other plugins in all index.html files
     * HTML tags "aside" and "nav" are targeted
     */
    public static function run() {
        foreach (Cache_Search::getAll() as $pluginSlug) {
            if (!is_file($indexPath = Config::get()->outputPath() . '/' . Render_Listing::FOLDER_PLUGIN . '/' . $pluginSlug . '/index.html')) {
                Console::log('Render not found for ' . $pluginSlug . ', removing from cache...', false);
                Cache_Data::get($pluginSlug)->fileDelete();
                Cache_Data::gc($pluginSlug);
                continue;
            }
            
            // Replace references
            file_put_contents(
                $indexPath, 
                preg_replace_callback(
                    '%<(aside|nav|footer)>(.*?)</\1>%ims', 
                    function($match) use($pluginSlug) {
                        $result = '<' . strtolower($match[1]) . '>';

                        switch ($match[1]) {
                            case 'footer':
                                $result .= '<div class="container">'
                                    . '&copy; ' . date('Y')
                                    . ' <a target="_blank" rel="noreferrer nofollow" href="https://translate.google.com/?sl=ro&amp;text=potrivit">potrivit</a>'
                                    . ' by <a target="_blank" rel="noreferrer nofollow author" href="https://markjivko.com">Mark Jivko</a>'
                                    . ' <a href="/privacy-policy/">Privacy Policy</a>'
                                . '</div>';
                                break;
                            
                            case 'aside':
                                // Aside already computed
                                if (!Run_Render::getOptionPurge() && 6 === substr_count($match[2], 'class="card"')) {
                                    $result .= $match[2];
                                } else {
                                    /* @var $data Cache_Data */
                                    foreach (Cache_Search::getSimilar($pluginSlug) as $data) {
                                        $result .= $data->renderWidget();
                                    }
                                }
                                break;

                            case 'nav':
                                /* @var $dataPrev Cache_Data */
                                /* @var $dataNext Cache_Data */
                                list($dataPrev, $dataNext) = Cache_Search::getSiblings($pluginSlug);

                                $result .= '<ul>'
                                    . '<li>'
                                        . '<a title="' . htmlentities('Code review of ' . html_entity_decode($dataPrev->getInfo()[Test_1_About::DATA_PLUGIN_NAME])) . '" href="https://' . Config::get()->domainLive() . '/' . Render_Listing::FOLDER_PLUGIN . '/' . $dataPrev->getSlug() . '/">'
                                            . ($dataPrev->getInfo()[Test_1_About::DATA_PLUGIN_NAME])
                                        . '</a>'
                                    . '</li>'
                                    . '<li>'
                                        . '<a title="' . htmlentities('Code review of ' . html_entity_decode($dataNext->getInfo()[Test_1_About::DATA_PLUGIN_NAME])) . '" href="https://' . Config::get()->domainLive() . '/' . Render_Listing::FOLDER_PLUGIN . '/' . $dataNext->getSlug() . '/">'
                                            . ($dataNext->getInfo()[Test_1_About::DATA_PLUGIN_NAME])
                                        . '</a>'
                                    . '</li>'
                                . '</ul>';
                                break;
                        }

                        return $result .= '</' . strtolower($match[1]) . '>';
                    }, 
                    file_get_contents($indexPath)
                )
            );
        }
    }
}

/*EOF*/