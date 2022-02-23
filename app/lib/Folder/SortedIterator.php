<?php
/**
 * Potrivit - Sorted Iterator for Folders
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Folder_SortedIterator extends SplHeap {
    
    /**
     * Sorted iterator
     * 
     * @param Iterator $iterator
     */
    public function __construct(Iterator $iterator) {
        foreach ($iterator as $item) {
            $this->insert($item);
        }
    }
    
    /**
     * Compare two paths
     * 
     * @param SplFileInfo $fileB File B info
     * @param SplFileInfo $fileA File A info
     * @return int
     */
    public function compare($fileB, $fileA) {
        return strcmp($fileA->getFilename(), $fileB->getFilename());
    }
}

/*EOF*/