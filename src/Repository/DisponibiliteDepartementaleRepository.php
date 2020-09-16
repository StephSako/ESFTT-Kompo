<?php

namespace App\Repository;

use App\Entity\DisponibiliteDepartementale;
use App\Entity\EquipeDepartementale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DisponibiliteDepartementale|null find($id, $lockMode = null, $lockVersion = null)
 * @method DisponibiliteDepartementale|null findOneBy(array $criteria, array $orderBy = null)
 * @method DisponibiliteDepartementale[]    findAll()
 * @method DisponibiliteDepartementale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DisponibiliteDepartementaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DisponibiliteDepartementale::class);
    }

    /**
     * Liste des dispos par journée
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
     * @param EquipeDepartementale $team
     * @param int $idJournee
     * @param int $idEquipe
     * @return int|mixed|string
     */
    public function findSelectionnablesDepartementales(EquipeDepartementale $team, int $idJournee, int $idEquipe)
    {
        $query = $this->createQueryBuilder('d')
            ->select('d')
            ->leftJoin('d.idCompetiteur', 'c')
            ->addSelect('c')
            ->where('d.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('d.disponibilite = 1')
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1.idJoueur1<>'NULL', p1.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p1 WHERE p1.idJournee = d.idJournee AND p1.idEquipe <> " . $idEquipe . ")")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2.idJoueur2<>'NULL', p2.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p2 WHERE p2.idJournee = d.idJournee AND p2.idEquipe <> " . $idEquipe . ")")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3.idJoueur3<>'NULL', p3.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p3 WHERE p3.idJournee = d.idJournee AND p3.idEquipe <> " . $idEquipe . ")")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4.idJoueur4<>'NULL', p4.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p4 WHERE p4.idJournee = d.idJournee AND p4.idEquipe <> " . $idEquipe . ")");

            switch ($team->getIdEquipe()) {
                case 2:
                    $query
                        ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2");
                    break;
                case 3:
                    $query
                        ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2")
                        ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2");
                    break;
                case 4:
                    $query
                        ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') < 2")
                        ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.2') < 2")
                        ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.3') < 2");
                    break;
            }

            return $query->orderBy('c.nom')
            ->getQuery()->getResult();
    }
}
