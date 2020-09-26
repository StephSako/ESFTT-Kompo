<?php

namespace App\Repository;

use App\Entity\DisponibiliteParis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DisponibiliteParis|null find($id, $lockMode = null, $lockVersion = null)
 * @method DisponibiliteParis|null findOneBy(array $criteria, array $orderBy = null)
 * @method DisponibiliteParis[]    findAll()
 * @method DisponibiliteParis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DisponibiliteParisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DisponibiliteParis::class);
    }

    /**
     * Liste des personnes ayant déclaré leur disponibilité pour la journée
     * @param $idJournee
     * @return int|mixed|string
     */
    public function findJoueursDeclares($idJournee)
    {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->leftJoin('d.idCompetiteur', 'c')
            ->addSelect('c')
            ->where('d.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->orderBy('d.disponibilite', 'DESC')
            ->addOrderBy('c.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Liste des joueurs sémectionables pour la composition d'une équipe (joueurs disponibles et non brûlés)
     * @param int $idJournee
     * @param int $idEquipe
     * @return int|mixed|string
     */
    public function findJoueursSelectionnables(int $idJournee, int $idEquipe)
    {
        $query = $this->createQueryBuilder('d')
            ->leftJoin('d.idCompetiteur', 'c')
            ->select('c.com as nom')
            ->addSelect('c.idCompetiteur as idCompetiteur')
            ->where('d.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('d.disponibilite = 1')
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> :idEquipe)")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> :idEquipe)")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> :idEquipe)")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> :idEquipe)")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> :idEquipe)")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> :idEquipe)")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> :idEquipe)")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> :idEquipe)")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> :idEquipe)")
            ->setParameter('idEquipe', $idEquipe);;

        if ($idEquipe == 2) {
            $query->andWhere("(SELECT COUNT(p.id) FROM App\Entity\RencontreParis p WHERE (p.idJoueur1 = d.idCompetiteur OR p.idJoueur2 = d.idCompetiteur OR p.idJoueur3 = d.idCompetiteur OR p.idJoueur4 = d.idCompetiteur OR p.idJoueur5 = d.idCompetiteur OR p.idJoueur5 = d.idCompetiteur OR p.idJoueur6 = d.idCompetiteur OR p.idJoueur7 = d.idCompetiteur OR p.idJoueur8 = d.idCompetiteur OR p.idJoueur9 = d.idCompetiteur) AND p.idJournee < :idJournee AND p.idEquipe = 1) < 3")
                  ->setParameter('idJournee', $idJournee);
        }

        return $query->orderBy('c.nom')
            ->getQuery()
            ->getResult();
    }
}
