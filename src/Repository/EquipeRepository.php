<?php

namespace App\Repository;

use App\Entity\Equipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getNbEquipesDepartementales()
    {
        return $this->createQueryBuilder('ed')
            ->select('count(ed.idEquipe)')
            ->where('ed.idDivision IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Equipes sans affiliation à une division
     * @return int|mixed|string
     */
    public function getEquipesSansDivision()
    {
        return array_column($this->createQueryBuilder('ed')
            ->select('ed.numero')
            ->where('ed.idDivision IS NULL')
            ->orderBy('ed.numero')
            ->getQuery()
            ->getResult(), 'numero');
    }

    /**
     * Liste des IDs des équipes soumises au brûlage
     * @param string $fonction
     * @return int|mixed|string
     */
    public function getIdEquipesBrulees(string $fonction)
    {
        return array_column($this->createQueryBuilder('ed')
            ->select('ed.idEquipe')
            ->where('ed.idDivision IS NOT NULL')
            ->andWhere('ed.idEquipe <> (SELECT ' . $fonction . '(e.idEquipe) from App\Entity\EquipeDepartementale e WHERE e.idDivision IS NOT NULL)')
            ->getQuery()
            ->getResult(), 'idEquipe');
    }

    /**
     * @param int $idDeletedDivision
     * @return int|mixed|string
     */
    public function setDeletedDivisionToNull(int $idDeletedDivision)
    {
        return $this->createQueryBuilder('ed')
            ->update('Equipe.php', 'ed')
            ->set('ed.idDivision', 'NULL')
            ->where('ed.idDivision = :idDeletedDivision')
            ->setParameter('idDeletedDivision', $idDeletedDivision)
            ->getQuery()
            ->execute();
    }
}
