<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 11/05/2018
 * Time: 15:04
 */

namespace AppBundle\Services;


use Psr\Log\LoggerInterface;

class ArrayHelperService
{

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * merge arrays (very specific type)
     *
     * @exemple
     * param == [ [ 4 => [ "id" => 4, "pr" => "2", "me" => "1"] , [ 4 => [ "id" => 4, "s" => "0" ] ] ] ]
     * result == [ 4 => [ "id" => 4, "pr" => "2", "me" => "1", "s" => "0" ]]
     *
     * @param array ...$maps
     * @return array
     */
    public function mergeMapOfArray(...$maps)
    {
        $this->logger->warning('test', $maps);
        $map_to_return = [];
        foreach ($maps as $map) {
            foreach ($map as $key => $array) {
                $key = intval($key);
                if (!array_key_exists($key, $map_to_return)) {
                    $map_to_return[$key] = $array;
                } else {
                    foreach ($array as $k => $v) {
                        if ($k != 'id') {
                            $map_to_return[$key][$k] = $v;
                        }
                    }
                }
            }
        }
        $this->logger->warning('test', $map_to_return);
        return $map_to_return;
    }
}