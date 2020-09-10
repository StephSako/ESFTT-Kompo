<?php

namespace App\Repository;

use App\Entity\RencontreDepartementale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RencontreDepartementale|null find($id, $lockMode = null, $lockVersion = null)
 * @method RencontreDepartementale|null findOneBy(array $criteria, array $orderBy = null)
 * @method RencontreDepartementale[]    findAll()
 * @method RencontreDepartementale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RencontreDepartementaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RencontreDepartementale::class);
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

    /**
     * @param $idJournee
     * @param $idEquipe
     * @param $idCompetiteur
     * @return int|mixed|string
     */
    public function getSelectedWhenBurnt($idJournee, $idEquipe, $idCompetiteur){
        $query = $this->createQueryBuilder('rd')
            ->select('GROUP_CONCAT(rd.id) as id_compos')
            ->from('App:Competiteur', 'c')
            ->where('rd.idJournee >= (:idJournee + 1)')
            ->andWhere('rd.idJournee <= 6')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('rd.idEquipe > :idEquipe')
            ->setParameter('idEquipe', $idEquipe)
            ->andWhere('c.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->andWhere('rd.idJoueur1 = c.idCompetiteur OR rd.idJoueur2 = c.idCompetiteur OR rd.idJoueur3 = c.idCompetiteur OR rd.idJoueur4 = c.idCompetiteur');

        switch ($idEquipe) {
            case 1:
                $query
                    ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') >= 2");
                break;
            case 2:
                $query
                    ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') >= 2 OR JSON_VALUE(c.brulageDepartemental, '$.2') >= 2");
                break;
            case 3:
                $query
                    ->andWhere("JSON_VALUE(c.brulageDepartemental, '$.1') >= 2 OR JSON_VALUE(c.brulageDepartemental, '$.2') >= 2 OR JSON_VALUE(c.brulageDepartemental, '$.3') >= 2");
                break;
        }

        return $query->getQuery()->getResult();
    }
}
