<?php

namespace App\Repository;

use App\Entity\EquipeDepartementale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EquipeDepartementale|null find($id, $lockMode = null, $lockVersion = null)
 * @method EquipeDepartementale|null findOneBy(array $criteria, array $orderBy = null)
 * @method EquipeDepartementale[]    findAll()
 * @method EquipeDepartementale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipeDepartementaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipeDepartementale::class);
    }

    /**
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getNbEquipesDepartementales()
    {
        return $this->createQueryBuilder('ed')
            ->select('count(ed.idEquipe)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
