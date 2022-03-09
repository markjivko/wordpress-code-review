<?php
/**
 * Potrivit - Widget
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
abstract class Widget {
    
    /**
     * Get data
     * 
     * @return array
     */
    abstract protected function _getData();
    
    /**
     * Render widget HTML
     * 
     * @return string
     */
    public function renderWidget() {
        // Prepare the result
        $result = '';
        
        // Prepare the widget name
        $widgetName = lcfirst(
            implode(
                '', 
                array_map(
                    'ucfirst', 
                    explode(
                        '_', 
                        strtolower(
                            get_called_class()
                        )
                    )
                )
            )
        );
        
        // Get the widget path
        if (is_file($widgetPath = ROOT . '/res/layout/widgets/' . $widgetName . '.phtml')) {
            ob_start();
            
            $data = $this->_getData();
            require $widgetPath;
            
            $result = ob_get_clean();
        }
        
        return (Config::get()->production()
            ? preg_replace(
                array(
                    '%(?:^\s*\/\/.*?\n|\/\*.*?\*\/|[\r\n]+\s*)%ims',
                    '%\s{2,}%',
                ), 
                array(
                    '', 
                    ' ',
                ), 
                $result
            )
            : $result
        );
    }
}

/*EOF*/