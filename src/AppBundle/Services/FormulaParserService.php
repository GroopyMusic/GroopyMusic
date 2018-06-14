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

    /**
     * here we add a new variable and its description
     */
    const QUERRY_DESCRIPTION = [
        'p' => 'Nombre de tickets achetés au total par un utilisateur',
        'm' => 'Nombre de concerts différents produits par un utilisateur',
        'a' => 'Nombre de parrainés producteurs d\'un utilisateur',
        'v' => 'Nombre de parrainés d\'un utilisateur',
        's' => 'Nombre d\'invitations de parrainage envoyées',
    ];

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->parser = new StdMathParser();
        $this->evaluator = new Evaluator();
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * set category statistics variables
     *
     * Here we add the variable that can be calculated ( attention format)
     *
     * @param $statistic
     */
    public function setUserStatisticsVariables($statistic)
    {
        $this->evaluator->setVariables([
            "p" => array_key_exists('pr', $statistic) ? intval($statistic['pr']) : '0',
            "m" => array_key_exists('me', $statistic) ? intval($statistic['me']) : '0',
            "a" => array_key_exists('amb', $statistic) ? intval($statistic['amb']) : '0',
            "v" => array_key_exists('v', $statistic) ? intval($statistic['v']) : '0',
            "s" => array_key_exists('s', $statistic) ? intval($statistic['s']) : '0'
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


    /**
     * get array with querry description
     *
     * @return array querry_description
     */
    public function getQuerryDescription()
    {
        return self::QUERRY_DESCRIPTION;
    }
}