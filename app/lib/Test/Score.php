<?php
/**
 * Potrivit - Test Score
 * 
 * @copyright  (c) 2021, Mark Jivko
 * @author     Mark Jivko <stephino.team@gmail.com> 
 * @package    Potrivit
 */
class Test_Score extends Widget {
    /**
     * Weights
     * 
     * Critical: Fail entire test regardless of other scores
     * Others: Add more weight to scores in final rating
     */
    const WEIGHT_CRITICAL  = 50; // Installer
    const WEIGHT_IMPORTANT = 35; // Dangerous files, Uninstaller
    const WEIGHT_ELEVATED  = 20; // Server-side errors, user-side errors, SRP
    
    /**
     * List of grades and information
     * 
     * @var array
     */
    protected $_grades = array();
    
    /**
     * Current test case
     * 
     * @var Test_Case
     */
    protected $_testCase = null;
    
    /**
     * Widget data
     * 
     * @return array
     */
    protected function _getData() {
        return $this->_grades;
    }
    
    /**
     * Test Score
     * 
     * @param Test_Case $testCase Test Case
     */
    public function __construct(Test_Case $testCase) {
        $this->_testCase = $testCase;
    }
    
    /**
     * Add a grade with details
     * 
     * @param string           $title       Item name; ex: "File size"
     * @param string|strings[] $description Item description(s); if array, one is chosen at random; ex: "File should not exceed <b>10MB</b>"
     * @param int              $testsFailed (optional) Number of tests failed; default <b>0</b>
     * @param int              $testsTotal  (optional) Total number of tests; default <b>10</b>
     * @param string           $htmlSuccess (optional) HTML used in in case the grade is 100; default <b>empty string</b>
     * @param string           $htmlFailure (optional) HTML used in in case the grade is lower than 100; default <b>empty string</b>
     * @param int              $weight      (optional) Grade weight; default <b>1</b>
     */
    public function grade($title, $description, $testsFailed = 0, $testsTotal = 10, $htmlSuccess = '', $htmlFailure = '', $weight = 1) {
        // Sanitize the grade
        $testsFailed = abs((int) $testsFailed);
        $testsTotal = abs((int) $testsTotal);
        $weight = abs((int) $weight);
        if ($testsFailed > $testsTotal) {
            $testsFailed = $testsTotal;
        }
        if ($weight < 1) {
            $weight = 1;
        }
        $grade = 0 === $testsTotal
            ? 100
            : round(100 * ($testsTotal - $testsFailed) / $testsTotal, 0);
        
        // Choose a random description
        if (is_array($description)) {
            shuffle($description);
        }
        
        // Store the mark
        $this->_grades[] = array(
            $title, 
            is_array($description) ? current($description) : trim($description), 
            $grade, 
            $testsTotal,
            $grade < 100 ? $htmlFailure : $htmlSuccess,
            $weight
        );
    }
    
    /**
     * Get the total score as an integer
     * 
     * @return int [10,100]
     */
    public function getTotal() {
        return 0 === count($this->_grades)
            ? 100
            : (int) round(
                array_sum(
                    array_map(
                        function($item) {
                            return $item[2] * $item[3] * $item[5];
                        },
                        $this->_grades
                    )
                ) / array_sum(
                    array_map(
                        function($item) {
                            return $item[3] * $item[5];
                        },
                        $this->_grades
                    )
                ),
                0
            );
    }
    
    /**
     * Get "x% from y tests" or "passed y tests" or empty string
     * 
     * @return string
     */
    public function getVerbose() {
        $numOfTests = 0;
        foreach ($this->getList() as $grade) {
            $numOfTests += $grade[3];
        }
        
        // Return the string
        return $numOfTests > 0
            ? (
                $this->getTotal() >= 100
                    ? ('Passed ' . $numOfTests . ' ' . (1 === $numOfTests ? 'test' : 'tests'))
                    : ($this->getTotal() . '% from ' . $numOfTests . ' ' . (1 === $numOfTests ? 'test' : 'tests'))
            )
            : 'no tests';
    }
    
    /**
     * Get individual marks
     * 
     * @return array
     */
    public function getList() {
        return $this->_grades;
    }
}

/*EOF*/