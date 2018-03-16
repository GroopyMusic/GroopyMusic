<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 15/03/2018
 * Time: 10:33
 */

namespace AppBundle\Services;
use Doctrine\ORM\EntityManagerInterface;
use MathParser\StdMathParser;
use MathParser\Interpreting\Evaluator;
use Psr\Log\LoggerInterface;


class FormulaParserService
{
    private $parser;
    private $evaluator;
    private $em;

    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->parser = new StdMathParser();
        $this->evaluator = new Evaluator();
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * compute all catgory point for $user_id
     *
     * @param $user_id
     *
     */
    public function setUserStatisticVariables($user_id){
        $arrayResult = $this->em->getRepository('AppBundle:User')->countUserStatistic($user_id);
        $arrayResult = array_pop($arrayResult);
        $this->evaluator->setVariables([
            "p" =>  $arrayResult[1],
            "m" =>  $arrayResult[2],
            "a" => 10
            //TODO Ambasadorat querry + Transform in 1 Querry
        ]);
    }

    /**
     * Parse and compute formula to get result
     *
     * @param $formula
     * @return mixed points
     */
    public function computeStatistic($formula){
        $AST = $this->parser->parse($formula);
        return $AST->accept($this->evaluator);
    }
}