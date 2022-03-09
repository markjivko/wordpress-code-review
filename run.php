<?php
/**
 * Potrivit - CLI Entry Point
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
define('ROOT', __DIR__ . '/app');
define('WP_ROOT', '/var/www/wordpress');
define('WP_PLUGINS', '/var/www/wordpress/wp-content/plugins');

// Prepare the autoloader
require ROOT . '/lib/Autoloader.php';

// Initialize autoloader and set limits
Autoloader::getInstance();

// Run a Command Line Interface task
Cli::run();

/* EOF */