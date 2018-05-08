<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 07/05/2018
 * Time: 15:32
 */

use PHPUnit\Framework\TestCase;

class FormulaParserServiceTest extends TestCase
{
    private $formulaParserService;

    protected function setUp()
    {
        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->disableOriginalConstructor()->getMock();
        $this->formulaParserService = new \AppBundle\Services\FormulaParserService($manager, $logger);
    }

    /**
     * test several correct calculations
     * @dataProvider statisticSuccessProvider
     *
     * @param $stat
     * @param $formula
     * @param $result
     */
    public function testComputeStatistic1($stat, $formula, $result)
    {
        $this->formulaParserService->setUserStatisticsVariables($stat);
        $this->assertEquals($result, $this->formulaParserService->computeStatistic($formula));
    }

    /**
     * test with unknown variable : error
     * @expectedException MathParser\Exceptions\UnknownVariableException
     */
    public function testComputeStatistic2()
    {
        $this->formulaParserService->setUserStatisticsVariables([]);
        $this->assertEquals(0, $this->formulaParserService->computeStatistic('p + unknown'));
    }

    /**
     * test with unknown tokken : error
     * @expectedException MathParser\Exceptions\UnknownTokenException
     */
    public function testComputeStatistic3()
    {
        $this->formulaParserService->setUserStatisticsVariables([]);
        $this->assertEquals(0, $this->formulaParserService->computeStatistic('p;m'));
    }

    /**
     * test division by zero : error
     * @expectedException MathParser\Exceptions\DivisionByZeroException
     */
    public function testComputeStatistic4()
    {
        $this->formulaParserService->setUserStatisticsVariables([]);
        $this->assertEquals(0, $this->formulaParserService->computeStatistic('p/0'));
    }

    /**
     * test parenthesis error : error
     * @expectedException MathParser\Exceptions\ParenthesisMismatchException
     */
    public function testComputeStatistic5()
    {
        $this->formulaParserService->setUserStatisticsVariables([]);
        $this->assertEquals(0, $this->formulaParserService->computeStatistic('(p/1'));
    }

    /**
     * test syntax error : error
     * @expectedException MathParser\Exceptions\SyntaxErrorException
     */
    public function testComputeStatistic6()
    {
        $this->formulaParserService->setUserStatisticsVariables([]);
        $this->assertEquals(0, $this->formulaParserService->computeStatistic('p1/'));
    }

    /**
     * error multiple bad args to setUserStatisticsVariables : error_warning
     * @dataProvider statisticErrorProvider
     *
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessageRegExp /^(array_key_exists\(\) expects parameter 2 to be array,.*)/
     */
    public function testComputeStatistic7($stat, $formula, $result)
    {
        $this->formulaParserService->setUserStatisticsVariables($stat);
        $this->assertEquals($result, $this->formulaParserService->computeStatistic($formula));
    }

    /**
     * test bad argument to computeStatistic
     * @expectedException Error
     * @expectedExceptionMessage Call to a member function accept() on null
     */
    public function testComputeStatistic8()
    {
        $this->formulaParserService->setUserStatisticsVariables([]);
        $this->assertEquals(0, $this->formulaParserService->computeStatistic(null));
    }

    public function statisticSuccessProvider()
    {
        return [
            [['pr' => 0, 'me' => 0], 'p + m', 0],
            [['pr' => 0, 'me' => 2], 'p * m', 0],
            [['pr' => 5, 'me' => 2], 'p / m', 2.5],
            [['pr' => 5, 'me' => 10], 'p - m', -5],
        ];
    }

    public function statisticErrorProvider()
    {
        return [
            [null, 'p', 0],
            ['', 'p', 0],
            [5, 'p', 0],
            [4.0, 'p', 0],
        ];
    }
}
