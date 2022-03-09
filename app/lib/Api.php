<?php
/**
 * Potrivit - API
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Api {

    /**
     * Run WprdPress API tools with CURL calls to /wp-admin/wp-api.php
     * 
     * @param string $methodName      Method name
     * @param array  $methodArguments (optional) Method arguments
     */
    public static function run($methodName, $methodArguments = array()) {       
        // Prepare the post load
        $postData = array(
            'method'    => $methodName,
            'arguments' => $methodArguments
        );
        
        // Get the API path
        $apiPath = 'wp-admin/wp-api.php';
        
        // Copy the files
        file_put_contents(
            '/var/www/wordpress/' . $apiPath,
            str_replace(
                '{ROOT}',
                ROOT,
                file_get_contents(ROOT . '/res/wp-api.php')
            )
        );

        // Set the correct ownership
        passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' /var/www/wordpress/wp-admin/wp-api.php');
        
        // Prepare the URL
        $postDomain = Config::get()->domainWp();
        
        // User agent
        $userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:90.0) Gecko/20100101 Firefox/90.0';

        // Prepare the headers
        $curlHeaders = array (
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent: ' . $userAgent,
            'DNT: 1',
            'Referer: http://' . $postDomain . '/',
            'Host: ' . $postDomain,
            'Referrer: ' . $postDomain,
            'Cache-Control: no-cache',
        );

        // Prepare the options
        $options = array(
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => http_build_query($postData),
            CURLOPT_USERAGENT       => $userAgent,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false,
            CURLOPT_HTTPHEADER      => $curlHeaders,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_ALL,
            CURLOPT_AUTOREFERER     => true,
            CURLOPT_CONNECTTIMEOUT  => 5,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_URL             => $postDomain . '/' . $apiPath,
        );

        // Prepare the retries counter
        $curlLoggedOutRetries = 0;
        
        do {
            // Initialize the CURL
            $ch = curl_init();

            // Set the options
            curl_setopt_array($ch, $options);

            // Get the result
            $curlResult = curl_exec($ch);

            // Remove the clutter
            $jsonString = preg_replace('%.*?({"status":.*$)%ims', '${1}', $curlResult);

            // Extra content
            $extraContent = preg_replace('%(.*?){"status":.*$%ims', '${1}', $curlResult);

            // Execute the request
            $jsonArray = @json_decode($jsonString, true);

            // Invalid result
            if (!is_array($jsonArray)) {
                // Logged out
                if (preg_match('%<\!doctype%i', $jsonString)) {
                    // Increment the counter
                    $curlLoggedOutRetries++;
                    
                    // Inform the user
                    Console::log('Invalid result (Logged Out): Attempt ' . $curlLoggedOutRetries . '/3', false);
                    Log::debug($jsonString);

                    // Give up
                    if ($curlLoggedOutRetries >= 3) {
                        Console::log('Gave up on request');
                        return;
                    }
                    
                    // Start over
                    continue;
                } 
                
                // Other type of error, stop here
                if (false === $curlResult) {
                    Console::log('Invalid result (cURL): ' . curl_error($ch), false);
                } else {
                    Console::log('Invalid result: ' . var_export($jsonString, true), false);
                }
                return;
            }
            
            // Close
            curl_close($ch);
            
            // Valid result, no need to retry
            break;
        } while (true);
        
        // Add the extra content
        $jsonArray['content'] .= trim(html_entity_decode(strip_tags(preg_replace('%<br\s*\/?\s*>%i', PHP_EOL, $extraContent))));

        if (!isset($jsonArray['status']) || !isset($jsonArray['content'])) {
            Console::log('Result has invalid format', false);
            return;
        }
        
        // Content
        if (strlen($jsonArray['content'])) {
            Console::log($jsonArray['content']);
        }
        
        // Prepare the result to log
        $loggedResult = is_string($jsonArray['result']) ? $jsonArray['result'] : json_encode($jsonArray['result']);
        if (strlen($loggedResult) > 128) {
            $loggedResult = substr($loggedResult, 0, 128 - 3) . '...';
        }

        // Log the result
        Log::debug($loggedResult);
        
        // Exception thrown
        if (!$jsonArray['status']) {
            throw new Exception($jsonArray['result']);
        }

        // All done
        return array((boolean)$jsonArray['status'], $jsonArray['result']);
    }
}

/* EOF */