<?php

/**
 * Potrivit - Install CLI methods
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Run_Install {

    /**
     * Run the installer tasks, passing along the arguments
     */
    public static function run() {
        if (0 !== posix_getuid()) {
            throw new Exception('You must run the installer as root');
        }

        // Prepare the tasksv
        $methods = array_map(
            function($item) {
                return $item->name;
            },
            array_filter((new ReflectionClass(__CLASS__))->getMethods(), function($item) {
                return 'run' !== $item->name;
            }) 
        );

        // Run the tasks
        foreach($methods as $methodName) {
            try {
                call_user_func_array(array(__CLASS__, $methodName), func_get_args());
            } catch (Exception $exc) {
                Console::log($exc->getMessage(), false);
            }
        }
    }

    /**
     * Prepare `wp` alias
     */
    public static function alias() {
        Console::header('Alias');
        if (0 !== posix_getuid()) {
            throw new Exception('Alias installer must run as root');
        }
        $bashAliasesFile = '/home/' . Config::get()->user() . '/.bash_aliases';
        
        // Prepare the entry point path
        $rootPath = dirname(ROOT) . '/run.php';

        // Get the app version
        $version = Config::get()->version();

        // Prepare the alias command
        $bashCommand = <<<"COM"
# Potrivit CLI v.$version
alias wp='php -f "$rootPath"'
COM;

        do {
            if (is_file($bashAliasesFile)) {
                $bashAliasesContent = file_get_contents($bashAliasesFile);
                if (preg_match('%\balias\s+wp\b%i', $bashAliasesContent)) {
                    $bashAliasesContent = preg_replace(
                        '%#\s*Potrivit.*?\n\s*alias\s+wp\b.*?(?:\n|$)%ims',
                        $bashCommand,
                        $bashAliasesContent . PHP_EOL
                    );

                    // Update the file
                    file_put_contents($bashAliasesFile, $bashAliasesContent);
                    Console::log('Updated `wp` alias');
                    break;
                }
            }

            // Append the command to aliases
            if (false === $fh = @fopen($bashAliasesFile, 'a')) {
                throw new Exception('Could not open "' . $bashAliasesFile . '"');
            }
            fwrite($fh, PHP_EOL . $bashCommand);
            fclose($fh);
            passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' "' . $bashAliasesFile . '"');

            Console::log('Created `wp` alias');
        } while(false); 

        // Final info
        Console::log('Remember to reload the current shell or `exec bash`');
    }

    /**
     * Set the correct user and group to apache config.<br/>
     * Set the correct owner to /var/www and /var/lib/phpmyadmin/*.<br/>
     * Load PHPMyAdmin configuration in apache.
     */
    public static function apache() {
        Console::header('Apache & PHPMyAdmin');
        if (0 !== posix_getuid()) {
            throw new Exception('Apache installer must run as root');
        }

        // Validate dependencies
        if (!is_dir('/var/www')) {
            throw new Exception('Apache is not installed');
        }
        if (!is_dir('/var/lib/phpmyadmin')) {
            throw new Exception('PHPMyAdmin is not installed');
        }
        if (!is_dir('/etc/mysql')) {
            throw new Exception('MySQL is not installed');
        }

        // Folder ownership
        Console::log('Updating owners...');
        passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' -R /var/www');
        passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' -R /var/log/apache2');
        passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' -R /var/lib/phpmyadmin');
        passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' -R /etc/phpmyadmin');
        passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' -R /usr/share/phpmyadmin');

        // Hosts
        $hostsContent = file_get_contents('/etc/hosts');
        if (!preg_match('%127\.0\.0\.1\s+' . preg_quote(Config::get()->domainWp()) . '%i', $hostsContent)) {
            Console::log('Setting Faux Domain...');
            file_put_contents(
                '/etc/hosts',
                $hostsContent 
                    . PHP_EOL . PHP_EOL . '# Potrivit Faux Domain' 
                    . PHP_EOL . '127.0.0.1 ' . Config::get()->domainWp() 
                    . PHP_EOL . '127.0.0.1 ' . Config::get()->domainTest() 
            );
        }

        // Configure apache user and group
        Console::log('Updating Apache user and group...');
        $apacheConfig = preg_replace(
            array(
                '%export\s+APACHE_RUN_USER\s*=\s*[\w\-]+%i',
                '%export\s+APACHE_RUN_GROUP\s*=\s*[\w\-]+%i'
            ),
            array(
                'export APACHE_RUN_USER=' . Config::get()->user(),
                'export APACHE_RUN_GROUP=' . Config::get()->group()
            ),
            file_get_contents($apachePath = '/etc/apache2/envvars')
        );
        file_put_contents($apachePath, $apacheConfig);
        
        // Configure Apache wordpress host
        $apacheConfig = file_get_contents($apachePath = '/etc/apache2/sites-enabled/000-default.conf');
        if (!preg_match('%\/var\/www\/wordpress\b%', $apacheConfig)) {
            Console::log('Updating wordpress host...');
            file_put_contents(
                $apachePath, 
                preg_replace(
                    '%DocumentRoot\s+.*?\n%i',
                    'DocumentRoot /var/www/wordpress' . PHP_EOL,
                    $apacheConfig
                )
            );
        }

        // Configure PHPMyAdmin
        $apacheConfig = file_get_contents($apachePath = '/etc/apache2/apache2.conf');
        if (!preg_match('%\bphpmyadmin\b%i', $apacheConfig)) {
            Console::log('Updating phpmyadmin host...');
            file_put_contents(
                $apachePath, 
                $apacheConfig 
                    . PHP_EOL . PHP_EOL . '# PHPMyAdmin'
                    . PHP_EOL . 'Include /etc/phpmyadmin/apache.conf'
            );
        }
        
        // Update log rotate
        $logRotatecontent = file_get_contents($logRotatePath = '/etc/logrotate.d/apache2');
        if (!preg_match('%\bcreate 666%i', $logRotatecontent)) {
            Console::log('Updating log rotate permissions...');
            file_put_contents(
                $logRotatePath, 
                preg_replace(
                    '%\bcreate\s*\d+\s*(\w+\s+\w+)%i', 
                    'create 666 $1', 
                    $logRotatecontent
                )
            );
        }
        
        // Update umask
        $envVarsContent = file_get_contents($envVarsPath = '/etc/apache2/envvars');
        if (!preg_match('%\bumask 000\b%i', $envVarsContent)) {
            Console::log('Updating umask...');
            file_put_contents(
                $envVarsPath, 
                preg_replace(
                    '%\bumask\s*\d+%ims', 
                    '', 
                    $envVarsContent
                ) . PHP_EOL . 'umask 000'
            );
        }
        
        // Configure the test domain
        $apacheConfig = file_get_contents($apachePath = '/etc/apache2/sites-available/000-default.conf');
        if (!preg_match('%\b' . preg_quote(Config::get()->domainTest()) . '\b%i', $apacheConfig)) {
            Console::log('Adding preview host "' . Config::get()->domainTest() . '"...');
            file_put_contents(
                $apachePath,
                PHP_EOL . '<VirtualHost *:80>'
                    . PHP_EOL. '    ServerName ' . Config::get()->domainTest()
                    . PHP_EOL. '    DocumentRoot ' . Config::get()->outputPath()
                    . PHP_EOL. '    ServerAdmin webmaster@localhost'
                    . PHP_EOL. '    ErrorLog ${APACHE_LOG_DIR}/error.log'
                    . PHP_EOL. '    CustomLog ${APACHE_LOG_DIR}/access.log combined'
                    . PHP_EOL
                    . PHP_EOL. '    <Directory ' . Config::get()->outputPath() . '>'
                    . PHP_EOL. '        Require all granted'
                    . PHP_EOL. '    </Directory>'
                . PHP_EOL. '</VirtualHost>',
                FILE_APPEND
            );
        } else {
            if (!preg_match('%ServerName ' . preg_quote(Config::get()->domainTest()) . '\n\s+DocumentRoot\s+' . preg_quote(Config::get()->outputPath()) . '\b%ims', $apacheConfig)) {
                Console::log('Updating preview host "' . Config::get()->domainTest() . '"...');
                file_put_contents(
                    $apachePath,
                    preg_replace(
                        '%(ServerName ' . preg_quote(Config::get()->domainTest()) . '\n\s+DocumentRoot)\s+.*?(?=\n)%i', 
                        '$1 ' . Config::get()->outputPath(), 
                        $apacheConfig
                    )
                );
            }
        }

        // Elevate phpmyadmin
        $mysqli = new mysqli('localhost', 'root', '');
        if (!$mysqli->connect_error) {
            Console::log('Granting all privileges to phpmyadmin...');
            $mysqli->query("GRANT ALL PRIVILEGES ON *.* TO 'phpmyadmin'@'localhost';");
            $mysqli->query("FLUSH PRIVILEGES;");
        }

        // Restart Apache
        passthru('/etc/init.d/apache2 restart');
    }

    /**
     * Prepare the WordPress workarea
     */
    public static function wordpress() {
        Console::header('WordPress');
        if (0 !== posix_getuid()) {
            throw new Exception('WordPress installer must run as root');
        }
        
        Console::log('Clean-up...');
        shell_exec('rm -rf /var/www/*');

        Console::log('Downloading the latest WordPress...');
        $archivePath = Temp::getPath(Temp::FOLDER_DOWN) . '/latest.zip';
        !Config::get()->cacheDownload() && is_file($archivePath) && unlink($archivePath);

        // Attempt to download
        if (!Config::get()->cacheDownload() || !is_file(Temp::getPath(Temp::FOLDER_DOWN) . '/latest.zip')) {
            passthru('wget -q --show-progress "https://wordpress.org/latest.zip" -P "' . Temp::getPath(Temp::FOLDER_DOWN) . '"');
        }
        passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' ' . escapeshellarg($archivePath));

        Console::log('Deploying WordPress...');
        passthru('su -c "unzip -o -qq \'' . Temp::getPath(Temp::FOLDER_DOWN) . '/latest.zip\' -d /var/www/" ' . Config::get()->user());
        !Config::get()->cacheDownload() && is_file($archivePath) && unlink($archivePath);

        Console::log('Installing WordPress...');
        Api::run('siteInstall');
        
        Console::log('Exporting database...');
        passthru('mysqldump -u ' . Config::get()->dbUser() . ' -p' . Config::get()->dbPass() . ' ' . Config::get()->dbName() . ' > ' . escapeshellarg($sqlPath = '/var/www/wordpress.sql') . ' 2>&1');
        passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' ' . escapeshellarg($sqlPath));
        passthru('printf "%s%s" "-- " "$(cat \'' . $sqlPath . '\')" > \'' . $sqlPath . '\'');

        Console::log('Initializing repo...');
        file_put_contents('/var/www/wordpress/.gitignore', 'wp-api.php');
        passthru('rm -rf /var/www/wordpress/.git');
        passthru('rm -rf /var/www/wordpress/wp-content/plugins/akismet');
        passthru('git init -q /var/www/wordpress');
        passthru('git -C /var/www/wordpress config --local user.email "user@example.com"');
        passthru('git -C /var/www/wordpress config --local user.name "Mark Jivko"');
        passthru('git -C /var/www/wordpress add -A');
        passthru('git -C /var/www/wordpress commit -q -m "first"');
        passthru('chown ' . Config::get()->user() . '.' . Config::get()->group() . ' -R ' . escapeshellarg('/var/www/wordpress'));

        // Initialize the admin pages list
        if (is_file($getPagesCache = '/var/www/wp-admin.json')) {
            unlink($getPagesCache);
        }
        Run_Plugin::getOptions(true);
        
        // Inform the user
        Console::info('You can now login at http://' . Config::get()->domainWp() . '/wp-admin with "' . Config::get()->siteUser() . '/' . Config::get()->siteUser() . '"');
    }
    
}

/* EOF */