<?php
/**
 * Potrivit - Console
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Console {

    /**
     * Output a string to the console (log level debug)
     * 
     * @param string  $string     Message
     * @param boolean $success    (optional) Status; default <b>true</b>
     * @param int     $paddingTop (optional) Number of new lines before message; default <b>0</b>
     */
    public static function log($string, $success = true, $paddingTop = 0) {
        $success && Log::debug($string);
        echo str_repeat(PHP_EOL, $paddingTop) . ($success ? '   ' : ' ! ') . $string . PHP_EOL;
    }

    /**
     * Output a string to the console (log level info)
     * 
     * @param string  $string     Message
     * @param boolean $success    (optional) Status; default <b>true</b>
     * @param int     $paddingTop (optional) Number of new lines before message; default <b>0</b>
     */
    public static function info($string, $success = true, $paddingTop = 0) {
        $success && Log::info($string);
        echo str_repeat(PHP_EOL, $paddingTop) . ($success ? ' > ' : ' ! ') . $string . PHP_EOL;
    }

    /**
     * Output a decorative header
     * 
     * @param string $string        Message
     * @param string $prefix        (optional) Header prefix string; default <b>#</b>
     * @param string $underlineChar (optional) Underline character; default <b>-</b>
     */
    public static function header($string, $prefix = '#', $underlineChar = '-') {
        Log::info($prefix . ' ' . $string);
        echo PHP_EOL . " $prefix $string" . PHP_EOL . ' ' . str_repeat($underlineChar, strlen($string) + strlen($prefix) + 1) . PHP_EOL;
    }
}

/* EOF */