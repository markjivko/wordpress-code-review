<?php
/**
 * Potrivit - Test
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Test_1_About extends Test_Case {
    
    /**
     * Mark the presence of a .git folder in this plugin
     * 
     * @var boolean
     */
    public static $gitFolderFound = false;
    
    const DATA_PLUGIN_FILE        = 'pluginFile';
    const DATA_PLUGIN_NAME        = 'pluginName';
    const DATA_PLUGIN_NAME_MAIN   = 'pluginNameMain';
    const DATA_PLUGIN_VERSION     = 'pluginVersion';
    const DATA_PLUGIN_DESC        = 'pluginDesc';
    const DATA_PLUGIN_DESC_MAIN   = 'pluginDescMain';
    const DATA_PLUGIN_ICON        = 'pluginIcon';
    const DATA_PLUGIN_TEXT_DOMAIN = 'pluginTextDomain';
    const DATA_PLUGIN_PATH_DOMAIN = 'pluginPathDomain';
    const DATA_PLUGIN_STATS       = 'pluginStats';
    const DATA_PLUGIN_COMMITS     = 'pluginCommits';
    const DATA_PLUGIN_ASSETS      = 'pluginAssets';
    const DATA_PLUGIN_SCREENSHOTS = 'pluginScreenshots';
    
    const DATA_PLUGIN_CONTRIB     = 'pluginContrib';
    const DATA_PLUGIN_LINK        = 'pluginLink';
    const DATA_PLUGIN_DONATE      = 'pluginDonate';
    const DATA_PLUGIN_TAGS        = 'pluginTags';
    const DATA_PLUGIN_REQ         = 'pluginReq';
    const DATA_PLUGIN_REQ_PHP     = 'pluginReqPhp';
    const DATA_PLUGIN_TESTED      = 'pluginTested';
    const DATA_PLUGIN_BRANCH      = 'pluginBranch';
    const DATA_PLUGIN_LICENSE     = 'pluginLicense';
    const DATA_PLUGIN_LICENSE_URI = 'pluginLicenseUri';
    
    /**
     * Run a test case
     * 
     * @return mixed Test result
     * @throws Exception
     */
    public function run() {
        $data = array();
        $invalidKeys = array();
        
        // Get the plugin info
        $readmePath = current(
            array_filter(
                glob(WP_PLUGINS . '/' . $this->_pluginSlug . '/*.txt'),
                function($path) {
                    return 'readme.txt' === strtolower(basename($path));
                }
            )
        );
        if (!strlen($readmePath)) {
            Render_Listing::deletePlugin($this->_pluginSlug, true);
            throw new Exception('Readme.txt is missing');
        }
        
        // Get the content
        $readmeContent = file_get_contents($readmePath);
        if (!preg_match('%(\={2,})\s*(.*?)\s*\1(.*?)(?=[=#]+\s*Description\s*[=#]+\s*[\r\n])%ims', $readmeContent, $readmeMatches)) {
            Render_Listing::deletePlugin($this->_pluginSlug, true);
            throw new Exception('Readme.txt is malformed');
        }

        // Prepare the readme keys
        $readmeKeys = array(
            'contributors'      => self::DATA_PLUGIN_CONTRIB,
            'plugin link'       => self::DATA_PLUGIN_LINK,
            'donate link'       => self::DATA_PLUGIN_DONATE,
            'tags'              => self::DATA_PLUGIN_TAGS,
            'tested up to'      => self::DATA_PLUGIN_TESTED,
            'requires at least' => self::DATA_PLUGIN_REQ,
            'requires php'      => self::DATA_PLUGIN_REQ_PHP,
            'stable tag'        => self::DATA_PLUGIN_BRANCH,
            'license'           => self::DATA_PLUGIN_LICENSE,
            'license uri'       => self::DATA_PLUGIN_LICENSE_URI,
        );
        
        // Store the plugin name
        $data[self::DATA_PLUGIN_NAME] = $this->_pluginSlug;
        if (preg_match('%^\s*plugin +name\s*?$%is', $readmeMatches[2])) {
            
            $readmeMatches[3] = trim(preg_replace_callback(
                '%^(\w.*?)[\r\n]([\w ]+\s*:)%is', 
                function($oldMatch) use(&$data, $readmeKeys) {
                    if (!preg_match('%^(?:' . implode('|', array_keys($readmeKeys)) . ') ?\:%i', $oldMatch[1])) {
                        $data[self::DATA_PLUGIN_NAME] = $oldMatch[1];
                        return $oldMatch[2];
                    }
                    return $oldMatch[0];
                }, 
                trim($readmeMatches[3])
            ));
            
            // Plugin name specified on next line
            $invalidKeys[] = array(
                'Plugin Name', 
                Seo::text(
                    Seo::TEXT_INFO_ABOUT_ERROR_PLUGIN_NAME,
                    '<code>=== ' . $data[self::DATA_PLUGIN_NAME] . ' ===</code>'
                )
            );
        } else {
            $data[self::DATA_PLUGIN_NAME] = $readmeMatches[2];
        }
        
        // Go through the labels
        $pluginDesc = trim(
            preg_replace_callback(
                '%^([\w ]+) *\: *(.*?)(?:[\r\n])%ims', 
                function($item) use(&$data, &$invalidKeys, $readmeKeys) {
                    $readmeKey = strtolower($item[1]);
                    if (isset($readmeKeys[$readmeKey])) {
                        $readmeValue = trim($item[2]);
                        
                        // Data clean-up
                        switch ($readmeKeys[$readmeKey]) {
                            // Lists
                            case self::DATA_PLUGIN_CONTRIB:
                            case self::DATA_PLUGIN_TAGS:
                                $readmeValue = array_unique(
                                    array_filter(
                                        array_map(
                                            function($item) {
                                                return trim(
                                                    preg_replace(
                                                        array('%[^\w\- ]+%', '% {2,}%'), 
                                                        array('', ' '), 
                                                        $item
                                                    )
                                                );
                                            }, 
                                            explode(',', strtolower($readmeValue))
                                        )
                                    )
                                );
                                
                                if (!count($readmeValue)) {
                                    $invalidKeys[] = array(
                                        $readmeKey, 
                                        Seo::text(Seo::TEXT_INFO_ABOUT_ERROR_TAGS)
                                    );
                                    $readmeValue = null;
                                } else {
                                    if (self::DATA_PLUGIN_TAGS === $readmeKeys[$readmeKey]) {
                                        if (count($readmeValue) > 10) {
                                            $invalidKeys[] = array(
                                                $readmeKey, 
                                                Seo::text(
                                                    Seo::TEXT_INFO_ABOUT_ERROR_TAGS_MANY,
                                                    count($readmeValue),
                                                    (count($readmeValue) ? 'tag' : 'tags') 
                                                )
                                            );
                                        } elseif ($readmeValue < 2) {
                                            $invalidKeys[] = array(
                                                $readmeKey, 
                                                Seo::text(
                                                    Seo::TEXT_INFO_ABOUT_ERROR_TAGS_FEW,
                                                    count($readmeValue),
                                                    (count($readmeValue) ? 'tag' : 'tags') 
                                                )
                                            );
                                        }
                                    }
                                }
                                break;
                            
                            // URLs
                            case self::DATA_PLUGIN_LINK:
                            case self::DATA_PLUGIN_DONATE:
                            case self::DATA_PLUGIN_LICENSE_URI:
                                if (!preg_match('%^https?\:\/\/%', $readmeValue)) {
                                    $readmeValue = null;
                                    $invalidKeys[] = array(
                                        $readmeKey, 
                                        Seo::text(
                                            Seo::TEXT_INFO_ABOUT_ERROR_URI,
                                            htmlentities(strip_tags($readmeValue))
                                        )
                                    );
                                }
                                break;
                            
                            // Versions
                            case self::DATA_PLUGIN_REQ:
                            case self::DATA_PLUGIN_REQ_PHP:
                            case self::DATA_PLUGIN_TESTED:
                                if (!preg_match('%^\d+(?:\.\d+)+$%', $readmeValue)) {
                                    $readmeValue = null;
                                    $invalidKeys[] = array(
                                        $readmeKey, 
                                        Seo::text(Seo::TEXT_INFO_ABOUT_ERROR_VERSION)
                                    );
                                }
                                break;
                            
                            default:
                                if (!strlen($readmeValue)) {
                                    $readmeValue = null;
                                    $invalidKeys[] = array(
                                        $readmeKey, 
                                        Seo::text(Seo::TEXT_INFO_ABOUT_ERROR_EMPTY)
                                    );
                                }
                                break;
                        }
                        
                        // Update the data store
                        if (null !== $readmeValue) {
                            $data[$readmeKeys[$readmeKey]] = $readmeValue;
                        }
                    }
                    return '';
                }, 
                trim($readmeMatches[3])
            )
        );

        // Validate description
        if (strlen($pluginDesc)) {
            $data[self::DATA_PLUGIN_DESC] = strip_tags($pluginDesc);
        } else {
            $invalidKeys[] = array(
                'Description', 
                Seo::text(Seo::TEXT_INFO_ABOUT_ERROR_DESC)
            );
        }
        
        // Get the plugin assets
        $data[self::DATA_PLUGIN_ASSETS] = Cache_Fetch::get($this->_pluginSlug)->assets();
        
        // Get the screenthost and icon
        $assetScreenshots = array();
        foreach ($data[self::DATA_PLUGIN_ASSETS] as $assetFile) {
            $assetName = preg_replace('%\.\w+$%i', '', $assetFile);
            if (0 === strpos($assetName, 'icon')) {
                if (!isset($data[self::DATA_PLUGIN_ICON]) || false !== strpos($assetName, '256x256')) {
                    $data[self::DATA_PLUGIN_ICON] = "https://ps.w.org/{$this->_pluginSlug}/assets/$assetFile";
                }
            } else {
                if (0 === strpos($assetName, 'screenshot')) {
                    $assetScreenshots[] = (int) preg_replace('%screenshot\-%i', '', $assetName);
                }
            }
        }
        
        // Prepare the readme screenshots
        $data[self::DATA_PLUGIN_SCREENSHOTS] = array();
        if (preg_match('%==\s*screenshots\s*==\s*(.*?)(?>==|\Z)%ims', $readmeContent, $screenshotText)) {
            if (preg_match_all('%^\s*(\d+)\s*\.\s*(.*?)\s*(?>\n|\Z)%ims', trim($screenshotText[1]), $matchScreenshots)) {
                $data[self::DATA_PLUGIN_SCREENSHOTS] = array_combine(
                    array_map('intval', $matchScreenshots[1]), 
                    $matchScreenshots[2]
                );
            }
        }

        // Extra assets on server
        if (count($extraAssets = array_diff($assetScreenshots, array_keys($data[self::DATA_PLUGIN_SCREENSHOTS])))) {
            $invalidKeys[] = array(
                'Screenshots', 
                (
                    1 === count($extraAssets)
                        ? Seo::text(Seo::TEXT_INFO_ABOUT_SCREEN, current($extraAssets))
                        : (
                            Seo::text(Seo::TEXT_INFO_ABOUT_SCREEN_LIST) . ' ' 
                                . implode(
                                    ', ', 
                                    array_map(function($item) {return "#$item";}, $extraAssets)
                                )
                        )
                ) 
                . ' in <a href="https://ps.w.org/' . $this->_pluginSlug . '/assets/"'
                    . ' target="_blank" rel="noreferrer nofollow">' 
                        . $this->_pluginSlug . '/assets'
                    . '</a> to your readme.txt'
            );
        }
        
        // Extra descriptions
        if (count($extraDesc = array_diff(array_keys($data[self::DATA_PLUGIN_SCREENSHOTS]), $assetScreenshots))) {
            $extraDescTexts = array_map(
                function($item) use($data) {
                    return '#' . $item . ' (' . $data[self::DATA_PLUGIN_SCREENSHOTS][$item] . ')';
                }, 
                $extraDesc
            );

            $invalidKeys[] = array(
                'Screenshots', 
                1 === count($extraDesc)
                    ? Seo::text(Seo::TEXT_INFO_ABOUT_SCREEN_IMAGE, current($extraDescTexts))
                    : (Seo::text(Seo::TEXT_INFO_ABOUT_SCREEN_IMAGE_LIST) . ': ' . implode(', ', $extraDescTexts))
            );
        }
        
        // Prepare shuffled tags
        $shuffledTags = $data[self::DATA_PLUGIN_TAGS] ?? [];
        shuffle($shuffledTags);
        
        // Authors not defined
        if (!isset($data[self::DATA_PLUGIN_CONTRIB]) || !count($data[self::DATA_PLUGIN_CONTRIB])) {
            $invalidKeys[] = array(
                'Contributors', 
                Seo::text(Seo::TEXT_INFO_ABOUT_ERROR_CONTRIBS)
            );
        }
        
        // Score this file
        shuffle($invalidKeys);
        $this->getScore()->grade(
            'readme.txt', 
            Seo::text(Seo::TEXT_INFO_ABOUT_README_FIX_DESC),
            count($invalidKeys),
            16,
            count($shuffledTags) 
                ? (
                    count($shuffledTags) . ' plugin ' 
                        . (1 === $shuffledTags ? 'tag' : 'tags') 
                        . ': ' . implode(
                            ', ', 
                            array_slice($shuffledTags, 0, 5)
                        ) 
                        . (count($shuffledTags) > 5 ? '...' : '')
                )
                : Seo::text(Seo::TEXT_INFO_ABOUT_TAGS_NONE),
            Seo::text(Seo::TEXT_INFO_ABOUT_README_FIX) . ': <ul><li>' 
                . implode(
                    '</li><li>', 
                    array_map(
                        function($item) {
                            return '<b>' . ucfirst($item[0]) . '</b>: ' . $item[1];
                        }, 
                        $invalidKeys
                    )
                )
                . '</li></ul>'
                . Seo::text(
                    Seo::TEXT_INFO_ABOUT_README_FIX_FINAL, 
                    '<a rel="noreferrer nofollow" target="_blank" href="https://wordpress.org/plugins/readme.txt">readme.txt</a>'
                )
        );

        // Keys extracted from the main plugin file
        $pluginFileKeys = array(
            'Plugin Name'       => self::DATA_PLUGIN_NAME_MAIN,
            'Description'       => self::DATA_PLUGIN_DESC_MAIN,
            'Version'           => self::DATA_PLUGIN_VERSION,
            'Text Domain'       => self::DATA_PLUGIN_TEXT_DOMAIN,
            'Requires at least' => self::DATA_PLUGIN_REQ,
            'Requires PHP'      => self::DATA_PLUGIN_REQ_PHP,
            'Domain Path'       => self::DATA_PLUGIN_PATH_DOMAIN,
        );
                        
        // Prepare the invalid keys
        $invalidKeys = [];
        
        // Found a .git repository
        if (self::$gitFolderFound) {
            $invalidKeys[] = array(
                'Git Repository', 
                Seo::text(Seo::TEXT_INFO_ABOUT_ERROR_GIT)
            );
        }
        
        // Go through the root PHP files
        foreach (glob(WP_PLUGINS . '/' . $this->_pluginSlug . '/*.php') as $phpPath) {
            $phpContents = file_get_contents($phpPath);
            
            // Found the required plugin name
            if (preg_match('%\bplugin name\s*\:%i', $phpContents)) {
                $data[self::DATA_PLUGIN_FILE] = basename($phpPath);
                
                // Invalid main file name
                if (basename($phpPath, '.php') !== $this->_pluginSlug) {
                    $invalidKeys[] = array(
                        'Main file name', 
                        Seo::text(
                            Seo::TEXT_INFO_ABOUT_MAIN_NAME, 
                            '"' . $this->_pluginSlug . '.php"', 
                            '"' . $data[self::DATA_PLUGIN_FILE] . '"'
                        )
                    );
                }
                
                // Go through the keys
                foreach ($pluginFileKeys as $pfKey => $pfValue) {
                    if (preg_match('%\b' . preg_quote($pfKey) . '\s*\:(.*?)[\r\n]%im', $phpContents, $matches)) {
                        $mainValue = trim($matches[1]);
                        
                        switch ($pfValue) {
                            case self::DATA_PLUGIN_NAME_MAIN:
                                if (strlen($mainValue) > 70) {
                                    $invalidKeys[] = array(
                                        $pfKey, 
                                        Seo::text(
                                            Seo::TEXT_INFO_ABOUT_MAIN_NAME_LENGTH_LONG,
                                            strlen($mainValue) 
                                        )
                                    );
                                } else {
                                    if ($this->_pluginSlug == $data[self::DATA_PLUGIN_NAME]) {
                                        $data[self::DATA_PLUGIN_NAME] = $mainValue;
                                    }
                                }
                                break;
                                
                            case self::DATA_PLUGIN_DESC_MAIN:
                                if (strlen($mainValue) > 140) {
                                    $invalidKeys[] = array(
                                        $pfKey, 
                                        Seo::text(
                                            Seo::TEXT_INFO_ABOUT_MAIN_DESC_LENGTH_LONG,
                                            strlen($mainValue) 
                                        )
                                    );
                                }
                                if (strlen($mainValue) < 10) {
                                    $invalidKeys[] = array(
                                        $pfKey, 
                                        Seo::text(
                                            Seo::TEXT_INFO_ABOUT_MAIN_DESC_LENGTH_SHORT,
                                            strlen($mainValue) 
                                        )
                                    );
                                }
                                break;
                                
                            case self::DATA_PLUGIN_VERSION:
                                if (!preg_match('%^\d+(?:\.\d+)+$%', $mainValue)) {
                                    $mainValue = htmlentities(strip_tags(substr($mainValue, 0, 20))) . '...';
                                    $invalidKeys[] = array(
                                        $pfKey, 
                                        Seo::text(
                                            Seo::TEXT_INFO_ABOUT_MAIN_VERSION,
                                            '"' . $mainValue . '"'
                                        )
                                    );
                                }
                                break;
                                
                            case self::DATA_PLUGIN_REQ:
                            case self::DATA_PLUGIN_REQ_PHP:
                                if (!preg_match('%^\d+(?:\.\d+)+$%', $mainValue)) {
                                    $invalidKeys[] = array(
                                        $pfKey, 
                                        Seo::text(
                                            Seo::TEXT_INFO_ABOUT_MAIN_REQ_VERSION,
                                            '"' . htmlentities(strip_tags($mainValue)) . '"'
                                        )
                                    );
                                } else {
                                    if (isset($data[$pfValue]) && $mainValue !== $data[$pfValue]) {
                                        $invalidKeys[] = array(
                                            $pfKey, 
                                            Seo::text(
                                                Seo::TEXT_INFO_ABOUT_MAIN_REQ_VERSION_DIFF,
                                                '"' . $data[$pfValue] . '"',
                                                '"' . $mainValue . '"'
                                            )
                                        );
                                    }
                                }
                                break;
                                
                            case self::DATA_PLUGIN_TEXT_DOMAIN:
                                if (!preg_match('%^[a-z0-9\-]+$%', $mainValue)) {
                                    $invalidKeys[] = array(
                                        $pfKey, 
                                        Seo::text(Seo::TEXT_INFO_ABOUT_MAIN_TD)
                                    );
                                } else {
                                    if ($mainValue !== $this->_pluginSlug) {
                                        $invalidKeys[] = array(
                                            $pfKey, 
                                            Seo::text(Seo::TEXT_INFO_ABOUT_MAIN_TD_DIFF)
                                        );
                                    }
                                }
                                break;
                                
                            case self::DATA_PLUGIN_PATH_DOMAIN:
                                if (!preg_match('%\/\w+%i', $mainValue)) {
                                    $invalidKeys[] = array(
                                        $pfKey,
                                        Seo::text(Seo::TEXT_INFO_ABOUT_MAIN_DP_SLASH, $mainValue)
                                    );
                                }
                                if (!preg_match('%^\/[a-z0-9\-\/]+\/?$%', $mainValue)) {
                                    $invalidKeys[] = array(
                                        $pfKey,
                                        Seo::text(Seo::TEXT_INFO_ABOUT_MAIN_DP_FORMAT, '"' . $mainValue . '"')
                                    );
                                } else {
                                    if (!is_dir(WP_PLUGINS . '/' . $this->_pluginSlug . $mainValue)) {
                                        $invalidKeys[] = array(
                                            $pfKey,
                                            Seo::text(Seo::TEXT_INFO_ABOUT_MAIN_DP_MISSING, '"' . $mainValue . '"')
                                        );
                                    }
                                }
                                break;
                        }
                        $data[$pfValue] = strip_tags($mainValue);
                    }
                }
                break;
            }
        }
        
        // No main file found
        if (!isset($data[self::DATA_PLUGIN_FILE])) {
            Render_Listing::deletePlugin($this->_pluginSlug, true);
            throw new Exception('Main file is missing');
        }
        
        // Plugin version fallback
        if (!isset($data[self::DATA_PLUGIN_VERSION])) {
            $data[self::DATA_PLUGIN_VERSION] = '0.1';
            $invalidKeys[] = array(
                'Version', 
                Seo::text(Seo::TEXT_INFO_ABOUT_MAIN_VERSION_MISSING)
            );
        }
        
        // Escape the plugin name
        $data[self::DATA_PLUGIN_NAME] = htmlentities(
            html_entity_decode(
                strip_tags(
                    $data[self::DATA_PLUGIN_NAME]
                )
            )
        );

        // Defaults
        $data[self::DATA_PLUGIN_DESC_MAIN] = strip_tags(
            $data[self::DATA_PLUGIN_DESC_MAIN] ?? $data[self::DATA_PLUGIN_DESC] ?? ''
        );
        $data[self::DATA_PLUGIN_TEXT_DOMAIN] = $data[self::DATA_PLUGIN_TEXT_DOMAIN] ?? $this->_pluginSlug;
        $data[self::DATA_PLUGIN_ICON] = $data[self::DATA_PLUGIN_ICON] ?? 'https://upload.wikimedia.org/wikipedia/commons/9/98/WordPress_blue_logo.svg';
        
        // Invalid description
        if (!isset($data[self::DATA_PLUGIN_DESC_MAIN])) {
            $invalidKeys[] = array(
                'Description', 
                Seo::text(Seo::TEXT_INFO_ABOUT_MAIN_DESC_MISSING)
            );
        }
        
        // Score this file
        shuffle($invalidKeys);
        $this->getScore()->grade(
            $this->_pluginSlug . '/' . $data[self::DATA_PLUGIN_FILE], 
            Seo::text(
                Seo::TEXT_INFO_ABOUT_MAIN_FIX_DESC,
                '"' . ($data[self::DATA_PLUGIN_NAME]) . '"',
                $data[self::DATA_PLUGIN_VERSION]
            ),
            count($invalidKeys),
            13,
            strlen($data[self::DATA_PLUGIN_DESC_MAIN]) . ' characters long description: '
                . '<blockquote cite="https://wordpress.org/plugins/' . $this->_pluginSlug . '">' 
                    . htmlentities($data[self::DATA_PLUGIN_DESC_MAIN]) 
                . '</blockquote>',
            Seo::text(Seo::TEXT_INFO_ABOUT_MAIN_FIX) . ': <ul><li>' 
                . implode(
                    '</li><li>', 
                    array_map(
                        function($item) {
                            return '<b>' . $item[0] . '</b>: ' . $item[1];
                        }, 
                        $invalidKeys
                    )
                )
                . '</li></ul>'
        );

        // Get the plugin statistics
        $data[self::DATA_PLUGIN_STATS] = Cache_Fetch::get($this->_pluginSlug)->information();
        $data[self::DATA_PLUGIN_COMMITS] = Cache_Fetch::get($this->_pluginSlug)->commits();

        // Update the cache
        Cache_Data::get($this->_pluginSlug)
            ->setTags($data[self::DATA_PLUGIN_TAGS] ?? [])
            ->setAuthors($data[self::DATA_PLUGIN_CONTRIB] ?? [])
            ->setInfo($data);
        
        return $data;
    }
}

/*EOF*/