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
     * @return int|mixed|string
     */
    public function findSelectionnablesDepartementales(EquipeDepartementale $team, int $idJournee)
    {
        $query = $this->createQueryBuilder('d')
            ->select('d')
            ->leftJoin('d.idCompetiteur', 'c')
            ->addSelect('c')
            ->where('d.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('d.disponibilite = 1');

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
            ->getQuery()
            ->getResult();
    }
}
