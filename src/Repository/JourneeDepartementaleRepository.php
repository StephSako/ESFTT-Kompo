<?php

namespace App\Repository;

use App\Entity\JourneeDepartementale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method JourneeDepartementale|null find($id, $lockMode = null, $lockVersion = null)
 * @method JourneeDepartementale|null findOneBy(array $criteria, array $orderBy = null)
 * @method JourneeDepartementale[]    findAll()
 * @method JourneeDepartementale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JourneeDepartementaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JourneeDepartementale::class);
    }

    /**
     * @return int|mixed|string
     */
    public function findAllDates()
    {
        return $this->createQueryBuilder('jd')
            ->select('jd.date')
            ->addSelect('jd.undefined')
            ->orderBy('jd.date')
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
