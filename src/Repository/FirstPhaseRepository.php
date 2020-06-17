<?php

namespace App\Repository;

use App\Entity\FirstPhase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FirstPhase|null find($id, $lockMode = null, $lockVersion = null)
 * @method FirstPhase|null findOneBy(array $criteria, array $orderBy = null)
 * @method FirstPhase[]    findAll()
 * @method FirstPhase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FirstPhaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FirstPhase::class);
    }

    // /**
    //  * @return FirstPhase_[] Returns an array of FirstPhase_ objects
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
    public function findOneBySomeField($value): ?FirstPhase_
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
