<?php

namespace AppBundle\Repository\YB;


class PublicTransportStationRepository extends \Doctrine\ORM\EntityRepository{

    public function getStationsFromInfos($stationName, $stationLat, $stationLon, $transportType, $distance){
        return $this->createQueryBuilder('s')
            ->where('s.name = :name')
            ->andWhere('s.latitude = :lat')
            ->andWhere('s.longitude = :lon')
            ->andWhere('s.type = :type')
            ->andWhere('s.distance = :dist')
            ->setParameter('name', $stationName)
            ->setParameter('lat', $stationLat)
            ->setParameter('lon', $stationLon)
            ->setParameter('type', $transportType)
            ->setParameter('dist', $distance)
            ->getQuery()
            ->getOneOrNullResult();
    }

}