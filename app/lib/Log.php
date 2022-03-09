<?php

/**
 * Potrivit - Log
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Log {

    /**
     * Debug modes
     */
    const LEVEL_DEBUG   = 'debug';
    const LEVEL_INFO    = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR   = 'error';

    /**
     * Set the current logging level
     */
    protected static $_currentLevel = self::LEVEL_INFO;
    
    /**
     * Cached checks; store log level check results
     * 
     * @var array
     */
    protected static $_cachedChecks = array();
    
    /**
     * Flag for log rotation check
     * 
     * @var boolean
     */
    protected static $_checkedLogRotate = false;
    
    /**
     * Flag for whether the file permissions were checked
     * 
     * @var boolean
     */
    protected static $_checkedFilePerms = false;

    /**
     * Logging priorities
     * 
     * @var array
     */
    public static $priorities = array(
        self::LEVEL_DEBUG,
        self::LEVEL_INFO,
        self::LEVEL_WARNING,
        self::LEVEL_ERROR,
    );

    /**
     * Extra information to log
     * 
     * @var array
     */
    protected static $_extra = array();

    /**
     * Get the current log level
     * 
     * @return string Log level, one of:<ul>
     * <li>Log::LEVEL_DEBUG</li>
     * <li>Log::LEVEL_INFO</li>
     * <li>Log::LEVEL_WARNING</li>
     * <li>Log::LEVEL_ERROR</li>
     * </u>
     */
    public static function getLevel() {
        return self::$_currentLevel;
    }
    
    /**
     * Set the log level
     * 
     * @param string $level Log level
     */
    public static function setLevel($level) {
        $level = strtolower($level);
        if (in_array($level, self::$priorities)) {
            self::$_currentLevel = $level;
        }
    }
    
    /**
     * Get the extra arguments to log
     * 
     * @return array
     */
    public static function getExtra() {
        return self::$_extra;
    }
    
    /**
     * Set extra values for the log
     * 
     * @param array $values Extra values
     */
    public static function setExtra(Array $values) {
        self::$_extra = $values;
    }

    /**
     * Check if the provided error level will be allowed to write to logs
     * 
     * @param string $errorLevel
     * @return boolean
     */
    public static function check($errorLevel) {
        // Cache check
        if (!isset(self::$_cachedChecks[$errorLevel])) {
            // Get the error level priority
            $priority = array_search(strtolower($errorLevel), self::$priorities);

            // Get the current priority
            $currentPriority = array_search(self::$_currentLevel, self::$priorities);

            // All done
            self::$_cachedChecks[$errorLevel] = (is_int($priority) && $priority >= $currentPriority);
        }
        
        // All done
        return self::$_cachedChecks[$errorLevel];
    }
    
    /**
     * Log a message
     * 
     * @param string  $message       Message
     * @param string  $errorLevel    Error level
     * @param string  $file          File
     * @param int     $line          Line
     * @param boolean $logErrorLevel (optional) Log the error level; default <b>true</b>
     * @param boolean $forced        (optional) Forced logging; default <b>false</b>
     */
    protected static function _log($message, $errorLevel, $file, $line, $logErrorLevel = true, $forced = false) {
        // Cannot log
        if (!$forced && !self::check($errorLevel)) {
            return;
        }

        // User did not provide a file and a line
        if (empty($file) || empty($line)) {
            // Get the debug backtrace information
            $debugBacktrace = debug_backtrace();

            // Get the file
            $file = $debugBacktrace[1]['file'];

            // Get the line
            $line = $debugBacktrace[1]['line'];
        }

        // Split the message into multiple lines
        if (!is_string($message)) {
            $message = var_export($message, true);
        }

        // Prepare the result
        $contents = '';
        
        // Clean-up \r\n
        $message = preg_replace('%\r\n%', PHP_EOL, $message);
        $message = preg_replace('%\r%', '', $message);
        
        // Get the session data
        $sessionData = array();
        
        // Prepare the message array
        $messageArrayCommon = array(
            str_pad(getmypid(), 6),
            date('d.m H:i:s'),
            str_pad(basename($file, '.php'), 16),
            str_pad($line, 5),
        );
        
        // Break the lines
        foreach (preg_split("%\n%", $message) as $messageLine) {
            // Start with the common details
            $messageArray = $messageArrayCommon;
            
            // Store the error level
            if ($logErrorLevel) {
                $messageArray[] = str_pad(strtoupper($errorLevel), 7, ' ', STR_PAD_RIGHT);
            }
            
            // The last element is always the message
            $messageArray[] = $messageLine;
            
            // Prepare the information
            $logInfo = array_merge($sessionData, self::$_extra, $messageArray);

            // Prepare the contents
            $contents .= ' ' . implode(' | ', $logInfo) . PHP_EOL;
        }

        // Append to the log file
        $logPath = Temp::getPath(Temp::FOLDER_LOGS) . '/main.log';
        if (!is_file($logPath)) {
            touch($logPath);
            passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' ' . $logPath);
        } else {
            // More than 5MB, reset the log
            if (filesize($logPath) > 5242880) {
                file_put_contents($logPath, '');
            }
        }
        @file_put_contents($logPath, $contents, FILE_APPEND);
    }

    /**
     * Log at a debug level
     * 
     * @param mixed   $message Message
     * @param string  $file    Current file
     * @param int     $line    Current line
     * @param boolean $forced  (optional) Forced logging; default <b>false</b>
     */
    public static function debug($message, $file = '', $line = '', $forced = false) {
        self::_log($message, self::LEVEL_DEBUG, $file, $line, true, $forced);
    }
    
    /**
     * Log at a info level
     * 
     * @param mixed   $message Message
     * @param string  $file    Current file
     * @param int     $line    Current line
     * @param boolean $forced  (optional) Forced logging; default <b>false</b>
     */
    public static function info($message, $file = '', $line = '', $forced = false) {
        self::_log($message, self::LEVEL_INFO, $file, $line, true, $forced);
    }

    /**
     * Log at a warning level
     * 
     * @param mixed   $message Message
     * @param string  $file    Current file
     * @param int     $line    Current line
     * @param boolean $forced  (optional) Forced logging; default <b>false</b>
     */
    public static function warning($message, $file = '', $line = '', $forced = false) {
        self::_log($message, self::LEVEL_WARNING, $file, $line, true, $forced);
    }

    /**
     * Log at a error level
     * 
     * @param mixed   $message Message
     * @param string  $file    Current file
     * @param int     $line    Current line
     * @param boolean $forced  (optional) Forced logging; default <b>false</b>
     */
    public static function error($message, $file = '', $line = '', $forced = false) {
        self::_log($message, self::LEVEL_ERROR, $file, $line, true, $forced);
    }

}

/*EOF*/