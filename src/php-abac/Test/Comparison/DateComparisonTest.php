<?php

namespace PhpAbac\Test\Comparison;

use PhpAbac\Comparison\DateComparison;

class DateComparisonTest extends \PHPUnit_Framework_TestCase {
    /** @var DateComparison **/
    protected $comparison;
    
    public function setUp() {
        $this->comparison = new DateComparison();
    }
    
    public function testIsBetween() {
        $start = new \DateTime('2015-08-01');
        $end = new \DateTime('2015-08-16');
        $rightValue = new \DateTime('2015-08-05');
        $wrongValue = new \DateTime('2015-07-18');
        
        $this->assertTrue($this->comparison->isBetween($start, $end, $rightValue));
        $this->assertFalse($this->comparison->isBetween($start, $end, $wrongValue));
    }
}