<?php

namespace App\Repository;

use App\Entity\Competiteur;
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
            ->where("c.idCompetiteur NOT IN (SELECT DISTINCT IDENTITY(d.idCompetiteur) FROM App\Entity\Disponibilite" . ucfirst($type) . " d WHERE d.idJournee = :idJournee)")
            ->setParameter('idJournee', $idJournee)
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
     * @param $idEquipes
     * @param int $nbJoueurs
     * @return int|mixed|string
     */
    public function getBrulages(string $type, int $idJournee, array $idEquipes, int $nbJoueurs){
        $brulages = $this->createQueryBuilder('c')
            ->select('c.nom');
        foreach ($idEquipes as $idEquipe) {
            $str = '';
            for ($i = 0; $i < $nbJoueurs; $i++) {
                $str .= 'p' . $idEquipe . '.idJoueur' . $i . ' = c.idCompetiteur';
                if ($i < $nbJoueurs - 1) $str .= ' OR ';
            }
            $brulages = $brulages->addSelect('(SELECT COUNT(p' . $idEquipe . '.id) FROM App\Entity\Rencontre' . ucfirst($type) . ' p' . $idEquipe . ', App\Entity\Equipe' . ucfirst($type) . ' e' . $idEquipe . ' WHERE (' . $str . ') AND p' . $idEquipe . '.idJournee < :idJournee AND e' . $idEquipe . '.idEquipe = p' . $idEquipe . '.idEquipe AND e' . $idEquipe . '.numero = ' . $idEquipe . ' AND e' . $idEquipe . '.idDivision IS NOT NULL) AS E' . $idEquipe);
        }
        $brulages = $brulages
            ->addSelect('c.idCompetiteur')
            ->where('c.visitor <> true')
            ->setParameter('idJournee', $idJournee)
            ->addOrderBy('c.nom')
            ->getQuery()
            ->getResult();

        $allBrulage = [];
        foreach ($brulages as $brulage){
            $brulageJoueur = [];
            $brulageInt = [];
            foreach ($idEquipes as $idEquipe) {
                array_push($brulageInt, intval($brulage['E'.$idEquipe]));
            }
            $brulageJoueur['brulage'] = $brulageInt;
            $brulageJoueur['idCompetiteur'] = $brulage['idCompetiteur'];
            $allBrulage[$brulage['nom']] = $brulageJoueur;
        }

        return $allBrulage;
    }

    /**
     * // TODO à faire
     * Brûlage des joueurs sélectionnables dans une compo
     * @param string $type
     * @param int $idEquipe
     * @param int $idJournee
     * @param array $idEquipes
     * @param int $nbJoueurs
     * @param int $limiteBrulage
     * @return array
     */
    public function getBrulagesSelectionnables(string $type, int $idEquipe, int $idJournee, array $idEquipes, int $nbJoueurs, int $limiteBrulage): array
    {
        $brulages = $this->createQueryBuilder('c')
            ->select('c.nom')
            ->addSelect('c.idCompetiteur')
            ->leftJoin('c.dispos' . ucfirst($type) . 's', 'd');
        foreach ($idEquipes as $equipe) {
            $strB = '';
            for ($i = 0; $i < $nbJoueurs; $i++) {
                $strB .= 'r' . $equipe . '.idJoueur' . $i . ' = c.idCompetiteur';
                if ($i < $nbJoueurs - 1) $strB .= ' OR ';
            }
            $brulages = $brulages->addSelect('(SELECT COUNT(r' . $equipe . '.id)' .
                                                   ' FROM App\Entity\Rencontre' . ucfirst($type) . ' r' . $equipe . ', App\Entity\Equipe' . ucfirst($type) . ' e' . $equipe .
                                                   ' WHERE (' . $strB . ') AND r' . $equipe . '.idJournee < :idJournee' .
                                                   ' AND e' . $equipe . '.idEquipe = r' . $equipe . '.idEquipe' .
                                                   ' AND e' . $equipe . '.numero = ' . $equipe .
                                                   ' AND e' . $equipe . '.idDivision IS NOT NULL) AS E' . $equipe);
        }
        $brulages = $brulages
            ->where('c.visitor <> true');
        $strD = '';
        for ($j = 0; $j < $nbJoueurs; $j++) {
            $strD .= 'p.idJoueur' . $j . ' = c.idCompetiteur';
            if ($j < $nbJoueurs - 1) $strD .= ' OR ';
            $brulages = $brulages->andWhere('c.idCompetiteur NOT IN (SELECT IF(p' . $j . '.idJoueur' . $j . ' IS NOT NULL, p' . $j . '.idJoueur' . $j . ', 0)' .
                                                                   ' FROM App\Entity\RencontreDepartementale p' . $j .
                                                                   ' WHERE p' . $j . '.idJournee = d.idJournee' .
                                                                   ' AND p' . $j . '.idEquipe <> :idEquipe)');
        }
        $brulages = $brulages
            ->andWhere('(SELECT COUNT(p.id) FROM App\Entity\RencontreDepartementale p' .
                       ' WHERE (' . $strD . ')' .
                       ' AND p.idJournee < :idJournee' .
                       ' AND p.idEquipe < :idEquipe) < ' . $limiteBrulage)
            ->andWhere('d.idJournee = :idJournee')
            ->andWhere('d.disponibilite = 1')
            ->setParameter('idJournee', $idJournee)
            ->setParameter('idEquipe', $idEquipe)
            ->addOrderBy('c.nom')
            ->getQuery()
            ->getResult();

        $allBrulage = [];
        foreach ($brulages as $brulage){
            $brulageJoueur = [];
            $brulageInt = [];
            foreach ($idEquipes as $equipe) {
                array_push($brulageInt, intval($brulage['E'.$equipe]));
            }
            $brulageJoueur['brulage'] = $brulageInt;
            $brulageJoueur['idCompetiteur'] = $brulage['idCompetiteur'];
            $allBrulage[$brulage['nom']] = $brulageJoueur;
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