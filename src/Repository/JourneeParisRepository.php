<?php

namespace App\Repository;

use App\Entity\JourneeParis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method JourneeParis|null find($id, $lockMode = null, $lockVersion = null)
 * @method JourneeParis|null findOneBy(array $criteria, array $orderBy = null)
 * @method JourneeParis[]    findAll()
 * @method JourneeParis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JourneeParisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JourneeParis::class);
    }

    /**
     * @return int|mixed|string
     */
    public function findAllDates()
    {
        return $this->createQueryBuilder('jp')
            ->select('jp.date')
            ->orderBy('jp.date')
            ->getQuery()
            ->getResult();
    }
}
