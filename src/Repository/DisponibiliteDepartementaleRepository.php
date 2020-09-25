<?php

namespace App\Repository;

use App\Entity\DisponibiliteDepartementale;
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
            ->select('d')
            ->leftJoin('d.idCompetiteur', 'c')
            ->addSelect('c')
            ->where('d.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('d.disponibilite = 1')
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p1_.idJoueur1<>'NULL', p1_.idJoueur1, 0) FROM App\Entity\RencontreDepartementale p1_ WHERE p1_.idJournee = d.idJournee AND p1_.idEquipe <> :idEquipe)")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p2_.idJoueur2<>'NULL', p2_.idJoueur2, 0) FROM App\Entity\RencontreDepartementale p2_ WHERE p2_.idJournee = d.idJournee AND p2_.idEquipe <> :idEquipe)")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p3_.idJoueur3<>'NULL', p3_.idJoueur3, 0) FROM App\Entity\RencontreDepartementale p3_ WHERE p3_.idJournee = d.idJournee AND p3_.idEquipe <> :idEquipe)")
            ->andWhere("d.idCompetiteur NOT IN (SELECT IF(p4_.idJoueur4<>'NULL', p4_.idJoueur4, 0) FROM App\Entity\RencontreDepartementale p4_ WHERE p4_.idJournee = d.idJournee AND p4_.idEquipe <> :idEquipe)")
            ->setParameter('idEquipe', $idEquipe);

        switch ($idEquipe) {
            case 2:
                $query
                    ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = d.idCompetiteur OR p1.idJoueur2 = d.idCompetiteur OR p1.idJoueur3 = d.idCompetiteur OR p1.idJoueur4 = d.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                    ->setParameter('idJournee', $idJournee);
                break;
            case 3:
                $query
                    ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = d.idCompetiteur OR p1.idJoueur2 = d.idCompetiteur OR p1.idJoueur3 = d.idCompetiteur OR p1.idJoueur4 = d.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                    ->andWhere('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = d.idCompetiteur OR p2.idJoueur2 = d.idCompetiteur OR p2.idJoueur3 = d.idCompetiteur OR p2.idJoueur4 = d.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) < 2')
                    ->setParameter('idJournee', $idJournee);
                break;
            case 4:
                $query
                    ->andWhere('(SELECT COUNT(p1.id) FROM App\Entity\RencontreDepartementale p1 WHERE (p1.idJoueur1 = d.idCompetiteur OR p1.idJoueur2 = d.idCompetiteur OR p1.idJoueur3 = d.idCompetiteur OR p1.idJoueur4 = d.idCompetiteur) AND p1.idJournee < :idJournee AND p1.idEquipe = 1) < 2')
                    ->andWhere('(SELECT COUNT(p2.id) FROM App\Entity\RencontreDepartementale p2 WHERE (p2.idJoueur1 = d.idCompetiteur OR p2.idJoueur2 = d.idCompetiteur OR p2.idJoueur3 = d.idCompetiteur OR p2.idJoueur4 = d.idCompetiteur) AND p2.idJournee < :idJournee AND p2.idEquipe = 2) < 2')
                    ->andWhere('(SELECT COUNT(p3.id) FROM App\Entity\RencontreDepartementale p3 WHERE (p3.idJoueur1 = d.idCompetiteur OR p3.idJoueur2 = d.idCompetiteur OR p3.idJoueur3 = d.idCompetiteur OR p3.idJoueur4 = d.idCompetiteur) AND p3.idJournee < :idJournee AND p3.idEquipe = 3) < 2')
                    ->setParameter('idJournee',$idJournee);
                break;
        }

            return $query->orderBy('c.nom')
            ->getQuery()->getResult();
    }
}
