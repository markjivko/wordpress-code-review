<?php
/**
 * Potrivit - Arrays
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Arrays {

    /**
     * Shuffle an associative array
     * 
     * @param array &$array Array
     */
    public static function shuffle(&$array) {
        if (is_array($array)) {
            $shuffledArray = [];
            
            // Get the keys
            $arrayKeys = array_keys($array);
            shuffle($arrayKeys);
            
            // Reform the array
            foreach ($arrayKeys as $key) {
                $shuffledArray[$key] = $array[$key];
            }
            
            // Replace the original array
            $array = $shuffledArray;
        }
    }
    
}

/*EOF*/