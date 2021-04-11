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
    public function getNbEquipes(int $type)
    {
        return $this->createQueryBuilder('e')
            ->select('count(e.idEquipe)')
            ->where('e.idDivision IS NOT NULL')
            ->andWhere('e.idChampionnat = :idChampionnat')
            ->setParameter('idChampionnat', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Equipes sans affiliation à une division
     * @return array
     */
    public function getEquipesSansDivision(): array
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
     * @param int $type
     * @return array
     */
    public function getIdEquipesBrulees(string $fonction, int $type): array
    {
        return array_column($this->createQueryBuilder('e')
            ->select('e.idEquipe')
            ->where('e.idDivision IS NOT NULL')
            ->andWhere('e.idEquipe <> (SELECT ' . $fonction . '(e_.idEquipe) from App\Entity\Equipe e_ WHERE e_.idChampionnat = :idChampionnat AND e_.idDivision IS NOT NULL)')
            ->setParameter('idChampionnat', $type)
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
