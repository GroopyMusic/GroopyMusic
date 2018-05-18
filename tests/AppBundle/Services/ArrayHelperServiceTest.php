<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 11/05/2018
 * Time: 15:27
 */

use AppBundle\Services\ArrayHelperService;
use AppBundle\Services\RankingService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ArrayHelperServiceTest extends TestCase
{
    private $logger;
    private $arrayHelperService;

    protected function setUp()
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $this->arrayHelperService = $this->getMockBuilder(ArrayHelperService::class)
            ->setMethods(null)
            ->setConstructorArgs(array($this->logger))
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->logger);
        unset($this->arrayHelperService);
    }

    /**
     * test with
     * param == [ [ 4 => [ "id" => 4, "pr" => "2", "me" => "1"] , [ 4 => [ "id" => 4, "s" => "0" ] ] ] ]
     * result == [ 4 => [ "id" => 4, "pr" => "2", "me" => "1", "s" => "0" ]]
     */
    public function testMergeMapOfArray1(){
        $result = [ 4 => [ "id" => 4, "pr" => "2", "me" => "1", "s" => "0" ]];
        $this->assertEquals($result,$this->arrayHelperService->mergeMapOfArray(
            [ 4 => [ "id" => 4, "pr" => "2", "me" => "1"] ] , [ 4 => [ "id" => 4, "s" => "0" ] ]
        ));
    }

    /**
     * test success
     */
    public function testMergeMapOfArray2(){
        $result = [ 4 => [ "id" => 4, "pr" => "2", "me" => "1"] , 5 => [ "id" => 4, "s" => "0" ] ];
        $this->assertEquals($result,$this->arrayHelperService->mergeMapOfArray(
            [ 4 => [ "id" => 4, "pr" => "2", "me" => "1"] ] , [ 5 => [ "id" => 4, "s" => "0" ] ]
        ));
    }

    /**
     * test success
     */
    public function testMergeMapOfArray3(){
        $result = [ 4 => [ "id" => 4, "pr" => "2", "me" => "1", "test" => "ko"] , 5 => [ "id" => 4, "s" => "0", "test" => "ok"] ];
        $this->assertEquals($result,$this->arrayHelperService->mergeMapOfArray(
            [ 4 => [ "id" => 4, "pr" => "2", "me" => "1"] ] , [ 5 => [ "id" => 4, "s" => "0" ] ] , [ 5 => [ "test" => "ok" ] , 4 => [ "test" => "ko"] ]
        ));
    }

    /**
     * test empty params
     */
    public function testMergeMapOfArray4(){
        $this->assertEmpty($this->arrayHelperService->mergeMapOfArray([]));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * test empty params
     */
    public function testMergeMapOfArray5(){
        $this->arrayHelperService->mergeMapOfArray(null);
    }
}
