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
    public function findAllDispos($idJournee)
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
     * @param EquipeParis $team
     * @param int $idJournee
     * @return int|mixed|string
     */
    public function findSelectionnablesParis(EquipeParis $team, int $idJournee)
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
                    ->andWhere("JSON_VALUE(c.brulageParis, '$.1') < 2");
                break;
        }

        return $query->orderBy('c.nom')
            ->getQuery()
            ->getResult();
    }
}
