<?php
/**
 * Potrivit - Folder
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Folder {
    
    /**
     * Get an iterator
     * 
     * @param string $folderPath Folder path
     * @return \SplHeap
     */
    public static function getIterator($folderPath) {
        return new Folder_SortedIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $folderPath, 
                    RecursiveDirectoryIterator::SKIP_DOTS
                ), 
                RecursiveIteratorIterator::CHILD_FIRST
            )
        );
    }
    
    /**
     * Mark all empty folders in /var/www/wordpress with .empty files so git will be able to list and reset them
     */
    public static function markEmptyFolders() {
        // Make all folders accessible
        shell_exec('find /var/www/wordpress -type d -exec chmod 755 {} +');
        
        // Add empty files to empty folders to mark them for Git reset
        foreach (self::getIterator('/var/www/wordpress') as /*@var $item SplFileInfo*/  $item) {
            // Ignore our git repo folder
            if (0 === strpos($item->getPath(), '/var/www/wordpress/.git')) {
                continue;
            }
            
            // Scandir can see hidden unix files, unlike glob
            if ($item->isDir() && 2 == count(scandir($item->getPathname()))) {
                touch($item->getPathname() . '/.empty');
            }
        }
    }
}
/*EOF*/