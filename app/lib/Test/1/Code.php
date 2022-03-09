<?php
/**
 * Potrivit - Test
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     https://markjivko.com 
 * @package    Potrivit
 */
class Test_1_Code extends Test_Case {
    
    // QA arguments for cyclomatic complexity
    const MAX_CX_CLASS  = 1000;
    const MAX_CX_METHOD = 100;
    
    // Dangerous file extensions
    const DANGEROUS_EXT = [
        'action'  => ['Automator Action', 'macOS'],
        'apk'     => ['Application', 'Android'],
        'app'     => ['Executable', 'macOS'],
        'bat'     => ['Batch File', 'Windows'],
        'bin'     => ['Binary Executable', 'Windows, macOS, Linux'],
        'cmd'     => ['Command Script', 'Windows'],
        'com'     => ['Command File', 'Windows'],
        'command' => ['Terminal Command', 'macOS'],
        'cpl'     => ['Control Panel Extension', 'Windows'],
        'csh'     => ['C Shell Script', 'macOS, Linux'],
        'exe'     => ['Executable', 'Windows'],
        'gadget'  => ['Windows Gadget', 'Windows'],
        'inf'     => ['Setup Information File', 'Windows'],
        'ins'     => ['Internet Communication Settings', 'Windows'],
        'inx'     => ['InstallShield Compiled Script', 'Windows'],
        'ipa'     => ['Application', 'iOS'],
        'isu'     => ['InstallShield Uninstaller Script', 'Windows'],
        'job'     => ['Windows Task Scheduler Job File', 'Windows'],
        'jse'     => ['JScript Encoded File', 'Windows'],
        'ksh'     => ['Unix Korn Shell Script', 'Linux'],
        'lnk'     => ['File Shortcut', 'Windows'],
        'msc'     => ['Microsoft Common Console Document', 'Windows'],
        'msi'     => ['Windows Installer Package', 'Windows'],
        'msp'     => ['Windows Installer Patch', 'Windows'],
        'mst'     => ['Windows Installer Setup Transform File', 'Windows'],
        'osx'     => ['Executable', 'macOS'],
        'out'     => ['Executable', 'Linux'],
        'paf'     => ['Portable Application Installer File', 'Windows'],
        'pif'     => ['Program Information File', 'Windows'],
        'prg'     => ['Executable', 'GEM'],
        'ps1'     => ['Windows PowerShell Cmdlet', 'Windows'],
        'reg'     => ['Registry Data File', 'Windows'],
        'rgs'     => ['Registry Script', 'Windows'],
        'run'     => ['Executable', 'Linux'],
        'scr'     => ['Screensaver Executable', 'Windows'],
        'sct'     => ['Windows Scriptlet', 'Windows'],
        'shb'     => ['Windows Document Shortcut', 'Windows'],
        'shs'     => ['Shell Scrap Object', 'Windows'],
        'u3p'     => ['U3 Smart Application', 'Windows'],
        'vb'      => ['VBScript File', 'Windows'],
        'vbe'     => ['VBScript Encoded Script', 'Windows'],
        'vbs'     => ['VBScript File', 'Windows'],
        'vbscript'=> ['Visual Basic Script', 'Windows'],
        'workflow'=> ['Automator Workflow', 'macOS'],
        'ws'      => ['Windows Script', 'Windows'],
        'wsf'     => ['Windows Script', 'Windows'],
        'wsh'     => ['Windows Script Preference', 'Windows'],
        '0xe'     => ['Renamed Virus File', 'F-Secure Internet Security'],
        '73k'     => ['TI-73 Application', 'TI Connect'],
        '89k'     => ['TI-89 Application', 'TI Connect'],
        'a6p'     => ['Authorware 6 Program File', 'Adobe Authorware'],
        'ac'      => ['GNU Autoconf Script', 'Autoconf'],
        'acc'     => ['GEM Accessory File', 'Gemulator'],
        'acr'     => ['ACRobot Script', 'ACRobot'],
        'actm'    => ['AutoCAD Action Macro', 'AutoCAD'],
        'ahk'     => ['AutoHotkey Script', 'AutoHotkey'],
        'air'     => ['Adobe AIR Installation Package', 'Adobe AIR'],
        'app'     => ['FoxPro Application', 'Visual FoxPro'],
        'arscript'=> ['ArtRage Script', 'ArtRage Studio'],
        'as'      => ['Adobe Flash ActionScript File', 'Adobe Flash'],
        'asb'     => ['Alphacam Stone VB Macro', 'Alphacam'],
        'awk'     => ['AWK Script', 'AWK'],
        'azw2'    => ['Kindle Active Content App File', 'Kindle Collection Manager'],
        'beam'    => ['Compiled Erlang File', 'Erlang'],
        'btm'     => ['4DOS Batch File', '4DOS'],
        'cel'     => ['Celestia Script', 'Celestia'],
        'celx'    => ['Celestia Script', 'Celestia'],
        'chm'     => ['Compiled HTML Help File', 'Firefox, IE, Safari'],
        'cof'     => ['MPLAB COFF File', 'MPLAB IDE'],
        'crt'     => ['Security Certificate', 'Firefox, IE, Chrome, Safari'],
        'dek'     => ['Eavesdropper Batch File', 'Eavesdropper'],
        'dld'     => ['EdLog Compiled Program', 'Edlog'],
        'dmc'     => ['Medical Manager Script', 'Sage Medical Manager'],
        'docm'    => ['Word Macro-Enabled Document', 'Microsoft Word'],
        'dotm'    => ['Word Macro-Enabled Template', 'Microsoft Word'],
        'dxl'     => ['Rational DOORS Script', 'Rational DOORS'],
        'ear'     => ['Java Enterprise Archive File', 'Apache Geronimo'],
        'ebm'     => ['EXTRA! Basic Macro', 'EXTRA!'],
        'ebs'     => ['E-Run 1.x Script', 'E-Prime (v1)'],
        'ebs2'    => ['E-Run 2.0 Script', 'E-Prime (v2)'],
        'ecf'     => ['SageCRM Component File', 'SageCRM'],
        'eham'    => ['ExtraHAM Executable', 'HAM Programmer Toolkit'],
        'elf'     => ['Nintendo Wii Game File', 'Dolphin Emulator'],
        'es'      => ['SageCRM Script', 'SageCRM'],
        'ex4'     => ['MetaTrader Program File', 'MetaTrader'],
        'exopc'   => ['ExoPC Application', 'EXOfactory'],
        'ezs'     => ['EZ-R Stats Batch Script', 'EZ-R Stats'],
        'fas'     => ['Compiled Fast-Load AutoLISP File', 'AutoCAD'],
        'fky'     => ['FoxPro Macro', 'Visual FoxPro'],
        'fpi'     => ['FPS Creator Intelligence Script', 'FPS Creator'],
        'frs'     => ['Flash Renamer Script', 'Flash Renamer'],
        'fxp'     => ['FoxPro Compiled Program', 'Visual FoxPro'],
        'gs'      => ['Geosoft Script', 'Oasis Montaj'],
        'ham'     => ['HAM Executable', 'Ham Runtime'],
        'hms'     => ['HostMonitor Script', 'HostMonitor'],
        'hpf'     => ['HP9100A Program File', 'HP9100A Emulator'],
        'hta'     => ['HTML Application', 'Internet Explorer'],
        'iim'     => ['iMacro Macro', 'iMacros (Firefox Add-on)'],
        'ipf'     => ['SMS Installer Script', 'Microsoft SMS'],
        'isp'     => ['Internet Communication Settings', 'Microsoft IIS'],
        'jar'     => ['Java Archive', 'Firefox, IE, Chrome, Safari'],
        'kix'     => ['KiXtart Script', 'KiXtart'],
        'lo'      => ['Interleaf Compiled Lisp File', 'QuickSilver'],
        'ls'      => ['LightWave LScript File', 'LightWave'],
        'mam'     => ['Access Macro-Enabled Workbook', 'Microsoft Access'],
        'mcr'     => ['3ds Max Macroscript or Tecplot Macro', '3ds Max'],
        'mel'     => ['Maya Embedded Language File', 'Maya 2013'],
        'mpx'     => ['FoxPro Compiled Menu Program', 'Visual FoxPro'],
        'mrc'     => ['mIRC Script', 'mIRC'],
        'ms'      => ['3ds Max Script', '3ds Max'],
        'ms'      => ['Maxwell Script', 'Maxwell Render'],
        'mxe'     => ['Macro Express Playable Macro', 'Macro Express'],
        'nexe'    => ['Chrome Native Client Executable', 'Chrome'],
        'obs'     => ['ObjectScript Script', 'ObjectScript'],
        'ore'     => ['Ore Executable', 'Ore Runtime Environment'],
        'otm'     => ['Outlook Macro', 'Microsoft Outlook'],
        'pex'     => ['ProBoard Executable', 'ProBoard BBS'],
        'plx'     => ['Perl Executable', 'ActivePerl or Microsoft IIS'],
        'potm'    => ['PowerPoint Macro-Enabled Design Template', 'Microsoft PowerPoint'],
        'ppam'    => ['PowerPoint Macro-Enabled Add-in', 'Microsoft PowerPoint'],
        'ppsm'    => ['PowerPoint Macro-Enabled Slide Show', 'Microsoft PowerPoint'],
        'pptm'    => ['PowerPoint Macro-Enabled Presentation', 'Microsoft PowerPoint'],
        'prc'     => ['Palm Resource Code File', 'Palm Desktop'],
        'pvd'     => ['Instalit Script', 'Instalit'],
        'pwc'     => ['PictureTaker File', 'PictureTaker'],
        'pyc'     => ['Python Compiled File', 'Python'],
        'pyo'     => ['Python Optimized Code', 'Python'],
        'qpx'     => ['FoxPro Compiled Query Program', 'Visual FoxPro'],
        'rbx'     => ['Rembo-C Compiled Script', 'Rembo Toolkit'],
        'rox'     => ['Actuate Report Object Executable', 'eReport'],
        'rpj'     => ['Real Pac Batch Job File', 'Real Pac'],
        's2a'     => ['SEAL2 Application', 'SEAL'],
        'sbs'     => ['SPSS Script', 'SPSS'],
        'sca'     => ['Scala Script', 'Scala Designer'],
        'scar'    => ['SCAR Script', 'SCAR'],
        'scb'     => ['Scala Published Script', 'Scala Designer'],
        'script'  => ['Generic Script', 'Original Scripting Engine'],
        'smm'     => ['Ami Pro Macro', 'Ami Pro'],
        'spr'     => ['FoxPro Generated Screen File', 'Visual FoxPro'],
        'tcp'     => ['Tally Compiled Program', 'Tally Developer'],
        'thm'     => ['Thermwood Macro', 'Mastercam'],
        'tlb'     => ['OLE Type Library', 'Microsoft Excel'],
        'tms'     => ['Telemate Script', 'Telemate'],
        'udf'     => ['Excel User Defined Function', 'Microsoft Excel'],
        'upx'     => ['Ultimate Packer for eXecutables File', 'Ultimate Packer for eXecutables'],
        'url'     => ['Internet Shortcut', 'Firefox, IE, Chrome, Safari'],
        'vlx'     => ['Compiled AutoLISP File', 'AutoCAD'],
        'vpm'     => ['Vox Proxy Macro', 'Vox Proxy'],
        'wcm'     => ['WordPerfect Macro', 'WordPerfect'],
        'wiz'     => ['Microsoft Wizard File', 'Microsoft Word'],
        'wpk'     => ['WordPerfect Macro', 'WordPerfect'],
        'wpm'     => ['WordPerfect Macro', 'WordPerfect'],
        'xap'     => ['Silverlight Application Package', 'Microsoft Silverlight'],
        'xbap'    => ['XAML Browser Application', 'Firefox, IE'],
        'xlam'    => ['Excel Macro-Enabled Add-In', 'Microsoft Excel'],
        'xlm'     => ['Excel Macro-Enabled Workbook', 'Microsoft Excel'],
        'xlsm'    => ['Excel Macro-Enabled Workbook', 'Microsoft Excel'],
        'xltm'    => ['Excel Macro-Enabled Template', 'Microsoft Excel'],
        'xqt'     => ['SuperCalc Macro', 'CA SuperCalc'],
        'xys'     => ['XYplorer Script', 'XYplorer'],
        'zl9'     => ['Renamed Virus File', 'ZoneAlarm'],
    ];
    
    /**
     * Run a test case
     * 
     * @return mixed Test result
     * @throws Exception
     */
    public function run() {
        $pluginPath = "/var/www/wordpress/wp-content/plugins/{$this->_pluginSlug}/";
        
        // Check file tyles and show lines of code
        $this->_runFileTypes($pluginPath);
        
        // Check cyclomatic complexity and show code structure
        $this->_runComplexity($pluginPath);
    }
    
    /**
     * Check file tyles and show lines of code
     * 
     * @param string $pluginPath
     */
    protected function _runFileTypes($pluginPath) {
        $errors = [];
        
        // Search for dangerous files
        $dExtFound = [];
        foreach (Folder::getIterator($pluginPath) as /*@var $item SplFileInfo*/ $item) {
            if ($item->isFile()) {
                $extension = strtolower(pathinfo($item->getPathname(), PATHINFO_EXTENSION));
                if (isset(self::DANGEROUS_EXT[$extension])) {
                    if (!isset($dExtFound[$extension])) {
                        $dExtFound[$extension] = [];
                    }
                    $dExtFound[$extension][] = preg_replace(
                        '%\/var\/www\/wordpress\/%', 
                        '', 
                        $item->getPathname()
                    );
                }
            }
        }
        
        // Found dangerous extensions
        if (count($dExtFound)) {
            $errorText = Seo::text(Seo::TEXT_INFO_CODE_FILE_DANGEROUS) . '<ul>';
            foreach ($dExtFound as $extension => $dangerousFiles) {
                $errorText .= '<li><b>.' . $extension . '</b> - ' 
                    . self::DANGEROUS_EXT[$extension][0] . ' in ' . self::DANGEROUS_EXT[$extension][1]
                        . '<ul>'
                            . implode(
                                '', 
                                array_map(
                                    function($item) {
                                        return '<li>&#9763; <em>' . $item . '</em></li>';
                                    },
                                    $dangerousFiles
                                )
                            )
                        . '</ul>'
                . '</li>';
            }
            $errorText .= '</ul>';
            $errors[] = $errorText;
        }
        
        // Get the lines of code
        $linesOfCode = json_decode(shell_exec("cloc --json '$pluginPath'"), true);
            
        // Prepare the table
        $clocTable = '<b>' . number_format($linesOfCode['SUM']['code']) . '</b> ' 
            . (1 == $linesOfCode['SUM']['code'] ? 'line' : 'lines') . ' of code in'
            . ' <b>' . number_format($linesOfCode['SUM']['nFiles']) . '</b> ' 
            . (1 == $linesOfCode['SUM']['nFiles'] ? 'file' : 'files') . ':'
            . '<table class="stats">'
                . '<thead>'
                    . '<tr>'
                        . '<th>Language</th>'
                        . '<th>Files</th>'
                        . '<th>Blank lines</th>'
                        . '<th>Comment lines</th>'
                        . '<th>Lines of code</th>'
                    . '</tr>'
                . '</thead>'
                . '<tbody>';
        foreach ($linesOfCode as $lang => $langInfo) {
            if (in_array($lang, ['header', 'SUM'])) {
                continue;
            }
            $clocTable .= '<tr>'
                . '<td>' . $lang . '</td>'
                . '<td>' . number_format($langInfo['nFiles']) . '</td>'
                . '<td>' . number_format($langInfo['blank']) . '</td>'
                . '<td>' . number_format($langInfo['comment']) . '</td>'
                . '<td>' . number_format($langInfo['code']) . '</td>'
            . '</tr>';
        }
        $clocTable .= '</tbody></table>';
        
        // Add the lines of code and file extension test
        $this->getScore()->grade(
            'File types', 
            Seo::text(Seo::TEXT_INFO_CODE_FILE_FIX_DESC), 
            count($errors), 
            1, 
            Seo::text(Seo::TEXT_INFO_CODE_FILE_FIX_SUCCESS) . $clocTable, 
            Seo::text(Seo::TEXT_INFO_CODE_FILE_FIX_FAILURE) 
                . '<ul><li>'
                    . implode('</li><li>', $errors)
                . '</li></ul>' 
                . $clocTable,
            Test_Score::WEIGHT_IMPORTANT
        );
    }
    
    /**
     * Check cyclomatic complexity and show code structure
     * 
     * @param string $pluginPath
     */
    protected function _runComplexity($pluginPath) {
        $errors = [];
        
        // Prepare the command
        $cxLines = preg_split(
            '%[\r\n]+%',
            trim(
                shell_exec("php '" . ROOT . "/res/phploc.phar' '$pluginPath'")
            )
        );
        
        // Store the complexity values
        $cxTable = '<table class="stats">'
            . '<thead>'
                . '<tr><th colspan="2">Cyclomatic complexity</th></tr>'
            . '</thead>'
            . '<tbody>';
        $cxLogging = false;
        $complexity = [];
        $renames = [
            'Average Complexity per LLOC'   => 'Average complexity per logical line of code',
            'Average Complexity per Class'  => 'Average class complexity',
            'Average Complexity per Method' => 'Average method complexity',
        ];
        foreach ($cxLines as $cxLine) {
            if ('Cyclomatic Complexity' === $cxLine) {
                $cxLogging = true;
                continue;
            }
            
            if ('Dependencies' === $cxLine) {
                break;
            }
            
            if ($cxLogging) {
                if (preg_match('%^\s+([\w ]+?)\s*([\d\.]+)$%', $cxLine, $cxMatches)) {
                    $complexity[$cxMatches[1]] = (float) $cxMatches[2];
                    $cxName = $cxMatches[1];
                    if (isset($renames[$cxName])) {
                        $cxName = $renames[$cxName];
                    }
                    $cxTable .= '<tr>'
                        . '<td>' 
                            . (preg_match('%^(minimum|maximum)%i', $cxName) ? '&#9655; ' : '') 
                            . ucfirst(strtolower($cxName)) 
                        . '</td>'
                        . '<td>' . number_format($cxMatches[2], 2). '</td>'
                    . '</tr>';
                }
            }
        }
        $cxTable .= '</tbody></table>';
        
        // Validate complexity
        if ($complexity['Maximum Class Complexity'] > self::MAX_CX_CLASS) {
            $errors[] = Seo::text(
                Seo::TEXT_INFO_CODE_COMP_MAX_CLASS,
                self::MAX_CX_CLASS,
                number_format($complexity['Maximum Class Complexity'])
            );
        }
        if ($complexity['Maximum Method Complexity'] > self::MAX_CX_METHOD) {
            $errors[] = Seo::text(
                Seo::TEXT_INFO_CODE_COMP_MAX_METHOD,
                self::MAX_CX_METHOD,
                number_format($complexity['Maximum Method Complexity'])
            );
        }
        
        // Create the structure table
        $cxTable .= '<table class="stats">'
            . '<thead>'
                . '<tr><th colspan="3">Code structure</th></tr>'
            . '</thead>'
            . '<tbody>';
        $cxLogging = false;
        foreach ($cxLines as $cxLine) {
            if ('Structure' === $cxLine) {
                $cxLogging = true;
                continue;
            }
            
            if ($cxLogging) {
                if (preg_match('%^\s+([\w ]+?)\s*([\d\.]+)\s*?(?:\((.*?)\))?$%', $cxLine, $cxMatches)) {
                    $cxTable .= '<tr>'
                        . '<td>' 
                            . (preg_match('%^([\w ]+)\s(?:classes|methods|functions|constants)%i', $cxMatches[1]) ? '&#9655; ' : '') 
                            . ucfirst(strtolower($cxMatches[1])) 
                        . '</td>'
                        . '<td>' . number_format($cxMatches[2], 0). '</td>'
                        . '<td>' . (isset($cxMatches[3]) ? $cxMatches[3] : '') . '</td>'
                    . '</tr>';
                }
            }
        }
        $cxTable .= '</tbody></table>';
        
        // Create the entry
        $this->getScore()->grade(
            'PHP code', 
            Seo::text(Seo::TEXT_INFO_CODE_COMP_FIX_DESC), 
            count($errors), 
            2, 
            Seo::text(Seo::TEXT_INFO_CODE_COMP_FIX_SUCCESS) . $cxTable, 
            Seo::text(Seo::TEXT_INFO_CODE_COMP_FIX_FAILURE) 
                . '<ul><li>'
                    . implode('</li><li>', $errors)
                . '</li></ul>' 
                . $cxTable
        );
    }
}

/*EOF*/