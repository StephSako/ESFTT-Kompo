<?php

namespace App\Repository;

use App\Entity\Competiteur;
use App\Entity\EquipeDepartementale;
use App\Entity\EquipeParis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Competiteur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competiteur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competiteur[]    findAll()
 * @method Competiteur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetiteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competiteur::class);
    }

    /**
     * @param int $idJournee
     * @param string $type
     * @return mixed[]
     */
    public function findJoueursNonDeclares(int $idJournee, string $type): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.nom')
            ->addSelect('c.idCompetiteur')
            ->addSelect('c.nom')
            ->where("c.idCompetiteur NOT IN (SELECT DISTINCT IDENTITY(d.idCompetiteur) FROM App\Entity\Disponibilite" . ucfirst($type) . " d WHERE d.idJournee = " . $idJournee . ")")
            ->andWhere('c.visitor <> true')
            ->addOrderBy('c.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récapitulatif de toutes les disponibilités dans la modale
     * @param string $type
     * @param int $nbJournees
     * @return int|mixed|string
     */
    public function findAllDisposRecapitulatif(string $type, int $nbJournees){
        $result = $this->createQueryBuilder('c')
            ->select('c.avatar')
            ->addSelect('c.nom');

        for ($i = 1; $i <= $nbJournees; $i++) {
            $result = $result->addSelect("(SELECT dt" . $i . ".disponibilite FROM App\Entity\Disponibilite" . $type . " dt" . $i . " WHERE c.idCompetiteur = dt" . $i . ".idCompetiteur AND dt" . $i . ".idJournee = " . $i . ") AS j" . $i);
        }

        $result = $result->where('c.visitor <> true')
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Brûlages en championnat départemental
     * @param string $type
     * @param int $idJournee
     * @param EquipeDepartementale|EquipeParis $equipes
     * @param int $nbJoueurs
     * @return int|mixed|string
     */
    public function getBrulagesDepartemental(string $type, int $idJournee, $equipes, int $nbJoueurs){
        $brulages = $this->createQueryBuilder('c')
            ->select('c.nom');
        foreach ($equipes as $equipe) {
            $str = '';
            for ($i = 0; $i < $nbJoueurs; $i++) {
                $str .= 'p' . $equipe->getNumero() . '.idJoueur1 = c.idCompetiteur';
                if ($i < $nbJoueurs) $str .= ' OR ';
            }
            $brulages->addSelect('(SELECT COUNT(p' . $equipe->getNumero() . '.id) FROM App\Entity\Rencontre' . ucfirst($type) . ' p' . $equipe->getNumero() . ' WHERE (' . $str . ') AND p' . $equipe->getNumero() . '.idJournee < :idJournee AND p' . $equipe->getNumero() . '.idEquipe = 1) AS E' . $equipe->getNumero() . '');
        }
        $brulages
            ->addSelect('c.idCompetiteur')
            ->where('c.visitor <> true')
            ->setParameter('idJournee', $idJournee)
            ->addOrderBy('c.nom')
            ->getQuery()
            ->getResult();

        $allBrulage = [];
        foreach ($brulages as $brulage){
            $allBrulage[$brulage["nom"]] = ["E1" => $brulage["E1"], "E2"=>$brulage["E2"], "E3" => $brulage["E3"], "idCompetiteur" => $brulage["idCompetiteur"]];
        }

        return $allBrulage;
    }

    /**
     * Liste de toutes les disponibilités
     * @param string $type
     * @return int|mixed|string
     */
    public function findAllDisponibilites(string $type)
    {
        return $this->createQueryBuilder('c')
            ->select('c.avatar')
            ->addSelect('c.idCompetiteur')
            ->addSelect('c.nom')
            ->addSelect('c.classement_officiel')
            ->addSelect('c.licence')
            ->addSelect('j.idJournee')
            ->addSelect('j.undefined')
            ->addSelect("(SELECT d1.idDisponibilite FROM App\Entity\Disponibilite" . ucfirst($type) . " d1 WHERE c.idCompetiteur = d1.idCompetiteur AND d1.idJournee = j.idJournee) AS idDisponibilite")
            ->addSelect("(SELECT d2.disponibilite FROM App\Entity\Disponibilite" . ucfirst($type) . " d2 WHERE c.idCompetiteur = d2.idCompetiteur AND d2.idJournee = j.idJournee) AS disponibilite")
            ->addSelect('j.date')
            ->from('App:Journee' . ucfirst($type), 'j')
            ->where('c.visitor <> true')
            ->orderBy('c.nom')
            ->addOrderBy('j.idJournee')
            ->getQuery()
            ->getResult();
    }
}