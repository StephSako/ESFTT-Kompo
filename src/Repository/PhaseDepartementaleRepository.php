<?php

namespace App\Repository;

use App\Entity\PhaseDepartementale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PhaseDepartementale|null find($id, $lockMode = null, $lockVersion = null)
 * @method PhaseDepartementale|null findOneBy(array $criteria, array $orderBy = null)
 * @method PhaseDepartementale[]    findAll()
 * @method PhaseDepartementale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhaseDepartementaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhaseDepartementale::class);
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
        }
        return $selectedPlayers;
    }
}
