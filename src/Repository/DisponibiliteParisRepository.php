<?php

namespace App\Repository;

use App\Entity\DisponibiliteParis;
use App\Entity\EquipeParis;
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
     * @param $idJournee
     * @return int|mixed|string
     */
    public function findAllDisposByJournee($idJournee)
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
     * Liste de toutes les disponibilités du championnat départemental affichée dans le back-office
     * @return int|mixed|string
     */
    public function findAllDispos()
    {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->leftJoin('d.idCompetiteur', 'c')
            ->addSelect('c')
            ->orderBy('c.nom')
            ->addOrderBy('d.idJournee')
            ->getQuery()
            ->getResult();
    }

    /**
     * Liste des joueurs sémectionables pour la composition d'une équipe (joueurs disponibles et non brûlés)
     * @param EquipeParis $team
     * @param int $idJournee
     * @param int $idEquipe
     * @return int|mixed|string
     */
    public function findSelectionnablesParis(EquipeParis $team, int $idJournee, int $idEquipe)
    {
        $query = $this->createQueryBuilder('d')
            ->select('d')
            ->leftJoin('d.idCompetiteur', 'c')
            ->addSelect('c')
            ->where('d.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('d.disponibilite = 1')
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1 <> 'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreParis p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $idEquipe . ")")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2 <> 'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreParis p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $idEquipe . ")")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3 <> 'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreParis p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $idEquipe . ")")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4 <> 'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreParis p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $idEquipe . ")")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p5.idJoueur5 <> 'NULL', p5.idJoueur5, 0) FROM App\Entity\RencontreParis p5 WHERE p5.idJournee = d.idJournee AND p5.idEquipe <> " . $idEquipe . ")")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p6.idJoueur6 <> 'NULL', p6.idJoueur6, 0) FROM App\Entity\RencontreParis p6 WHERE p6.idJournee = d.idJournee AND p6.idEquipe <> " . $idEquipe . ")")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p7.idJoueur7 <> 'NULL', p7.idJoueur7, 0) FROM App\Entity\RencontreParis p7 WHERE p7.idJournee = d.idJournee AND p7.idEquipe <> " . $idEquipe . ")")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p8.idJoueur8 <> 'NULL', p8.idJoueur8, 0) FROM App\Entity\RencontreParis p8 WHERE p8.idJournee = d.idJournee AND p8.idEquipe <> " . $idEquipe . ")")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p9.idJoueur9 <> 'NULL', p9.idJoueur9, 0) FROM App\Entity\RencontreParis p9 WHERE p9.idJournee = d.idJournee AND p9.idEquipe <> " . $idEquipe . ")");

        switch ($team->getIdEquipe()) {
            case 2:
                $query
                    ->andWhere("JSON_VALUE(c.brulageParis, '$.1') < 3");
                break;
        }

        return $query->orderBy('c.nom')
            ->getQuery()
            ->getResult();
    }
}
