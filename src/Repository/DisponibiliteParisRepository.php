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
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();
    }
}
