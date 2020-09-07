<?php

namespace App\Repository;

use App\Entity\RencontreParis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RencontreParis|null find($id, $lockMode = null, $lockVersion = null)
 * @method RencontreParis|null findOneBy(array $criteria, array $orderBy = null)
 * @method RencontreParis[]    findAll()
 * @method RencontreParis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RencontreParisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RencontreParis::class);
    }

    /**
     * @param $compos
     * @return array
     */
    public function getSelectedPlayers($compos){
        $selectedPlayers = [];
        foreach ($compos as $compo){
            if ($compo->getIdJoueur1() != null) array_push($selectedPlayers, $compo->getIdJoueur1()->getIdCompetiteur());
            if ($compo->getIdJoueur2() != null) array_push($selectedPlayers, $compo->getIdJoueur2()->getIdCompetiteur());
            if ($compo->getIdJoueur3() != null) array_push($selectedPlayers, $compo->getIdJoueur3()->getIdCompetiteur());
            if ($compo->getIdJoueur4() != null) array_push($selectedPlayers, $compo->getIdJoueur4()->getIdCompetiteur());
            if ($compo->getIdJoueur5() != null) array_push($selectedPlayers, $compo->getIdJoueur5()->getIdCompetiteur());
            if ($compo->getIdJoueur6() != null) array_push($selectedPlayers, $compo->getIdJoueur6()->getIdCompetiteur());
            if ($compo->getIdJoueur7() != null) array_push($selectedPlayers, $compo->getIdJoueur7()->getIdCompetiteur());
            if ($compo->getIdJoueur8() != null) array_push($selectedPlayers, $compo->getIdJoueur8()->getIdCompetiteur());
            if ($compo->getIdJoueur9() != null) array_push($selectedPlayers, $compo->getIdJoueur9()->getIdCompetiteur());
        }
        return $selectedPlayers;
    }

    /**
     * @return int|mixed|string
     */
    public function getOrderedRencontres(){
        return $this->createQueryBuilder('c')
            ->leftJoin('c.idJournee', 'j')
            ->orderBy('j.date')
            ->addOrderBy('c.idJournee')
            ->addOrderBy('c.idEquipe')
            ->getQuery()
            ->getResult();
    }
}
