# WordPress Code Review

<p align="center">
    <a href="https://potrivit.com">
        <img src="https://repository-images.githubusercontent.com/462785554/0649e40d-fd0e-481b-ab2e-86a4a66003c8"/>
    </a>
</p>

Generate Quality Assurance tests for public WordPress plugins and store them as static HTML pages.

## Install procedure

Install the required packages (LAMP stack & PHPMyAdmin)

```
sudo apt-get install -y apache2 mysql-server php7.4 php-sqlite3 curl phpmyadmin chromium-browser wpcloc pngquant subversion
```

> Store the phpmyadmin password in "./app/config/config.ini"/

Install chrome and chromedriver

```
sudo apt-get install unzip && a=$(uname -m) && rm -r /tmp/chromedriver/
```

```
mkdir /tmp/chromedriver/ &&
wget -O /tmp/chromedriver/LATEST_RELEASE http://chromedriver.storage.googleapis.com/LATEST_RELEASE &&
if [ $a == i686 ]; then b=32; elif [ $a == x86_64 ]; then b=64; fi &&
latest=$(cat /tmp/chromedriver/LATEST_RELEASE) &&
wget -O /tmp/chromedriver/chromedriver.zip 'http://chromedriver.storage.googleapis.com/'$latest'/chromedriver_linux'$b'.zip' &&
sudo unzip /tmp/chromedriver/chromedriver.zip chromedriver -d /usr/local/bin/
```

Store your user and group by running the following commands as yourself (not root) from inside the project folder (same level as run.php)

```sed -i -E "s/\buser\s*=\s*.*$/user = \"$(whoami)\"/" "./app/config/config.ini"```

```sed -i -E "s/\bgroup\s*=\s*.*$/group = \"$(id -g -n)\"/" "./app/config/config.ini"```

Run the installer as root

```sudo php -f run.php install```

You can now use ```wp``` commands which support chaining (```wp first && wp second```)

## Common commands

Plugins:

 * Remove all plugins:  ```wp plugin purge```
 * Install a plugin:    ```wp plugin install {plugin-slug}```
 * Uninstall a plugin:  ```wp plugin uninstall {plugin-slug}```
 * Activate a plugin:   ```wp plugin activate {plugin-slug}```
 * Deactivate a plugin: ```wp plugin deactivate {plugin-slug}```
 
Tests:

 * Run test suites on a plugin: ```wp test run {plugin-slug}```
 * Render all assets and listings: ```wp render```
 * Purge all references: ```wp render run purge```

Batch:
 * Test newly added plugins: ```wp batch new [{limit}]```
 * Test updated plugins:     ```wp batch updates [{limit}]```
 * Re-test local plugins:    ```wp batch local [{limit}]```
