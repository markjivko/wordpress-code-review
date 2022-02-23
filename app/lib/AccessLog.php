<?php
/**
 * Potrivit - Access Logs
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class AccessLog {
    
    const ACCESS_LOG_PATH = '/var/www/access.log';
    
    /**
     * Request types
     */
    const REQUEST_TYPE_GET  = 'GET';
    const REQUEST_TYPE_POST = 'POST';
    
    /**
     * Reset the access log file
     */
    public static function clear() {
        file_put_contents(self::ACCESS_LOG_PATH, '');
    }
    
    /**
     * Get the list of access logs after the indicated page
     * 
     * @param string $page        (optional) Filter out logs before this entry; default <b>null</b>
     * @param string $requestType (optional) Request type, one of "GET" or "POST"; default <b>GET</b>
     * @return array[] Array of [<ul>
     * <li>(string) Request type (typically "POST" or "GET")</li>
     * <li>(string) Request URL</li>
     * <li>(float) CPU Time in seconds</li>
     * <li>(float) Memory in bytes</li>
     * <li>(null|array) Server-side error, associative array [<ul>
     *         <li>"type"    => (int) PHP Error Code</li>
     *         <li>"message" => (string) Error message</li>
     *         <li>"file"    => (string) Error file</li>
     *         <li>"line"    => (int) Error line</li>
     *     </ul>]
     * </li>
     * </ul>],...
     */
    public static function getAll($page = null, $requestType = self::REQUEST_TYPE_GET) {
        $accessLogArray = file(self::ACCESS_LOG_PATH);

        // Clean-up previous POST data
        foreach ($accessLogArray as $alaKey => $alaValue) {
            $alaJson = @json_decode($alaValue, true);

            // Stop at our request
            if (is_array($alaJson) 
                && $requestType === $alaJson[0] 
                && (null === $page || !strlen($page) || 0 === strpos($alaJson[1], $page))
            ) {
                break;
            }
            unset($accessLogArray[$alaKey]);
        }
        
        // Reindex the array
        return array_map(
            function($line) {
                $result = json_decode($line, true);
                
                // Remove references to our api and /var/www/wordpress
                if (is_array($result) && isset($result[4]) && is_array($result[4])) {
                    $result[4]['message'] = preg_replace(
                        [
                            '%(?:\#\d+\s+)?' . preg_quote('/var/www/wordpress/wp-admin/wp-api.php') . '.*%ims', 
                            '%' . preg_quote('/var/www/wordpress/') . '%i', 
                            '%[\r\n]%'
                        ], 
                        [
                            '', 
                            '', 
                            '<br/>'
                        ], 
                        htmlentities(strip_tags($result[4]['message']))
                    );
                }
                
                return $result;
            }, 
            array_values(
                $accessLogArray
            )
        );
    }
}

/*EOF*/