<?php

namespace App\Repository;

use App\Entity\Disponibilite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Disponibilite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Disponibilite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Disponibilite[]    findAll()
 * @method Disponibilite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DisponibiliteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Disponibilite::class);
    }

    /**
     * Liste des personnes ayant déclaré leur disponibilité pour la journée
     * @param int $idJournee
     * @param int $type
     * @return int|mixed|string
     */
    public function findJoueursDeclares(int $idJournee, int $type)
    {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->leftJoin('d.idCompetiteur', 'c')
            ->addSelect('c')
            ->where('d.idJournee = :idJournee')
            ->andWhere('d.idChampionnat = :idChampionnat')
            ->setParameter('idJournee', $idJournee)
            ->setParameter('idChampionnat', $type)
            ->andWhere('c.isLoisir <> true')
            ->andWhere('c.isArchive <> true')
            ->orderBy('d.disponibilite', 'DESC')
            ->addOrderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime toutes les disponibilités d'un joueur devenant loisir ou archivé
     * @param int $idCompetiteur
     * @return int|mixed|string
     */
    public function setDeleteDispos(int $idCompetiteur)
    {
        return $this->createQueryBuilder('dd')
            ->delete('App\Entity\Disponibilite', 'dd')
            ->where('dd.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->getQuery()
            ->execute();
    }
}
