<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/03/2018
 * Time: 12:25
 */

namespace AppBundle\Repository;


class User_CategoryRepository  extends \Doctrine\ORM\EntityRepository
{

    public function findStatLimit($level_id,$limit){
        return $this->getEntityManager()->createQuery(
            'SELECT stat
                  FROM AppBundle:User_Category stat
                  LEFT JOIN stat.level l
                  WHERE l.id = ?1
                  ORDER BY stat.statistic DESC
                  ')
            ->setParameter(1,$level_id)
            ->setMaxResults($limit)
            ->getResult();
    }
}