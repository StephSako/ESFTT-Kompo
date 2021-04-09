<?php

namespace App\Repository;

use App\Entity\Journee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Journee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Journee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Journee[]    findAll()
 * @method Journee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JourneeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Journee::class);
    }

    /**
     * @return int|mixed|string
     */
    public function findAllDates()
    {
        return $this->createQueryBuilder('jd')
            ->select('jd.dateJournee')
            ->addSelect('jd.undefined')
            ->orderBy('jd.dateJournee')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getNbJournee(): int
    {
        return intval($this->createQueryBuilder('j')
            ->select('COUNT(j.idJournee)')
            ->getQuery()
            ->getSingleScalarResult());
    }
}
