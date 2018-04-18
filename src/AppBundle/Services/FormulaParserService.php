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
    public $querry_descritpions;
    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->parser = new StdMathParser();
        $this->evaluator = new Evaluator();
        $this->em = $em;
        $this->logger = $logger;
        $this->querry_descritpions = [
            'm' => 'Nombre de tickets achetés au total par un utilisateur',
            'p' => 'Nombre de concerts différents produits par un utilisateur',
            'a' => 'Nombre de parrainés producteurs d\'un utilisateur'
        ];
    }

    /**
     * set category statistics variables
     *
     * @param $statistic
     */
    public function setUserStatisticsVariables($statistic)
    {
        $this->evaluator->setVariables([
            "p" => intval($statistic['pr']),
            "m" => intval($statistic['me']),
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
    public function computeStatistic($formula)
    {
        $AST = $this->parser->parse($formula);
        return $AST->accept($this->evaluator);
    }




}