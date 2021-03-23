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
            ->andWhere('c.visitor <> true')
            ->orderBy('d.disponibilite', 'DESC')
            ->addOrderBy('c.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Liste des joueurs sélectionables pour la composition d'une équipe (joueurs disponibles et non brûlés)
     * @param int $idJournee
     * @param int $idEquipe
     * @param int $nbJoueurs
     * @param int $limiteBrulage
     * @return array
     */
    public function findJoueursSelectionnables(int $idJournee, int $idEquipe, int $nbJoueurs, int $limiteBrulage): array
    {
        $selectionnablesDQL = $this->createQueryBuilder('d')
            ->leftJoin('d.idCompetiteur', 'c')
            ->select('c.nom')
            ->where('d.idJournee = :idJournee')
            ->andWhere('c.visitor <> true')
            ->andWhere('d.disponibilite = 1');
        $str = '';
        for ($i = 0; $i < $nbJoueurs; $i++) {
            $str .= 'p.idJoueur' . $i . ' = d.idCompetiteur';
            if ($i < $nbJoueurs) $str .= ' OR ';
            $selectionnablesDQL->andWhere("d.idCompetiteur NOT IN (SELECT IF(p' . $i . '.idJoueur' . $i . ' <> 'NULL', p' . $i . '.idJoueur' . $i . ', 0) FROM App\Entity\RencontreDepartementale ' . $i . ' WHERE p' . $i . '.idJournee = d.idJournee AND p' . $i . '.idEquipe <> :idEquipe)");
        }
        $selectionnablesDQL
            ->andWhere('(SELECT COUNT(p.id) FROM App\Entity\RencontreDepartementale p WHERE (' . $str . ') AND p.idJournee < :idJournee AND p.idEquipe < :idEquipe) < ' . $limiteBrulage)
            ->setParameter('idJournee',$idJournee)
            ->setParameter('idEquipe',$idEquipe)
            ->getQuery()
            ->getResult();

        $selectionnables = [];
        foreach ($selectionnablesDQL as $selectionnable){
            array_push($selectionnables, $selectionnable["nom"]);
        }

        return $selectionnables;
    }
}
