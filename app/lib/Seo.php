<?php
/**
 * Potrivit - SEO texts
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Seo {

    /**
     * Order of categories in the final HTML render for plugins
     */
    const CATEGORIES = array(
        Test_Case::PREFIX_BENCHMARKS    => 'Benchmarks',
        Test_Case::PREFIX_OPTIMIZATIONS => 'Optimizations',
    );
    
    // Text keys
    const TEXT_INFO_ABOUT_ERROR_GIT = 'info.about.error.git';
    const TEXT_INFO_ABOUT_ERROR_PLUGIN_NAME = 'info.about.error.plugin.name';
    const TEXT_INFO_ABOUT_ERROR_TAGS = 'info.about.error.tags';
    const TEXT_INFO_ABOUT_ERROR_TAGS_MANY = 'info.about.error.tags.many';
    const TEXT_INFO_ABOUT_ERROR_TAGS_FEW = 'info.about.error.tags.few';
    const TEXT_INFO_ABOUT_ERROR_URI = 'info.about.error.uri';
    const TEXT_INFO_ABOUT_ERROR_VERSION = 'info.about.error.version';
    const TEXT_INFO_ABOUT_ERROR_EMPTY = 'info.about.error.empty';
    const TEXT_INFO_ABOUT_ERROR_CONTRIBS = 'info.about.error.contribs';
    const TEXT_INFO_ABOUT_ERROR_DESC = 'info.about.error.desc';
    const TEXT_INFO_ABOUT_README_FIX = 'info.about.readme.fix';
    const TEXT_INFO_ABOUT_README_FIX_DESC = 'info.about.readme.fix.desc';
    const TEXT_INFO_ABOUT_README_FIX_FINAL = 'info.about.readme.fix.final';
    const TEXT_INFO_ABOUT_SCREEN = 'info.about.screen';
    const TEXT_INFO_ABOUT_SCREEN_LIST = 'info.about.screen.list';
    const TEXT_INFO_ABOUT_SCREEN_IMAGE = 'info.about.screen.image';
    const TEXT_INFO_ABOUT_SCREEN_IMAGE_LIST = 'info.about.screen.image.list';
    const TEXT_INFO_ABOUT_TAGS_NONE = 'info.about.tags.none';
    const TEXT_INFO_ABOUT_MAIN_NAME = 'info.about.main.name';
    const TEXT_INFO_ABOUT_MAIN_NAME_LENGTH_LONG = 'info.about.main.name.length.long';
    const TEXT_INFO_ABOUT_MAIN_DESC_LENGTH_LONG = 'info.about.main.desc.length.long';
    const TEXT_INFO_ABOUT_MAIN_DESC_LENGTH_SHORT = 'info.about.main.desc.length.short';
    const TEXT_INFO_ABOUT_MAIN_DESC_MISSING = 'info.about.main.desc.missing';
    const TEXT_INFO_ABOUT_MAIN_VERSION = 'info.about.main.version';
    const TEXT_INFO_ABOUT_MAIN_VERSION_MISSING = 'info.about.main.version.missing';
    const TEXT_INFO_ABOUT_MAIN_REQ_VERSION = 'info.about.main.req.version';
    const TEXT_INFO_ABOUT_MAIN_REQ_VERSION_DIFF = 'info.about.main.req.version.diff';
    const TEXT_INFO_ABOUT_MAIN_TD = 'info.about.main.td';
    const TEXT_INFO_ABOUT_MAIN_TD_DIFF = 'info.about.main.td.diff';
    const TEXT_INFO_ABOUT_MAIN_DP_SLASH = 'info.about.main.dp.slash';
    const TEXT_INFO_ABOUT_MAIN_DP_FORMAT = 'info.about.main.dp.format';
    const TEXT_INFO_ABOUT_MAIN_DP_MISSING = 'info.about.main.dp.missing';
    const TEXT_INFO_ABOUT_MAIN_FIX = 'info.about.main.fix';
    const TEXT_INFO_ABOUT_MAIN_FIX_DESC = 'info.about.main.fix.desc';
    const TEXT_INFO_ABOUT_SIZE_FIX_IMG = 'info.about.size.fix';
    
    const TEXT_INFO_CODE_FILE_FIX_DESC = 'info.code.file.fix.desc';
    const TEXT_INFO_CODE_FILE_FIX_SUCCESS = 'info.code.file.fix.success';
    const TEXT_INFO_CODE_FILE_FIX_FAILURE = 'info.code.file.fix.failure';
    const TEXT_INFO_CODE_FILE_DANGEROUS = 'info.code.file.dangerous';
    const TEXT_INFO_CODE_COMP_FIX_DESC = 'info.code.comp.fix.desc';
    const TEXT_INFO_CODE_COMP_FIX_SUCCESS = 'info.code.comp.fix.success';
    const TEXT_INFO_CODE_COMP_FIX_FAILURE = 'info.code.comp.fix.failure';
    const TEXT_INFO_CODE_COMP_MAX_CLASS = 'info.code.comp.max.class';
    const TEXT_INFO_CODE_COMP_MAX_METHOD = 'info.code.comp.max.method';
    
    const TEXT_INFO_SIZE_FIX_DESC = 'info.size.fix.desc';
    const TEXT_INFO_SIZE_NO_IMAGES = 'info.size.no.images';
    
    const TEXT_BENCH_FP_INSTALL_FIX_DESC = 'bench.fp.install.fix.desc';
    const TEXT_BENCH_FP_INSTALL_FIX_SUCCESS = 'bench.fp.install.fix.success';
    const TEXT_BENCH_FP_INSTALL_FIX_FAILURE = 'bench.fp.install.fix.failure';
    const TEXT_BENCH_FP_INSTALL = 'bench.fp.install';
    const TEXT_BENCH_FP_UNINSTALL_FIX_DESC = 'bench.fp.uninstall.fix.desc';
    const TEXT_BENCH_FP_UNINSTALL_FIX_SUCCESS = 'bench.fp.uninstall.fix.success';
    const TEXT_BENCH_FP_UNINSTALL_FIX_FAILURE = 'bench.fp.uninstall.fix.failure';
    const TEXT_BENCH_FP_UNINSTALL = 'bench.fp.uninstall';
    const TEXT_BENCH_FP_UNINSTALL_IO = 'bench.fp.uninstall.io';
    const TEXT_BENCH_FP_UNINSTALL_DB_TABLES = 'bench.fp.uninstall.db.tables';
    const TEXT_BENCH_FP_UNINSTALL_DB_OPTIONS = 'bench.fp.uninstall.db.options';
    const TEXT_BENCH_FP_SERVER_FIX_DESC = 'bench.fp.server.fix.desc';
    const TEXT_BENCH_FP_SERVER_FIX_SUCCESS = 'bench.fp.server.fix.success';
    const TEXT_BENCH_FP_SERVER_FIX_FAILURE = 'bench.fp.server.fix.failure';
    const TEXT_BENCH_FP_SERVER_MEM_TOTAL = 'bench.fp.server.mem.total';
    const TEXT_BENCH_FP_SERVER_CPU_TOTAL = 'bench.fp.server.cpu.total';
    const TEXT_BENCH_FP_SERVER_MEM_EXTRA = 'bench.fp.server.mem.extra';
    const TEXT_BENCH_FP_SERVER_CPU_EXTRA = 'bench.fp.server.cpu.extra';
    const TEXT_BENCH_FP_STORAGE_FIX_DESC = 'bench.fp.storage.fix.desc';
    const TEXT_BENCH_FP_STORAGE_FIX_SUCCESS = 'bench.fp.stroage.fix.success';
    const TEXT_BENCH_FP_STORAGE_FIX_FAILURE = 'bench.fp.storage.fix.failure';
    const TEXT_BENCH_FP_STORAGE_OUTSIDE = 'bench.fp.storage.outside';
    const TEXT_BENCH_FP_STORAGE_IO_SIZE = 'bench.fp.storage.io.size';
    const TEXT_BENCH_FP_STORAGE_DB_SIZE = 'bench.fp.storage.db.size';
    const TEXT_BENCH_FP_BROWSER_FIX_DESC = 'bench.fp.browser.fix.desc';
    const TEXT_BENCH_FP_BROWSER_FIX_SUCCESS = 'bench.fp.browser.fix.success';
    const TEXT_BENCH_FP_BROWSER_FIX_FAILURE = 'bench.fp.browser.fix.failure';
    const TEXT_BENCH_FP_BROWSER_NODES = 'bench.fp.browser.nodes';
    const TEXT_BENCH_FP_BROWSER_MEMORY = 'bench.fp.browser.memory';
    const TEXT_BENCH_FP_BROWSER_SCRIPT = 'bench.fp.browser.script';
    const TEXT_BENCH_FP_BROWSER_LAYOUT = 'bench.fp.browser.layout';
    
    const TEXT_BENCH_SMOKE_SERVER_FIX_DESC = 'bench.smoke.server.fix.desc';
    const TEXT_BENCH_SMOKE_SERVER_FIX_SUCCESS = 'bench.smoke.server.fix.success';
    const TEXT_BENCH_SMOKE_SERVER_FIX_FAILURE = 'bench.smoke.server.fix.failure';
    const TEXT_BENCH_SMOKE_SRP_FIX_DESC = 'bench.smoke.srp.fix.desc';
    const TEXT_BENCH_SMOKE_SRP_FIX_SUCCESS = 'bench.smoke.srp.fix.success';
    const TEXT_BENCH_SMOKE_SRP_FIX_FAILURE = 'bench.smoke.srp.fix.failure';
    const TEXT_BENCH_SMOKE_USER_FIX_DESC = 'bench.smoke.user.fix.desc';
    const TEXT_BENCH_SMOKE_USER_FIX_SUCCESS = 'bench.smoke.user.fix.success';
    const TEXT_BENCH_SMOKE_USER_FIX_FAILURE = 'bench.smoke.user.fix.failure';
    const TEXT_BENCH_SMOKE_SRP_OUTPUT = 'bench.smoke.srp.output';
    const TEXT_BENCH_SMOKE_SRP_500 = 'bench.smoke.srp.500';
    
    /**
     * Collection of text variants
     * 
     * @var string[][]
     */
    protected static $_texts = [
        /*** Descriptions ***/
        /* ABOUT */
        self::TEXT_INFO_ABOUT_README_FIX_DESC => [
            'The <b>readme.txt</b> file is an important file in your plugin as it is parsed by WordPress.org to prepare the public listing of your plugin',
            'The <b>readme.txt</b> file is important because it is parsed by WordPress.org for the public listing of your plugin',
            'It\'s important to format your <b>readme.txt</b> file correctly as it is parsed for the public listing of your plugin',
            'The <b>readme.txt</b> file describes your plugin functionality and requirements and it is parsed to prepare the your plugin\'s listing',
            'The <b>readme.txt</b> file uses markdown syntax to describe your plugin to the world',
            'The <b>readme.txt</b> file is undoubtedly the most important file in your plugin, preparing it for public listing on WordPress.org',
            'You should put a lot of thought into formatting <b>readme.txt</b> as it is used by WordPress.org to prepare the public listing of your plugin',
            'Perhaps the most important file in your plugin <b>readme.txt</b> gets parsed in order to generate the public listing of your plugin',
            'Don\'t ignore <b>readme.txt</b> as it is the file that instructs WordPress.org on how to present your plugin to the world',
            'Often overlooked, <b>readme.txt</b> is one of the most important files in your plugin',
        ],
        self::TEXT_INFO_ABOUT_MAIN_FIX_DESC => [
            'This is the main PHP file of %s version <b>%s</b>, providing information about the plugin in the header fields and serving as the principal entry point to the plugin\'s functions',
            'The entry point to %s version <b>%s</b> is a PHP file that has certain tags in its header comment area',
            '%s version <b>%s</b>\'s main PHP file describes plugin functionality and also serves as the entry point to any WordPress functionality',
            'The main file in %s v. <b>%s</b> serves as a complement to information provided in readme.txt and as the entry point to the plugin',
            'The main PHP file in %s ver. <b>%s</b> adds more information about the plugin and also serves as the entry point for this plugin',
            'The principal PHP file in %s v. %s is loaded by WordPress automatically on each request',
            '%s version %s\'s primary PHP file adds more information about the plugin and serves as the entry point for WordPress',
            'Analyzing the main PHP file in %s version <b>%s</b>',
            'The primary PHP file in %s version <b>%s</b> is used by WordPress to initiate all plugin functionality',
            'The main PHP script in %s version <b>%s</b> is automatically included on every request by WordPress',
        ],
        
        /* CODE */
        self::TEXT_INFO_CODE_COMP_FIX_DESC => [
            'An overview of cyclomatic complexity and code structure',
            'An short overview of logical lines of code, cyclomatic complexity, and other code metrics',
            'Analyzing cyclomatic complexity and code structure',
            'Analyzing logical lines of code, cyclomatic complexity, and other code metrics',
            'A short review of cyclomatic complexity and code structure',
            'This is a very shot review of cyclomatic complexity and code structure',
            'Cyclomatic complexity and code structure are the fingerprint of this plugin',
            'A brief analysis of cyclomatic complexity and code structure for this plugin',
            'This is a short overview of cyclomatic complexity and code structure for this plugin',
            'This plugin\'s cyclomatic complexity and code structure detailed below',
        ],
        self::TEXT_INFO_CODE_COMP_FIX_SUCCESS => [
            'No complexity issues detected',
            'There were no cyclomatic complexity issued detected',
            'Everything seems fine, there were no complexity issues found',
            'All good! No complexity issues found',
            'Great job! No cyclomatic complexity issues were detected in this plugin',
            'There are no cyclomatic complexity problems detected for this plugin',
            'This plugin has no cyclomatic complexity issues',
            'This plugin has no cyclomatic complexity problems',
            'Although this was not an exhaustive test, there were no cyclomatic complexity issues detected',
            'No cyclomatic complexity issues were detected for this plugin',
        ],
        self::TEXT_INFO_CODE_COMP_FIX_FAILURE => [
            'Please fix the following',
            'The following items need your attention',
            'These items need your attention',
            'It is recommended to fix the following',
            'Please tend to the following items',
        ],
        self::TEXT_INFO_CODE_FILE_FIX_DESC => [
            'An overview of files in this plugin; executable files are not allowed',
            'A short review of files and their extensions; it is not recommended to include executable files',
            'Executable files are not allowed as they can serve as attack vectors',
            'This is an overview of programming languages used in this plugin; dangerous file extensions are not allowed',
            'A short check of programming languages and file extensions; no executable files are allowed',
            'This is a short overview of programming languages used in this plugin, detecting executable files',
            'Executable files are considered dangerous and should not be included with any WordPress plugin',
            'This is an overview of file extensions present in this plugin and a short test that no dangerous files are bundled with this plugin',
            'There should be no dangerous file extensions present in any WordPress plugin',
            'A short glimpse at programming languages used with this plugin and a check that no dangerous files are present',
        ],
        self::TEXT_INFO_CODE_FILE_FIX_SUCCESS => [
            'No dangerous file extensions were detected',
            'Everything looks great! No dangerous files found in this plugin',
            'There were no executable files found in this plugin',
            'Success! There were no dangerous files found in this plugin',
            'Good job! No executable or dangerous file extensions detected',
        ],
        self::TEXT_INFO_CODE_FILE_FIX_FAILURE => [
            'Please fix the following items',
            'Please make sure to remedy the following',
            'It is important to fix the following items',
            'Almost there! Just fix the following issues',
            'These items require your attention',
        ],
        
        /* SIZE */
        self::TEXT_INFO_SIZE_FIX_DESC => [
            'All PNG images should be compressed to minimize bandwidth usage for end users',
            'PNG files should be compressed to save space and minimize bandwidth usage',
            'It is recommended to compress PNG files in your plugin to minimize bandwidth usage',
            'Using a strong compression for your PNG files is a great way to speed-up your plugin',
            'Often times overlooked, PNG files can occupy unnecessary space in your plugin',
        ],
        self::TEXT_INFO_SIZE_NO_IMAGES => [
            'There are no PNG files in this plugin',
            'No PNG files were detected',
            'There were not PNG files found in your plugin',
            'No PNG images were found in this plugin',
            'PNG images were not found in this plugin',
        ],
        
        /* FOOTPRINT */
        self::TEXT_BENCH_FP_INSTALL_FIX_DESC => [
            'Checking the installer triggered no errors',
            'Verifying that this plugin installs correctly without errors',
            'It is important to correctly install your plugin, without throwing errors or notices',
            'The install procedure must perform silently',
            'All plugins must install correctly, without throwing any errors, warnings, or notices',
        ],
        self::TEXT_BENCH_FP_INSTALL_FIX_SUCCESS => [
            'Installer ran successfully',
            'The plugin installed gracefully, with no errors',
            'The plugin installed successfully, without throwing any errors or notices',
            'Install script ran successfully',
            'This plugin\'s installer ran successfully',
        ],
        self::TEXT_BENCH_FP_INSTALL_FIX_FAILURE => [
            'Please fix the following installer errors',
            'The following installer errors require your attention',
            'You still need to fix the following installer errors',
            'It is recommended to fix the following installer errors',
            'These installer errors require your attention',
        ],
        self::TEXT_BENCH_FP_UNINSTALL_FIX_DESC => [
            'Checking the uninstaller removed all traces of the plugin',
            'Verifying that this plugin uninstalls completely without leaving any traces',
            'It is important to correctly uninstall your plugin, without leaving any traces',
            'The uninstall procedure must remove all plugin files and extra database tables',
            'All plugins must uninstall correctly, removing their source code and extra database tables they might have created',
        ],
        self::TEXT_BENCH_FP_UNINSTALL_FIX_SUCCESS => [
            'Uninstaller ran successfully',
            'The plugin uninstalled completely, with no zombie files or tables',
            'The plugin uninstalled successfully, without leaving any zombie files or tables',
            'Uninstall script ran successfully',
            'This plugin\'s uninstaller ran successfully',
        ],
        self::TEXT_BENCH_FP_UNINSTALL_FIX_FAILURE => [
            'Please fix the following items',
            'The following items require your attention',
            'You still need to fix the following',
            'It is recommended to fix the following',
            'These items require your attention',
        ],
        self::TEXT_BENCH_FP_SERVER_FIX_DESC => [
            'A check of server-side resources used by %s',
            'Server-side resources used by %s',
            'This is a short check of server-side resources used by %s',
            'Analyzing server-side resources used by %s',
            'An overview of server-side resources used by %s',
        ],
        self::TEXT_BENCH_FP_SERVER_FIX_SUCCESS => [
            'Normal server usage',
            'This plugin does not affect your website\'s performance',
            'This plugin has minimal impact on server resources',
            'No issues were detected with server-side resource usage',
            'Server-side resource usage in normal parameters',
        ],
        self::TEXT_BENCH_FP_SERVER_FIX_FAILURE => [
            'It is recommended to improve the following',
            'Please take the time to fix the following items',
            'Please have a look at the following items',
            'Please fix the following',
            'The following require your attention',
        ],
        self::TEXT_BENCH_FP_STORAGE_FIX_DESC => [
            'Filesystem and database footprint',
            'Input-output and database impact of this plugin',
            'Analyzing filesystem and database footprints of this plugin',
            'A short overview of filesystem and database impact',
            'How much does this plugin use your filesystem and database?',
        ],
        self::TEXT_BENCH_FP_STORAGE_FIX_SUCCESS => [
            'The plugin installed successfully',
            'This plugin installed successfully',
            'No storage issues were detected',
            'There were no storage issued detected upon installing this plugin',
            'This plugin was installed successfully',
        ],
        self::TEXT_BENCH_FP_STORAGE_FIX_FAILURE => [
            'Please fix the following',
            'Please try to fix the following items',
            'It is recommended to fix the following issues',
            'Just a few items left to fix',
            'These are issues you should consider',
        ],
        self::TEXT_BENCH_FP_BROWSER_FIX_DESC => [
            'A check of browser resources used by %s',
            '%s: an overview of browser usage',
            'Checking browser requirements for %s',
            'An overview of browser requirements for %s',
            'This is an overview of browser requirements for %s',
        ],
        self::TEXT_BENCH_FP_BROWSER_FIX_SUCCESS => [
            'Normal browser usage',
            'Minimal impact on browser resources',
            'This plugin has a minimal impact on browser resources',
            'This plugin renders optimally with no browser resource issues detected',
            'There were no issues detected in relation to browser resource usage',
        ],
        self::TEXT_BENCH_FP_BROWSER_FIX_FAILURE => [
            'Please improve the following',
            'You may want to improve the following',
            'A great user experience is important, so you should focus on the following',
            'It is time to focus on the following',
            'Having an awesome user experience is important, so please improve the following',
        ],
        
        /* SMOKE */
        self::TEXT_BENCH_SMOKE_SERVER_FIX_DESC => [
            'A shallow check that no server-side errors were triggered',
            'This is a shallow check for server-side errors',
            'A smoke test targeting server-side errors',
            'This is a short smoke test looking for server-side errors',
            'Just a short smoke test targeting errors on the server (in the Apache logs)',
        ],
        self::TEXT_BENCH_SMOKE_SERVER_FIX_SUCCESS => [
            'Everything seems fine, however this is by no means an exhaustive test',
            'Even though no errors were found, this is by no means an exhaustive test',
            'Good news, no errors were detected',
            'Even though everything seems fine, this is not an exhaustive test',
            'The smoke test was a success, however most plugin functionality was not tested',
        ],
        self::TEXT_BENCH_SMOKE_SERVER_FIX_FAILURE => [
            'Please fix the following server-side errors',
            'These server-side errors were triggered',
            'Smoke test failed, please fix the following',
            'Almost there, just fix the following server-side errors',
            'These errors were triggered by the plugin',
        ],
        self::TEXT_BENCH_SMOKE_SRP_FIX_DESC => [
            'A shallow check of the single-responsibility principle; PHP files should perform no action - including output of placeholder text - and trigger no errors when accessed directly',
            'The single-responsibility principle: PHP files have to remain inert when accessed directly, throwing no errors and performing no actions',
            'SRP (Single-Responsibility Principle) - PHP files must act as libraries and never output text or perform any action when accessed directly in a browser',
            'The single-responsibility principle applies for WordPress plugins as well - please make sure your PHP files perform no actions when accessed directly',
            'It is important to ensure that your PHP files perform no action when accessed directly, respecting the single-responsibility principle',
        ],
        self::TEXT_BENCH_SMOKE_SRP_FIX_SUCCESS => [
            'Everything seems fine, however this is by no means an exhaustive test',
            'Congratulations! This plugin passed the SRP test',
            'No output text or server-side errors detected on direct access of PHP files',
            'Looking good! No server-side errors or output on direct access of PHP files',
            'The SRP test was a success',
        ],
        self::TEXT_BENCH_SMOKE_SRP_FIX_FAILURE => [
            'Please fix the following items',
            'The following issues need your attention',
            'Please fix the following',
            'Please take a closer look at the following',
            'Almost there! Just fix the following items',
        ],
        self::TEXT_BENCH_SMOKE_USER_FIX_DESC => [
            'A shallow check that no browser errors were triggered',
            'This is a shallow check for browser errors',
            'This is a smoke test targeting browser errors/issues',
            'This is just a short smoke test looking for browser issues',
            'Just a short smoke test targeting errors on the browser (console and network errors and warnings)',
        ],
        self::TEXT_BENCH_SMOKE_USER_FIX_SUCCESS => [
            'Everything seems fine, but this is not an exhaustive test',
            'No browser issues were found',
            'No browser errors were detected',
            'There were no browser issues found',
            'Everything seems fine on the user side',
        ],
        self::TEXT_BENCH_SMOKE_USER_FIX_FAILURE => [
            'Please fix the following user-side errors',
            'Please take a look at the following user-side issues',
            'There are user-side issues you should fix',
            'These are user-side errors you should fix',
            'Please fix the following browser errors',
        ],
        
        /*** Errors ***/
        self::TEXT_INFO_ABOUT_ERROR_GIT => [
            'A Git repository was detected inside this plugin',
            'An alternative Git repository was detected',
            'Please remove the Git repository from this plugin',
            'Please do not include Git repositories in your plugin',
            'There should be no Git repositories present in your plugin',
        ],
        self::TEXT_INFO_ABOUT_ERROR_PLUGIN_NAME => [
            'Please specify the plugin name on the first line ( %s )',
            'Please replace "Plugin Name" with the name of your plugin on the first line ( %s )',
            'You should set the name of your plugin on the first line ( %s )',
            'Write the name of your plugin instead of "Plugin Name" on the first line ( %s )',
            '"Plugin Name" should be replaced with the name of your plugin on the first line ( %s )',
        ],
        self::TEXT_INFO_ABOUT_ERROR_TAGS => [
            'No valid plugin tags found',
            'The plugin tags were not defined',
            'Please add at least on tag',
            'There were no tags found',
            'No tags were detected',
        ],
        // 2, "tag/tags"
        self::TEXT_INFO_ABOUT_ERROR_TAGS_MANY => [
            'Too many tags (%d %s instead of maximum 10); only the first 5 tags are used in your directory listing',
            'There are too many tags (%d %s instead of maximum 10)',
            'Please reduce the number of tags, currently %d %s instead of maximum 10',
            'You are using too many tags: %d %s instead of maximum 10',
            'Please delete some tags, you are using %d %s instead of maximum 10',
        ],
        // 2, "tag/tags"
        self::TEXT_INFO_ABOUT_ERROR_TAGS_FEW => [
            'Too few tags (%d %s instead of minimum 2); note that only the first 5 tags are used by WordPress.org',
            'Please add more tags, there are currently %d %s instead of minimum 2',
            'This plugin needs more tags, currently using %d %s instead of minimum 2',
            'It is recommended to use more tags (%d %s instead of minimum 2)',
            'Please add more tags (currently %d %s instead of minimum 2)',
        ],
        // "http://x"
        self::TEXT_INFO_ABOUT_ERROR_URI => [
            'Invalid URI ("%s")',
            'Invalid url: "%s"',
            'Please fix this invalid URI: "%s"',
            'Please fix this invalid url: "%s"',
            'Invalid URI found ("%s")',
        ],
        self::TEXT_INFO_ABOUT_ERROR_VERSION => [
            'Invalid version format',
            'Version not formatted correctly',
            'Version format is invalid',
            'The plugin version is formatted incorrectly',
            'Invalid plugin version format',
        ],
        self::TEXT_INFO_ABOUT_ERROR_EMPTY => [
            'Empty value',
            'No value',
            'Not defined',
            'Empty string',
            'No string',
        ],
        self::TEXT_INFO_ABOUT_ERROR_CONTRIBS => [
            'Contributors not specified',
            'Plugin contributors not specified',
            'The plugin contributors field is missing',
            'The plugin contributors field is not present',
            'Plugin contributors field is missing',
        ],
        self::TEXT_INFO_ABOUT_ERROR_DESC => [
            'No description provided',
            'Description not provided',
            'Description not found',
            'No description found',
            'No description available',
        ],
        self::TEXT_INFO_ABOUT_README_FIX => [
            'Please fix the following attributes',
            'These attributes need your attention',
            'Attributes that require attention',
            'These attributes need to be fixed',
            'Attributes that need to be fixed',
        ],
        // "readme.txt" link
        self::TEXT_INFO_ABOUT_README_FIX_FINAL => [
            'You can take inspiration from this %s',
            'Please take inspiration from this %s',
            'You can look at the official %s',
            'The official %s might help',
            'The official %s is a good inspiration',
        ],
        self::TEXT_INFO_ABOUT_SCREEN => [
            'Add a description for screenshot #%s',
            'Please a description for screenshot #%s',
            'A description for screenshot #%s is required',
            'A description for screenshot #%s is missing',
            'Please describe screenshot #%s',
        ],
        self::TEXT_INFO_ABOUT_SCREEN_LIST => [
            'Add descriptions for screenshots',
            'These screenshots lack descriptions',
            'Please add descriptions for these screenshots',
            'No descriptions were found for these screenshots',
            'These screenshots need descriptions',
        ],
        self::TEXT_INFO_ABOUT_SCREEN_IMAGE => [
            'Add an image for screenshot %s',
            'Please add an image for screenshot %s',
            'Screenshot %s image not found',
            'Screenshot %s image required',
            'Screenshot %s image missing',
        ],
        self::TEXT_INFO_ABOUT_SCREEN_IMAGE_LIST => [
            'Add images for these screenshots',
            'These screenshots require images',
            'These screenshots do not have images',
            'Please add images for these screenshots',
            'These screenshots have no corresponding images in /assets',
        ],
        self::TEXT_INFO_ABOUT_TAGS_NONE => [
            'No plugin tags provided',
            'There were no plugin tags found',
            'No tags were found',
            'No tags were detected',
            'There were not plugin tags detected',
        ],
        self::TEXT_INFO_ABOUT_MAIN_NAME => [
            'Name the main plugin file the same as the plugin slug (%s instead of %s)',
            'The principal plugin file should be the same as the plugin slug (%s instead of %s)',
            'Even though not officially enforced, the main plugin file should be the same as the plugin slug (%s instead of %s)',
            'It is recommended to name the main PHP file as the plugin slug (%s instead of %s)',
            'Please rename the main PHP file in this plugin to the plugin slug (%s instead of %s)',
        ],
        self::TEXT_INFO_ABOUT_MAIN_NAME_LENGTH_LONG => [
            'Keep the plugin name shorter than 70 characters (currently %s characters long)',
            'It is recommended to keep the plugin name shorter than 70 characters (currently %s characters long)',
            'Please don\'t use more than 70 characters for the plugin name (currently %s characters long)',
            'A shorter plugin name is better (currently %s characters long instead of max. 70)',
            'Please shorten the plugin name (currently %s characters long instead of max. 70)',
        ],
        self::TEXT_INFO_ABOUT_MAIN_DESC_LENGTH_LONG => [
            'Keep the plugin description shorter than 140 characters (currently %s characters long)',
            'Please keep the plugin description shorter than 140 characters (currently %s characters long)',
            'The description should be shorter than 140 characters (currently %s characters long)',
            'If Twitter did it, so should we! Keep the description under 140 characters (currently %s characters long)',
            'Please don\'t use more than 140 characters for the plugin description (currently %s characters long)',
        ],
        self::TEXT_INFO_ABOUT_MAIN_DESC_LENGTH_SHORT => [
            'The plugin description must be longer than 10 characters (currently %s characters long)',
            'Make the plugin description longer than 10 characters (currently %s characters long)',
            'A good plugin description is longer than 10 characters (currently %s characters long)',
            'Please improve your description making it at least 10 characters long (currently %s characters long)',
            'Please use more than 10 characters for the plugin description (currently %s characters long)',
        ],
        self::TEXT_INFO_ABOUT_MAIN_DESC_MISSING => [
            'The plugin description is missing',
            'This plugin has no description',
            'No description was found for this plugin',
            'This plugin has provided no description',
            'The plugin description was not found',
        ],
        self::TEXT_INFO_ABOUT_MAIN_VERSION => [
            'Plugin version number should only contain digits separated by dots (ex. "1.0.3" instead of %s)',
            'The version number should be digits and periods (ex. "1.0.3" instead of %s)',
            'Use only periods and digits for the version number (ex. "1.0.3" instead of %s)',
            'The version number should only use digits and periods (ex. "1.0.3" instead of %s)',
            'Use periods and digits only for your plugin\'s version number (ex. "1.0.3" instead of %s)',
        ],
        self::TEXT_INFO_ABOUT_MAIN_VERSION_MISSING => [
            'The plugin version is required',
            'Plugin version not found',
            'Plugin version missing',
            'The plugin version was not found',
            'The plugin version was missing',
        ],
        self::TEXT_INFO_ABOUT_MAIN_REQ_VERSION => [
            'Required version number should only contain digits separated by dots (ex. "7.0" instead of %s)',
            'Please use periods and digits for the required version (ex. "7.0" instead of %s)',
            'Periods and digits should be used for the required version number (ex. "7.0" instead of %s)',
            'Required version number formatted incorrectly (ex. "7.0" instead of %s)',
            'Required version number should use periods and dots only (ex. "7.0" instead of %s)',
        ],
        self::TEXT_INFO_ABOUT_MAIN_REQ_VERSION_DIFF => [
            'Required version must be the same as the one declared in readme.txt (%s instead of %s)',
            'Required version does not match the one declared in readme.txt (%s instead of %s)',
            'Required version must match the one declared in readme.txt (%s instead of %s)',
            'The required version number must match the one declared in readme.txt (%s instead of %s)',
            'The required version number did not match the one declared in readme.txt (%s instead of %s)',
        ],
        self::TEXT_INFO_ABOUT_MAIN_TD => [
            'The text domain name must use dashes instead of underscores, and it must be lowercase',
            'The text domain should only use lowercase characters and dashes',
            'Please use dashes and lowercase characters for text domains',
            'The text domain follows the same naming rules as the plugin slug: lowercase characters and dashes',
            'The text domain name should consist of only dashes and lowercase characters',
        ],
        self::TEXT_INFO_ABOUT_MAIN_TD_DIFF => [
            'The text domain is optional since WordPress version <b>4.6</b>; if you do specify it, it must be the same as the plugin slug',
            'Since WordPress version <b>4.6</b> the text domain is optional; if specified, it must be the same as the plugin slug',
            'If you choose to specify the text domain, it must be the same as the plugin slug; optional since WordPress version <b>4.6</b>',
            'You no longer need to specify the text domain since WordPress <b>4.6</b>; it must be the same as the plugin slug',
            'The text domain must be the same as the plugin slug, although optional since WordPress version <b>4.6</b>',
        ],
        self::TEXT_INFO_ABOUT_MAIN_DP_SLASH => [
            'The domain path must begin with a forward slash character ("/%s")',
            'Prefix the domain path with a forward slash character ("/%s")',
            'Please prefix the domain path with a forward slash character ("/%s")',
            'Prefix the domain path with a forward slash character ("/%s")',
            'The domain path should be prefixed with a forward slash character ("/%s")',
        ],
        self::TEXT_INFO_ABOUT_MAIN_DP_FORMAT => [
            'The domain path follows the same naming rules as the domain name, using only dashes and lowercase characters (%s)',
            'Note that the domain path follows the same naming rules as the domain name, using only dashes and lowercase characters (%s)',
            'The domain path should use only dashes and lowercase characters (%s)',
            'Use only dashes and lowercase characters for the domain path (%s)',
            'You should use only dashes and lowercase characters for the domain path (%s)',
        ],
        self::TEXT_INFO_ABOUT_MAIN_DP_MISSING => [
            'The domain path points to a folder that does not exist (%s)',
            'The domain path folder does not exist (%s)',
            'The domain path folder was not found (%s)',
            'The domain path is invalid: folder %s does not exist',
            'The domain path points to an invalid folder, %s does not exist',
        ],
        self::TEXT_INFO_ABOUT_MAIN_FIX => [
            'You should first fix the following items',
            'Please take the time to fix the following',
            'The following require your attention',
            'It is important to fix the following',
            'Please make the necessary changes and fix the following',
        ],
        self::TEXT_INFO_CODE_FILE_DANGEROUS => [
            'Do not distribute dangerous files with your plugin',
            'Do not include executable or dangerous files in your plugin',
            'You should never include executable (binary) or otherwise dangerous files in your plugin',
            'For security reasons, never distribute binary or executable files with your plugin',
            'Even if your plugin relies on executable files (for example a companion app), never distribute executable files with your plugin',
        ],
        self::TEXT_INFO_CODE_COMP_MAX_CLASS => [
            'Please reduce cyclomatic complexity of classes to less than %s (currently <b>%s</b>)',
            'Cyclomatic complexity of classes should be reduced to less than %s (currently <b>%s</b>)',
            'Cyclomatic complexity of classes has to be reduced to less than %s (currently <b>%s</b>)',
            'Class cyclomatic complexity has to be reduced to less than %s (currently <b>%s</b>)',
            'Class cyclomatic complexity should be reduced to less than %s (currently <b>%s</b>)',
        ],
        self::TEXT_INFO_CODE_COMP_MAX_METHOD => [
            'Please reduce cyclomatic complexity of methods to less than %s (currently <b>%s</b>)',
            'Cyclomatic complexity of methods should be reduced to less than %s (currently <b>%s</b>)',
            'Cyclomatic complexity of methods has to be reduced to less than %s (currently <b>%s</b>)',
            'Method cyclomatic complexity has to be reduced to less than %s (currently <b>%s</b>)',
            'Method cyclomatic complexity should be reduced to less than %s (currently <b>%s</b>)',
        ],
        self::TEXT_BENCH_FP_INSTALL => [
            'The plugin cannot be installed',
            'This plugin did not install gracefully',
            'The plugin did not install without errors',
            'Install procedure had errors',
            'Install procedure validation failed for this plugin',
        ],
        self::TEXT_BENCH_FP_UNINSTALL => [
            'This plugin cannot be uninstalled',
            'The plugin did not uninstall gracefully',
            'This plugin did not uninstall without warnings or errors',
            'Uninstall procedure had uncaught errors',
            'Uninstall procedure validation failed for this plugin',
        ],
        self::TEXT_BENCH_FP_UNINSTALL_IO => [
            'The plugin did not uninstall successfully, leaving %s in the plugin directory',
            'Incomplete uninstall procedure, leaving %s in the plugin directory',
            'The plugin did not uninstall correctly, leaving %s in the plugin directory',
            'This plugin has failed uninstalling correctly, leaving %s in the plugin directory',
            'Uninstaller has failed for this plugin, leaving %s in the plugin directory',
        ],
        self::TEXT_BENCH_FP_UNINSTALL_DB_TABLES => [
            'The plugin did not uninstall successfully, leaving %s in the database',
            'The uninstall procedure failed, leaving %s in the database',
            'This plugin does not fully uninstall, leaving %s in the database',
            'Zombie tables detected upon uninstall: %s',
            'Zombie tables were found after uninstall: %s',
        ],
        self::TEXT_BENCH_FP_UNINSTALL_DB_OPTIONS => [
            'This plugin did not uninstall successfully, leaving %s in the database',
            'The uninstall procedure has failed, leaving %s in the database',
            'This plugin does not fully uninstall, leaving %s in the database',
            'Zombie WordPress options detected upon uninstall: %s',
            'Zombie WordPress options were found after uninstall: %s',
        ],
        self::TEXT_BENCH_FP_SERVER_MEM_TOTAL => [
            'Try to keep total memory usage under %s (currently %s on %s)',
            'You should keep total memory usage under %s (currently %s on %s)',
            'Total memory usage should be kept under %s (currently %s on %s)',
            'Total memory usage must be kept under %s (currently %s on %s)',
            'The total memory usage must be kept under %s (currently %s on %s)',
        ],
        self::TEXT_BENCH_FP_SERVER_CPU_TOTAL => [
            'Try to keep total CPU usage under %s (currently %s on %s)',
            'You should keep total CPU usage under %s (currently %s on %s)',
            'Total CPU usage should be kept under %s (currently %s on %s)',
            'Total CPU usage must kept under %s (currently %s on %s)',
            'The total CPU usage must kept under %s (currently %s on %s)',
        ],
        self::TEXT_BENCH_FP_SERVER_MEM_EXTRA => [
            'Try to keep extra memory usage under %s (currently %s on %s)',
            'Extra memory usage must be kept under %s (currently %s on %s)',
            'Extra memory usage should kept under %s (currently %s on %s)',
            'The extra memory usage should kept under %s (currently %s on %s)',
            'The extra memory usage must be under %s (currently %s on %s)',
        ],
        self::TEXT_BENCH_FP_SERVER_CPU_EXTRA => [
            'Try to keep extra CPU usage under %s (currently %s on %s)',
            'Extra CPU usage must be kept under %s (currently %s on %s)',
            'Extra CPU usage should be kept under %s (currently %s on %s)',
            'The extra CPU usage should be kept under %s (currently %s on %s)',
            'The extra CPU usage must be under %s (currently %s on %s)',
        ],
        // "3 files", "3MB", "wp-content/plugins/{x} and wp-content/upoads"
        self::TEXT_BENCH_FP_STORAGE_OUTSIDE => [
            'You have illegally modified %s (%s) outside of %s',
            'There were %s (%s) illegally modified outside of %s',
            'The plugin illegally modified %s (%s) outside of %s',
            'Illegal file modification detected: %s (%s) outside of %s',
            'Illegal file modification found: %s (%s) outside of %s',
        ],
        // "25MB", "11MB"
        self::TEXT_BENCH_FP_STORAGE_IO_SIZE => [
            'Try to limit filesystem usage to %s (currently using %s)',
            'Total filesystem usage should be limited to %s (currently using %s)',
            'Total filesystem usage must be limited to %s (currently using %s)',
            'Filesystem usage must be lower than %s (currently using %s)',
            'The filesystem usage should be lower than %s (currently using %s)',
        ],
        // "25MB", "11MB"
        self::TEXT_BENCH_FP_STORAGE_DB_SIZE => [
            'Try to limit database usage to %s (currently using %s)',
            'You should limit database usage to %s (currently using %s)',
            'Total database usage must be lower than %s (currently using %s)',
            'Total database footprint must be lower than %s (currently using %s)',
            'You should limit the database footprint to less than %s (currently using %s)',
        ],
        self::TEXT_BENCH_FP_BROWSER_NODES => [
            'Try to keep the DOM nodes under %s (currently %s on %s)',
            'Limit the number of DOM nodes under %s (currently %s on %s)',
            'You must limit the number of DOM nodes under %s (currently %s on %s)',
            'For the best user experience, please reduce the number of DOM nodes under %s (currently %s on %s)',
            'It is recommended to reduce the number of DOM nodes under %s (currently %s on %s)',
        ],
        self::TEXT_BENCH_FP_BROWSER_MEMORY => [
            'Try to limit used browser memory to %s (currently %s on %s)',
            'You must limit browser memory usage to %s (currently %s on %s)',
            'In order to improve user experience, please limit browser memory usage to %s (currently %s on %s)',
            'Browser memory usage must be limited to %s (currently %s on %s)',
            'Total browser memory usage should be limited to %s (currently %s on %s)',
        ],
        self::TEXT_BENCH_FP_BROWSER_SCRIPT => [
            'Try to keep JavaScript execution under %s (currently %s on %s)',
            'JavaScript execution time must be kept under %s (currently %s on %s)',
            'Total JavaScript execution time should be lower than %s (currently %s on %s)',
            'Please improve your JavaScript code, lowering total execution time to less than %s (currently %s on %s)',
            'Total JavaScript execution time should be lower than %s (currently %s on %s)',
        ],
        self::TEXT_BENCH_FP_BROWSER_LAYOUT => [
            'Try to keep CSS layout execution time under %s (currently %s on %s)',
            'Total CSS layout time should be under %s (currently %s on %s)',
            'CSS layout must be faster than %s (currently %s on %s)',
            'For a better user experience, CSS layout must be faster than %s (currently %s on %s)',
            'Improve the UX by limiting CSS layout time to less than %s (currently %s on %s)',
        ],
        self::TEXT_BENCH_SMOKE_SRP_OUTPUT => [
            'PHP files output text when accessed directly',
            'GET requests to PHP files return non-empty strings',
            'PHP files output non-empty strings when accessed directly via GET requests',
            'PHP files perform the task of outputting text when accessed with GET requests',
            'PHP files perform the action of outputting non-empty strings when accessed directly',
        ],
        self::TEXT_BENCH_SMOKE_SRP_500=> [
            'PHP files trigger server errors when accessed directly',
            'PHP files trigger server-side errors or warnings when accessed directly',
            'GET requests to PHP files trigger server-side errors or Error 500 responses',
            'GET requests to PHP files have triggered server-side errors or warnings',
            'PHP files trigger errors when accessed directly with GET requests',
        ],
    ];
    
    /**
     * Collection of main page descriptions; you can use one of the following placeholders: <ul>
     * <li>__SLUG__</li>
     * <li>__NAME__</li>
     * <li>__VER__   </li>
     * <li>__VER_PHP__</li>
     * <li>__VER_WP__ </li>
     * <li>__VER_WP_T__</li>
     * <li>__RATING_VALUE__</li>
     * <li>__RATING_COUNT__</li>
     * <li>__SCORE_INFORMATION_ABOUT__</li>
     * <li>...__SCORE_{CATEGORY}_{TEST}__</li>
     * </ul>
     * 
     * @var string[]
     */
    protected static $_desc = [
        0 => [
            'Just __RATING_VALUE__% from __RATING_COUNT__ code reviews? Find out how to improve __NAME__',
            'A terrible score of __RATING_VALUE__% from __RATING_COUNT__ tests? Find out how to improve __NAME__',
            'Learn how to improve __NAME__. This WordPress plugin scored only __RATING_VALUE__% from __RATING_COUNT__ code reviews',
            'Learn from the mistakes made by __NAME__. This plugin scored only __RATING_VALUE__% from __RATING_COUNT__ tests',
            'Oof. Terrible performance by __NAME__. This WordPress plugin scored only __RATING_VALUE__% from __RATING_COUNT__ code reviews',
            
            'It\'s time to improve __NAME__. This WordPress plugin scored only __RATING_VALUE__% from __RATING_COUNT__ tests',
            'This is awful. __NAME__ scored only __RATING_VALUE__% from __RATING_COUNT__ tests',
            'It\'s time for a complete rewrite. "__NAME__" scored only __RATING_VALUE__% from __RATING_COUNT__ code reviews',
            'How not to write a WordPress plugin. "__NAME__" scored only __RATING_VALUE__% from __RATING_COUNT__ tests',
            'As bad as it gets. "__NAME__" WordPress plugin scored only __RATING_VALUE__% from __RATING_COUNT__ code reviews',
        ],
        75 => [
            'Mediocre results by __NAME__, just __RATING_VALUE__% from __RATING_COUNT__ tests',
            '__NAME__ is barely passing with just __RATING_VALUE__% from __RATING_COUNT__ code reviews',
            'What not to do in a WordPress plugin? __NAME__ scored just __RATING_VALUE__% from __RATING_COUNT__ tests',
            'Not terrible. Not Good. __NAME__ scored just __RATING_VALUE__% from __RATING_COUNT__ code reviews',
            'It\'s time for a major update! __NAME__ scored just __RATING_VALUE__% from __RATING_COUNT__ tests',
            
            'Anyone can learn from the mistakes of __NAME__. This WordPress plugin scored just __RATING_VALUE__% from __RATING_COUNT__ tests',
            'What your plugin does better than __NAME__. This WordPress plugin scored just __RATING_VALUE__% from __RATING_COUNT__ code reviews',
            'Most plugins score higher than __NAME__. Why did __NAME__ score just __RATING_VALUE__% from __RATING_COUNT__ tests?',
            '__NAME__, get your act together! Why did __NAME__ score just __RATING_VALUE__% from __RATING_COUNT__ code reviews?',
            '__NAME__, let\'s improve these scores! Why did __NAME__ score just __RATING_VALUE__% from __RATING_COUNT__ tests?',
        ],
        85 => [
            'Nice results by __NAME__ with a score of __RATING_VALUE__% from __RATING_COUNT__ tests',
            '__NAME__ is a great WordPress plugin with a score of __RATING_VALUE__% from __RATING_COUNT__ code reviews',
            'How dit __NAME__ get a score of __RATING_VALUE__% from __RATING_COUNT__ tests?',
            '__NAME__ is an awesome WordPress plugin with a __RATING_VALUE__% score from __RATING_COUNT__ tests',
            'Good job, __NAME__! Learn how this plugin scored __RATING_VALUE__% from __RATING_COUNT__ tests',
            
            'Way to go, __NAME__! Learn more about how this plugin scored __RATING_VALUE__% from __RATING_COUNT__ code reviews',
            '__NAME__ is an awesome WordPress plugin that scored __RATING_VALUE__% from __RATING_COUNT__ tests',
            'Congratulations __NAME__! How can you get your plugin to score __RATING_VALUE__% from __RATING_COUNT__ code reviews?',
            '__NAME__ just scored __RATING_VALUE__% from __RATING_COUNT__ tests. How did they do it?',
            '__NAME__ is a WordPress plugin with a score of __RATING_VALUE__% from __RATING_COUNT__ code reviews. Learn how they did it.',
        ],
        99 => [
            'Perfect score! Learn how to get a score __RATING_VALUE__% from __RATING_COUNT__ tests from __NAME__',
            'Exceptional results by __NAME__ - just scored __RATING_VALUE__% from __RATING_COUNT__ code reviews',
            'Learn from the best: __NAME__ just scored __RATING_VALUE__% from __RATING_COUNT__ tests',
            '__NAME__ is one of the very best WordPress plugins. Learn how they just scored __RATING_VALUE__% from __RATING_COUNT__ tests',
            '__NAME__ is an exceptional WordPress plugin. Learn how they just scored __RATING_VALUE__% from __RATING_COUNT__ code reviews',
            
            'The best of the best! __NAME__ aced all __RATING_COUNT__ WordPress tests',
            'This is one of the best WordPress plugins! __NAME__ aced all __RATING_COUNT__ WordPress code reviews',
            '__NAME__ aced all __RATING_COUNT__ WordPress tests, making it one of the best plugins out there',
            'How did __NAME__ ace all __RATING_COUNT__ WordPress code reviews?',
            'Learn how to ace our WordPress tests like a pro. The story of __NAME__',
        ],
    ];
    
    /**
     * Get a random page description, cached for each plugin page
     * 
     * @return string
     */
    public static function desc($ratingValue) {
        $cacheData = Cache_Data::get(Tester::getSlug());
        $cachedRating = $cacheData->getRating();
        
        // Cache miss on the SEO description template or new score
        if (!strlen($cacheData->getSeoDescription()) || $cachedRating[0] != $ratingValue) {
            // Prepare the rating keys in descending order
            $ratingKeys = array_keys(self::$_desc);
            rsort($ratingKeys);
            
            // Store the description key
            foreach ($ratingKeys as $descKey) {
                if ($ratingValue >= $descKey) {
                    break;
                }
            }
            
            $cacheData->setSeoDescription(
                self::$_desc[$descKey][mt_rand(0, count(self::$_desc[$descKey]) - 1)]
            );
        }
        
        return $cacheData->getSeoDescription();
    }
    
    /**
     * Get the English text of a PHP error
     * 
     * @param int $errorCode Error code
     * @return string
     */
    public static function getError($errorCode) {
        $errorTypes = [
            E_RECOVERABLE_ERROR => 'Recoverable error',
            E_STRICT            => 'Strict error',
            E_PARSE             => 'Parse error',
            
            E_CORE_ERROR        => 'Core error',
            E_CORE_WARNING      => 'Core warning',
            
            E_COMPILE_ERROR     => 'Compile error',
            E_COMPILE_WARNING   => 'Compile warning',
            
            E_ERROR             => 'Error',
            E_WARNING           => 'Warning',
            E_NOTICE            => 'Notice',
            E_DEPRECATED        => 'Deprecated',
            
            E_USER_ERROR        => 'User error',
            E_USER_WARNING      => 'User warning',
            E_USER_NOTICE       => 'User notice',
            E_USER_DEPRECATED   => 'User deprecated',
        ];
        
        return isset($errorTypes[$errorCode])
            ? $errorTypes[$errorCode]
            : 'Unknown error';
    }
    
    /**
     * Sprintf-ready texts identified by key, cached for each plugin page
     * 
     * @param string $key       Text repo key
     * @param mixed  ...$string (optional) Arguments to sprintf call
     * @return string|null Random string from the data store or null if key not found
     */
    public static function text($key) {
        $arguments = func_get_args();
        
        // SEO text not cached
        $cacheData = Cache_Data::get(Tester::getSlug());
        if (null === $cacheData->getSeoText($key)) {
            $cacheData->setSeoText(
                $key, 
                isset(self::$_texts[$key])
                    ? self::$_texts[$key][mt_rand(0, count(self::$_texts[$key]) - 1)]
                    : null
            );
        }
        
        // Replace the first argument
        $arguments[0] = $cacheData->getSeoText($key);
        
        // Prepare the result
        $result = null !== $arguments[0] && count($arguments) > 1
            ? call_user_func_array('sprintf', $arguments)
            : $arguments[0];
        
        // Log invalid results
        if (null === $result) {
            Console::log('Invalid SEO key "' . $key . '"', false);
        }
        
        return $result;
    }
    
}

/*EOF*/