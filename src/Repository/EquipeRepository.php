<?php

namespace App\Repository;

use App\Entity\Equipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Equipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Equipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Equipe[]    findAll()
 * @method Equipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipe::class);
    }

    /**
     * @param int $idDeletedDivision
     * @return int|mixed|string
     */
    public function setDeletedDivisionToNull(int $idDeletedDivision)
    {
        return $this->createQueryBuilder('e')
            ->update('App\Entity\Equipe', 'e')
            ->set('e.idDivision', 'NULL')
            ->where('e.idDivision = :idDeletedDivision')
            ->setParameter('idDeletedDivision', $idDeletedDivision)
            ->getQuery()
            ->execute();
    }

    /**
     * @param Equipe $equipe
     * @return int|mixed|string
     */
    public function getEquipesWithSameDivision(Equipe $equipe)
    {
        return $this->createQueryBuilder('e')
            ->where('e.idDivision = :idDivision')
            ->andWhere('e.idDivision IS NOT NULL')
            ->andWhere('e.idEquipe <> :idEquipe')
            ->andWhere('e.idChampionnat = :idChampionnat')
            ->setParameter('idDivision', $equipe->getIdDivision())
            ->setParameter('idEquipe', $equipe->getIdEquipe())
            ->setParameter('idChampionnat', $equipe->getIdChampionnat())
            ->orderBy('e.numero')
            ->addOrderBy('e.idPoule')
            ->getQuery()
            ->execute();
    }
}
