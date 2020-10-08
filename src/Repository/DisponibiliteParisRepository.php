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
            ->andWhere('c.visitor <> true')
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
            ->select('c.nom')
            ->where('d.idJournee = :idJournee')
            ->andWhere('c.visitor <> true')
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
            ->andWhere('(SELECT COUNT(_p1.id) FROM App\Entity\RencontreParis _p1 WHERE (_p1.idJoueur1 = d.idCompetiteur OR _p1.idJoueur2 = d.idCompetiteur OR _p1.idJoueur3 = d.idCompetiteur OR _p1.idJoueur4 = d.idCompetiteur) AND _p1.idJournee < :idJournee AND _p1.idEquipe < :idEquipe) < 3')
            ->setParameter('idEquipe', $idEquipe)
            ->setParameter('idJournee', $idJournee)
            ->getQuery()->getResult();

        $selectionnables = [];
        foreach ($query as $selectionnable){
            array_push($selectionnables, $selectionnable["nom"]);
        }

        return $selectionnables;
    }
}
