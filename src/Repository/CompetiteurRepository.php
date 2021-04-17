<?php

namespace App\Repository;

use App\Entity\Championnat;
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
     * @param int $type
     * @return array
     */
    public function findJoueursNonDeclares(int $idJournee, int $type): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.nom')
            ->addSelect('c.idCompetiteur')
            ->addSelect('c.prenom')
            ->where("c.idCompetiteur NOT IN (SELECT DISTINCT IDENTITY(d.idCompetiteur) FROM App\Entity\Disponibilite d WHERE d.idJournee = :idJournee AND d.idChampionnat = :idChampionnat)")
            ->setParameter('idJournee', $idJournee)
            ->setParameter('idChampionnat', $type)
            ->andWhere('c.visitor <> true')
            ->orderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récapitulatif de toutes les disponibilités dans la modale
     * @param Championnat[] $championnats
     * @return array
     */
    public function findAllDisposRecapitulatif(array $championnats): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.avatar')
            ->addSelect('c.nom')
            ->addSelect('c.prenom');

        foreach ($championnats as $championnat){
            $strDispos = '';
            foreach ($championnat->getJournees()->toArray() as $i => $journee) {
                $suffixe = $journee->getIdJournee() . $championnat->getIdChampionnat();
                $strDispos .= 'IFNULL((SELECT dt' . $suffixe . '.disponibilite' .
                              ' FROM App\Entity\Disponibilite dt' . $suffixe .
                              ' WHERE dt' . $suffixe . '.idChampionnat = ' . $championnat->getIdChampionnat() .
                              ' AND c.idCompetiteur = dt' . $suffixe . '.idCompetiteur' .
                              ' AND dt' . $suffixe . '.idJournee = ' . $journee->getIdJournee() . '), -1)';
                if ($i < count($championnat->getJournees()->toArray())-1) $strDispos .= ", ',' , ";
            }
            $result = $result
                ->addSelect('CONCAT(' . $strDispos . ') AS ' . $championnat->getSlug());
        }

        $result = $result
            ->where('c.visitor <> true')
            ->orderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();

        $queryResult = $queryChamp = [];
        foreach ($championnats as $championnat) {
            foreach ($result as $key => $item) {
                $queryResult[$championnat->getSlug()][$key] = $item;
                $queryChamp[$championnat->getNom()] = $queryResult[$championnat->getSlug()];
            }
        }
        return $queryChamp;
    }

    /**
     * Brûlages en championnat départemental
     * @param int $type
     * @param int $idJournee
     * @param array $idEquipes
     * @param int $nbJoueurs
     * @return array
     */
    public function getBrulages(int $type, int $idJournee, array $idEquipes, int $nbJoueurs): array
    {
        $brulages = $this->createQueryBuilder('c')
            ->select('c.nom')
            ->addSelect('c.prenom');
        foreach ($idEquipes as $idEquipe) {
            $str = '';
            for ($i = 0; $i < $nbJoueurs; $i++) {
                $str .= 'p' . $idEquipe . '.idJoueur' . $i . ' = c.idCompetiteur';
                if ($i < $nbJoueurs - 1) $str .= ' OR ';
            }
            $brulages = $brulages
                ->addSelect('(SELECT COUNT(p' . $idEquipe . '.id) ' .
                                  'FROM App\Entity\Rencontre p' . $idEquipe . ', ' .
                                  'App\Entity\Equipe e' . $idEquipe . ' ' .
                                  'WHERE p' . $idEquipe . '.idChampionnat = :idChampionnat ' .
                                  'AND e' . $idEquipe . '.idChampionnat = :idChampionnat ' .
                                  'AND (' . $str . ') AND p' . $idEquipe . '.idJournee < :idJournee ' .
                                  'AND e' . $idEquipe . '.idEquipe = p' . $idEquipe . '.idEquipe ' .
                                  'AND e' . $idEquipe . '.numero = ' . $idEquipe . ' ' .
                                  'AND e' . $idEquipe . '.idDivision IS NOT NULL) AS E' . $idEquipe)
                ->setParameter('idJournee', $idJournee)
                ->setParameter('idChampionnat', $type);
        }
        $brulages = $brulages
            ->addSelect('c.idCompetiteur')
            ->where('c.visitor <> true')
            ->orderBy('c.nom')
            ->addOrderBy('c.prenom')
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
            $allBrulage[$brulage['nom'] . ' ' . $brulage['prenom']] = $brulageJoueur;
        }

        return $allBrulage;
    }

    /**
     * Brûlage des joueurs sélectionnables dans une compo
     * @param Championnat $championnat
     * @param int $idEquipe
     * @param int $idJournee
     * @param array $idEquipes
     * @param int $nbJoueurs
     * @param int $limiteBrulage
     * @return array
     */
    public function getBrulagesSelectionnables(Championnat $championnat, int $idEquipe, int $idJournee, array $idEquipes, int $nbJoueurs, int $limiteBrulage): array
    {
        if ($idJournee == 2 && $championnat->isJ2Rule()) $strJ2 = '';
        $strD = '';
        for ($j = 0; $j < $nbJoueurs; $j++) {
            if ($idJournee == 2 && $championnat->isJ2Rule()) $strJ2 .= 'r.idJoueur' . $j . ' = c.idCompetiteur';
            $strD .= 'p.idJoueur' . $j . ' = c.idCompetiteur';
            if ($j < $nbJoueurs - 1){
                $strD .= ' OR ';
                if ($idJournee == 2 && $championnat->isJ2Rule()) $strJ2 .= ' OR ';
            }
        }

        $brulages = $this->createQueryBuilder('c')
            ->select('c.nom')
            ->addSelect('c.prenom')
            ->addSelect('c.idCompetiteur');

        if ($idJournee == 2 && $championnat->isJ2Rule()){
            $brulages = $brulages->addSelect('(SELECT COUNT(r.id)' .
                ' FROM App\Entity\Rencontre r, App\Entity\Equipe e' .
                ' WHERE e.idDivision IS NOT NULL' .
                ' AND e.numero < :idEquipe' .
                ' AND e.idChampionnat = :idChampionnat' .
                ' AND r.idChampionnat = :idChampionnat' .
                ' AND e.numero < :idEquipe' .
                ' AND r.idJournee = 1' .
                ' AND r.idEquipe = e.idEquipe' .
                ' AND (' . $strJ2 . ')) AS bruleJ2');
        }

        foreach ($idEquipes as $equipe) {
            $strB = '';
            for ($i = 0; $i < $nbJoueurs; $i++) {
                $strB .= 'r' . $equipe . '.idJoueur' . $i . ' = c.idCompetiteur';
                if ($i < $nbJoueurs - 1) $strB .= ' OR ';
            }
            $brulages = $brulages
                ->addSelect('(SELECT COUNT(r' . $equipe . '.id)' .
                                  ' FROM App\Entity\Rencontre r' . $equipe . ', App\Entity\Equipe e' . $equipe .
                                  ' WHERE (' . $strB . ') AND r' . $equipe . '.idJournee < :idJournee' .
                                  ' AND e' . $equipe . '.idEquipe = r' . $equipe . '.idEquipe' .
                                  ' AND e' . $equipe . '.numero = ' . $equipe .
                                  ' AND r' . $equipe . '.idChampionnat = ' . $championnat->getIdChampionnat() .
                                  ' AND e' . $equipe . '.idDivision IS NOT NULL) AS E' . $equipe);
        }
        $brulages = $brulages
            ->leftJoin('c.dispos', 'd')
            ->where('d.idChampionnat = :idChampionnat')
            ->andWhere('c.visitor <> true')
            ->andWhere('d.idJournee = :idJournee')
            ->andWhere('d.disponibilite = 1');
        for ($j = 0; $j < $nbJoueurs; $j++) {
            $brulages = $brulages
                ->andWhere('c.idCompetiteur NOT IN (SELECT IF(p' . $j . '.idJoueur' . $j . ' IS NOT NULL, p' . $j . '.idJoueur' . $j . ', 0)' .
                                                  ' FROM App\Entity\Rencontre p' . $j .
                                                  ' WHERE p' . $j . '.idJournee = d.idJournee' .
                                                  ' AND p' . $j . '.idEquipe <> :idEquipe'.
                                                  ' AND p' . $j . '.idChampionnat = :idChampionnat)');
        }
        $brulages = $brulages
            ->andWhere('(SELECT COUNT(p.id) FROM App\Entity\Rencontre p' .
                       ' WHERE (' . $strD . ')' .
                       ' AND p.idJournee < :idJournee' .
                       ' AND p.idChampionnat = :idChampionnat' .
                       ' AND p.idEquipe < :idEquipe) < ' . $limiteBrulage)
            ->setParameter('idJournee', $idJournee)
            ->setParameter('idEquipe', $idEquipe)
            ->setParameter('idChampionnat', $championnat->getIdChampionnat())
            ->orderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();

        $allBrulage = [];
        foreach ($brulages as $joueur => $brulage){
            /** On formate en associant le joueur à son brûlage par équipe */
            $brulageJoueur = [];
            $brulageInt = [];
            foreach ($idEquipes as $equipe) {
                array_push($brulageInt, intval($brulages[$joueur]['E'.$equipe]));
            }
            $brulageJoueur['brulage'] = $brulageInt;
            $brulageJoueur['idCompetiteur'] = $brulages[$joueur]['idCompetiteur'];
            $nom = $brulages[$joueur]['nom'] . ' ' . $brulages[$joueur]['prenom'];

            $allBrulage[$nom] = $brulageJoueur;
            $allBrulage[$nom]['bruleJ2'] = (array_key_exists('bruleJ2', $brulage) ? boolval($brulage['bruleJ2']) : false);

            /** On effectue le brûlage prévisionnel **/
            if (in_array($idEquipe - 1, array_keys($allBrulage[$nom]['brulage'])))
                $allBrulage[$nom]['brulage'][$idEquipe - 1]++;
        }

        return $allBrulage;
    }

    /**
     * Joueurs brûlés pour une rencontre
     * @param int $idEquipe
     * @param int $idJournee
     * @param int $type
     * @param int $nbJoueurs
     * @param int $limiteBrulage
     * @return array
     */
    public function getBrulesDansEquipe(int $idEquipe, int $idJournee, int $type, int $nbJoueurs, int $limiteBrulage): array
    {
        $str = '';
        for ($j = 0; $j < $nbJoueurs; $j++) {
            $str .= 'r.idJoueur' . $j . ' = c.idCompetiteur';
            if ($j < $nbJoueurs - 1) $str .= ' OR ';
        }
        $query = $this->createQueryBuilder('c')
            ->select('c.nom')
            ->addSelect('c.prenom')
            ->where('c.visitor <> true')
            ->andWhere('(SELECT COUNT(r.id) ' .
                       ' FROM App\Entity\Rencontre r, App\Entity\Equipe e' .
                       ' WHERE e.idDivision IS NOT NULL' .
                       ' AND e.numero < :idEquipe' .
                       ' AND e.idChampionnat = :idChampionnat' .
                       ' AND r.idChampionnat = :idChampionnat' .
                       ' AND r.idJournee < :idJournee' .
	                   ' AND r.idEquipe = e.idEquipe' .
                       ' AND (' . $str . ')) >= ' . $limiteBrulage)
            ->setParameter('idJournee', $idJournee)
            ->setParameter('idEquipe', $idEquipe)
            ->setParameter('idChampionnat', $type)
            ->orderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();

        $joueursBrules = [];
        foreach ($query as $joueur){
            array_push($joueursBrules, $joueur['nom'] . ' ' . $joueur['prenom']);
        }
        return $joueursBrules;
    }

    /**
     * Liste des disponibilités de tous les joueurs
     * @param Championnat[] $allChampionnats
     * @return array
     */
    public function findAllDisponibilites(array $allChampionnats): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c.avatar')
            ->addSelect('c.idCompetiteur')
            ->addSelect('c.nom')
            ->addSelect('c.prenom')
            ->addSelect('c.classement_officiel')
            ->addSelect('c.licence')
            ->addSelect('j.idJournee')
            ->addSelect('champ.nom AS nomChamp')
            ->addSelect('j.undefined')
            ->addSelect('(SELECT d1.idDisponibilite FROM App\Entity\Disponibilite d1 ' .
                              'WHERE c.idCompetiteur = d1.idCompetiteur ' .
                              'AND d1.idJournee = j.idJournee) AS idDisponibilite')
            ->addSelect('(SELECT d2.disponibilite FROM App\Entity\Disponibilite d2 ' .
                              'WHERE c.idCompetiteur = d2.idCompetiteur ' .
                              'AND d2.idJournee = j.idJournee) AS disponibilite')
            ->addSelect('j.dateJournee')
            ->from('App:Journee', 'j')
            ->leftJoin('j.idChampionnat', 'champ')
            ->where('c.visitor <> true')
            ->orderBy('c.nom', 'ASC')
            ->addOrderBy('c.prenom', 'ASC')
            ->addOrderBy('j.idJournee', 'ASC')
            ->getQuery()
            ->getResult();

        $queryTest = $queryFinal = [];
        foreach ($query as $key => $item) {
            $queryTest[$item['nomChamp']][$key] = $item;
            /*foreach ($queryTest as $key2 => $item2) {
                $queryFinal[$key2][$item['nom'] . ' ' . $item['prenom']] = $item2;
            }*/
        }
        dump($queryTest);

        $querySorted = [];
        foreach ($query as $key => $item) {
            $querySorted[$item['nom'] . ' ' . $item['prenom']][$key] = $item;
        }

        return $querySorted;
    }
}