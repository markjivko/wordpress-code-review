<?php

/**
 * Potrivit - Render
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Render_Helper {
    
    /**
     * Prepare the current plugin icon URL
     * 
     * @return string Icon URL
     */
    public static function getIconUrl() {
        return Tester::getData()[Tester::DATA_ACTIVE][Test_1_About::class][Test_1_About::DATA_PLUGIN_ICON];
    }

    /**
     * Get the total rating information for this plugin
     * 
     * @return array Array of [rating value, rating count]
     */
    public static function getRating() {
        $ratingTests = 0;
        $ratingCount = 0;
        $ratingValue = 0;
        $importantFailure = false;
        
        // Go through all the tests
        foreach (Tester::getData()[Tester::DATA_ACTIVE] as $testClass => $data) {
            if ($data instanceof Exception) {
                break;
            }
            
            /*@var $score Test_Score*/
            $score = Tester::getData()[Tester::SCORE][$testClass];
            
            // Append the totals
            $ratingTests += array_sum(
                array_map(
                    function($item) {
                        return $item[3];
                    },
                    $score->getList()
                )
            );
            $ratingCount += array_sum(
                array_map(
                    function($item) {
                        return $item[3] * $item[5];
                    },
                    $score->getList()
                )
            );
            $ratingValue += count($score->getList()) 
                ? array_sum(
                    array_map(
                        function($item) use(&$importantFailure) {
                            if (!$importantFailure && $item[5] >= Test_Score::WEIGHT_CRITICAL && $item[2] < 50) {
                                $importantFailure = true;
                            }
                            return $item[2] * $item[3] * $item[5];
                        },
                        $score->getList()
                    )
                )
                : 0;
        }
        
        // Prepare the final rating
        $finalRating = $importantFailure
            ? 10
            : (
                $ratingCount > 0 
                    ? round($ratingValue / $ratingCount, 0) 
                    : 100
            );
        
        return [
            $finalRating < 10 ? 10 : $finalRating,
            $ratingTests > 0 ? $ratingTests : 1
        ];
    }
    
    /**
     * Get the ld+json definition for this plugin
     * 
     * @return string JSON definition
     */
    public static function getLdJson() {
        // Get the rating
        list($ratingValue, $ratingCount) = self::getRating();
        $pluginStats = Tester::getData()[Tester::DATA_ACTIVE][Test_1_About::class][Test_1_About::DATA_PLUGIN_STATS];
        $pluginDesc = isset(Tester::getData()[Tester::DATA_ACTIVE][Test_1_About::class][Test_1_About::DATA_PLUGIN_DESC_MAIN])
            ? Tester::getData()[Tester::DATA_ACTIVE][Test_1_About::class][Test_1_About::DATA_PLUGIN_DESC_MAIN]
            : (Tester::getData()[Tester::DATA_ACTIVE][Test_1_About::class][Test_1_About::DATA_PLUGIN_NAME] . ' WordPress Plugin');
        $pluginAuthors = Cache_Data::get(Tester::getSlug())->getAuthors();
        
        // Prepare the data
        $ldJson = [
            '@context'            => 'https://schema.org/',
            '@type'               => ['SoftwareApplication'],
            'sku'                 => 'wp.org/plugin/' . Tester::getSlug(),
            'brand'               => [
                '@type' => 'Brand',
                'name'  => count($pluginAuthors) ? ucfirst(current($pluginAuthors)) : 'WordPress Plugin'
            ],
            'review'              => [
                '@type'        => 'Review',
                'url'          => 'https://' . Config::get()->domainLive() . '/' . Render_Listing::FOLDER_PLUGIN . '/' . Tester::getSlug() . '/',
                'reviewRating' => [
                    '@type'             => 'Rating',
                    'reviewAspect'      => 'Code Review',
                    'ratingValue'       => $ratingValue,
                    'worstRating'       => 1,
                    'bestRating'        => 100,
                    'ratingExplanation' => "$ratingCount tests performed",
                ],
                'reviewBody'   => self::getDescription(),
                'author'       => [
                    '@type' => 'Person',
                    'name'  => 'Mark Jivko',
                ],
                'publisher'    => [
                    '@type' => 'Organization',
                    'name'  => ucfirst(Config::get()->domainLive()),
                    'url'   => 'https://' . Config::get()->domainLive()
                ],
            ],
            'applicationCategory' => 'Plugin',
            'operatingSystem'     => 'WordPress',
            'name'                => 'Code Review: ' . Tester::getData()[Tester::DATA_ACTIVE][Test_1_About::class][Test_1_About::DATA_PLUGIN_NAME],
            'description'         => self::getDescription(),
            'softwareVersion'     => Config::get()->version() . ' (' . Tester::getData()[Tester::DATA_ACTIVE][Test_1_About::class][Test_1_About::DATA_PLUGIN_VERSION] . ')',
            'url'                 => 'https://' . Config::get()->domainLive() .'/' . Render_Listing::FOLDER_PLUGIN . '/' . Tester::getSlug() . '/',
            'image'               => self::getIconUrl(),
            'offers'              => [
                '@type'           => 'Offer',
                'url'             => 'https://wordpress.org/plugins/' . Tester::getSlug() . '/',
                'price'           => '0.00',
                'priceCurrency'   => 'USD',
                'priceValidUntil' => '2121-01-01',
                'availability'    => 'https://schema.org/InStock',
                'seller'          => [
                    '@type' => 'Organization',
                    'name'  => 'WordPress.org',
                    'url'   => 'https://wordpress.org',
                ],
            ],
        ];
        
        // Valid aggregate rating
        if ($pluginStats[Cache_Fetch::STAT_RATING_VALUE] >= 1 
            && $pluginStats[Cache_Fetch::STAT_RATING_COUNT] >= 1) {
            $ldJson['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => round($pluginStats[Cache_Fetch::STAT_RATING_VALUE], 2),
                'bestRating'  => 5,
                'worstRating' => 1,
                'ratingCount' => $pluginStats[Cache_Fetch::STAT_RATING_COUNT]
            ];
        }
        
        return json_encode($ldJson);
    }
    
    /**
     * Get the current page title
     * 
     * @return string
     */
    public static function getTitle() {
        return Tester::getData()[Tester::DATA_ACTIVE][Test_1_About::class][Test_1_About::DATA_PLUGIN_NAME] . ' | Code Review | ' . Config::get()->siteName();
    }
    
    /**
     * Get the current page description; cached
     * 
     * @return string
     */
    public static function getDescription() {
        $cacheData = Cache_Data::get(Tester::getSlug());
        
        // Prepare the plugin data
        $pluginData = $cacheData->getInfo();

        // Prepare the data set
        $dataSet = array(
            '__SLUG__'     => $cacheData->getSlug(),
            '__NAME__'     => $pluginData[Test_1_About::DATA_PLUGIN_NAME],
            '__VER__'      => $pluginData[Test_1_About::DATA_PLUGIN_VERSION],
            '__VER_PHP__'  => $pluginData[Test_1_About::DATA_PLUGIN_REQ_PHP] ?? '5.6',
            '__VER_WP__'   => $pluginData[Test_1_About::DATA_PLUGIN_REQ] ?? '5.0',
            '__VER_WP_T__' => $pluginData[Test_1_About::DATA_PLUGIN_TESTED] ?? '5.0',
        );

        // Prepare the scores
        foreach (Tester::getData()[Tester::SCORE] as $className => /*@var $score Test_Score*/$score) {
            // Prepare the score key
            $scoreKey = '__SCORE_' . strtoupper(preg_replace('%^Test_%i', '', $className)) . '__';

            // Store the total
            $dataSet[$scoreKey] = $score->getTotal();
        }

        // Get the rating
        list($dataSet['__RATING_VALUE__'], $dataSet['__RATING_COUNT__']) = self::getRating();
        
        return str_replace(
            array_keys($dataSet), 
            array_values($dataSet), 
            Seo::desc($dataSet['__RATING_VALUE__'])
        );
    }
    
    /**
     * Compress an HTML page, removing white space from the <b>body</b> tag
     * 
     * @param string $html HTML5 document
     * @return string
     */
    public static function compressHtml($html) {
        return preg_replace_callback(
            '%(<\s*(body|script)\b[^<>]*?>)(.*?)(<\s*/\s*\2\s*>)%ims', 
            function($matches) {
                return $matches[1] 
                    . preg_replace(
                        array('%[\r\n]+\s*%','%\s{2,}%'), 
                        array('', ' '), 
                        trim($matches[3])
                    ) 
                    . $matches[4];
            }, 
            $html
        );
    }
}

/*EOF*/