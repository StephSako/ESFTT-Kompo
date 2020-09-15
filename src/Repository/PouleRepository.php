<?php

namespace App\Repository;

use App\Entity\Poule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Poule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Poule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Poule[]    findAll()
 * @method Poule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PouleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Poule::class);
    }
}
