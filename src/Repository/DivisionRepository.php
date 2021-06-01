<?php

namespace App\Repository;

use App\Entity\Division;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Division|null find($id, $lockMode = null, $lockVersion = null)
 * @method Division|null findOneBy(array $criteria, array $orderBy = null)
 * @method Division[]    findAll()
 * @method Division[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DivisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Division::class);
    }

    /**
     * Retourne le nombre maximal de joueurs de toutes les divisions
     * @return array
     * @throws NonUniqueResultException
     */
    public function getNbJoueursMax(): array
    {
        return $this->createQueryBuilder('d')
            ->select('MAX(d.nbJoueurs) as nbMaxJoueurs')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
