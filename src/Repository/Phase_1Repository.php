<?php

namespace App\Repository;

use App\Entity\Phase_1;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Phase_1|null find($id, $lockMode = null, $lockVersion = null)
 * @method Phase_1|null findOneBy(array $criteria, array $orderBy = null)
 * @method Phase_1[]    findAll()
 * @method Phase_1[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Phase_1Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Phase_1::class);
    }

    // /**
    //  * @return Phase_1[] Returns an array of Phase_1 objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Phase_1
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
