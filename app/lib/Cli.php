<?php
/**
 * Potrivit - CLI Entry Point
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Cli {
    
    /**
     * Default runner method
     */
    const DEFAULT_METHOD = 'run';

    /**
     * Start the specified command-line task
     */
    public static function run() {
        global $argv;
        $arguments = array_slice($argv, 1);

        do {
            if (null !== $classId = array_shift($arguments)) {
                if (class_exists($className = 'Run_' . ucfirst(preg_replace('%\W+%i', '', strtolower($classId))))) {
                    $methodName = array_shift($arguments) ?? self::DEFAULT_METHOD;
                    $methodName = preg_replace('%\W+%', '', $methodName);
                    
                    if (method_exists($className, $methodName)) {
                        try {
                            call_user_func_array(array($className, $methodName), $arguments);
                        } catch (Exception $exc) {
                            Console::log($exc->getMessage(), false);
                            exit(1);
                        }
                        break;
                    }
                }
            }

            Console::log('No action specified', false);
        } while(false);
    }
}

/* EOF */