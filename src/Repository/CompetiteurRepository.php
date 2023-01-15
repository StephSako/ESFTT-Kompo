<?php

namespace App\Repository;

use App\Entity\Championnat;
use App\Entity\Competiteur;
use App\Entity\Rencontre;
use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

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
     * @param int $idChampionnat
     * @param int $nbJoueurs
     * @return array
     */
    public function findDisposJoueurs(int $idJournee, int $idChampionnat, int $nbJoueurs): array
    {
        $strMJ = '';
        for ($j = 0; $j < $nbJoueurs; $j++) {
            $strMJ .= 'r.idJoueur' . $j . ' = c.idCompetiteur';
            if ($j < $nbJoueurs - 1) $strMJ .= ' OR ';
        }

        $query = $this->createQueryBuilder('c')
            ->select('c')
            ->addSelect('(
                    SELECT COUNT(r.id)
                    from App\Entity\Rencontre r, App\Entity\Equipe e
                    where (' . $strMJ . ')
                    AND r.idEquipe = e.idEquipe
                    AND e.idDivision IS NOT NULL
                    AND r.idChampionnat = :idChampionnat
                ) AS nbMatchesJoues')
            ->addSelect('(
                    SELECT d.disponibilite
                    FROM App\Entity\Disponibilite d
                    WHERE d.idJournee = :idJournee
                    AND d.idChampionnat = :idChampionnat
                    AND c.idCompetiteur = d.idCompetiteur
                ) as disponibilite')
            ->addSelect('(
                    SELECT et.numero
                    FROM App\Entity\Titularisation t, App\Entity\Equipe et
                    WHERE t.idChampionnat = :idChampionnat
                    AND c.idCompetiteur = t.idCompetiteur
                    AND t.idEquipe = et.idEquipe
                ) as numero')
            ->setParameter('idJournee', $idJournee)
            ->setParameter('idChampionnat', $idChampionnat)
            ->where('c.isCompetiteur = true')
            ->orderBy('numero')
            ->addOrderBy('c.classement_officiel', 'DESC')
            ->addOrderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();

        $disposFormatted = array_map(function($dispo){
            $dispo['joueur'] = $dispo['0'];
            unset($dispo['0']);
            return $dispo;
        }, $query);

        $disposParEquipe = [];
        foreach($disposFormatted as $dispo) {
            $labelEquipe = $dispo['numero'] ? 'Équipe n°' . $dispo['numero'] : 'Sans équipe';
            $disposParEquipe[$labelEquipe][$dispo['joueur']->getNom() . ' ' . $dispo['joueur']->getPrenom()] = $dispo;
            unset($disposParEquipe[$labelEquipe][$dispo['joueur']->getNom() . ' ' . $dispo['joueur']->getPrenom()]['numero']);
        }

        return $disposParEquipe;
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
            ->addSelect('c.licence')
            ->addSelect('c.prenom');

        foreach ($championnats as $championnat){
            $strDispos = '';
            $journees = $championnat->getJournees()->toArray();
            foreach ($journees as $i => $journee) {
                $suffixe = $journee->getIdJournee() . $championnat->getIdChampionnat();
                $strDispos .= 'IFNULL((SELECT dt' . $suffixe . '.disponibilite' .
                              ' FROM App\Entity\Disponibilite dt' . $suffixe .
                              ' WHERE dt' . $suffixe . '.idChampionnat = ' . $championnat->getIdChampionnat() .
                              ' AND c.idCompetiteur = dt' . $suffixe . '.idCompetiteur' .
                              ' AND dt' . $suffixe . '.idJournee = ' . $journee->getIdJournee() . '), -1)';
                if ($i < count($journees)-1) $strDispos .= ", ',' , ";
            }
            $result = $result
                ->addSelect('CONCAT(' . $strDispos . ') AS ' . $championnat->getSlug())
                ->addSelect('(
                    SELECT et' . $championnat->getIdChampionnat() . '.numero
                    FROM App\Entity\Titularisation t' . $championnat->getIdChampionnat() . ', App\Entity\Equipe et' . $championnat->getIdChampionnat() . '
                    WHERE c.idCompetiteur = t' . $championnat->getIdChampionnat() . '.idCompetiteur
                    AND t' . $championnat->getIdChampionnat() . '.idChampionnat = ' . $championnat->getIdChampionnat() . '
                    AND t' . $championnat->getIdChampionnat() . '.idEquipe = et' . $championnat->getIdChampionnat() . '.idEquipe
                ) as numero' . $championnat->getSlug());
        }

        $result = $result
            ->where('c.isCompetiteur = true')
            ->orderBy('c.classement_officiel', 'DESC')
            ->addOrderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();

        $queryResult = $queryChamp = [];

        foreach ($championnats as $championnat) {
            foreach ($result as $key => $item) {
                $queryResult[$championnat->getSlug()][$key] = $item;
                $queryChamp[$championnat->getNom()]["nbJournees"] = $championnat->getNbJournees();
                $queryChamp[$championnat->getNom()]["dispos"] = $queryResult[$championnat->getSlug()];
            }
        }

        foreach ($queryChamp as $nomChamp => $championnat) {
            $disposTemp = $championnat["dispos"];
            unset($queryChamp[$nomChamp]["dispos"]);
            foreach($disposTemp as $dispo) {
                $labelEquipe = $dispo['numero' . (new Slugify())->slugify($nomChamp)] ? 'Équipe n°' . $dispo['numero' . (new Slugify())->slugify($nomChamp)] : 'Sans équipe';
                $queryChamp[$nomChamp]["dispos"][$labelEquipe][] = $dispo;
            }
        }
        return $queryChamp;
    }

    /**
     * Liste du brûlage
     * @param int $idChampionnat
     * @param int $idJournee
     * @param array $numEquipes
     * @param int $nbJoueurs
     * @return array
     */
    public function getBrulages(int $idChampionnat, int $idJournee, array $numEquipes, int $nbJoueurs): array
    {
        $brulages = $this->createQueryBuilder('c')
            ->select('c.nom')
            ->addSelect('c.prenom')
            ->addSelect('(
                    SELECT et.numero
                    FROM App\Entity\Titularisation t, App\Entity\Equipe et
                    WHERE t.idChampionnat = :idChampionnat
                    AND c.idCompetiteur = t.idCompetiteur
                    AND t.idEquipe = et.idEquipe
                ) as numero')
            ->addSelect('(
                    SELECT et2.idEquipe
                    FROM App\Entity\Titularisation t2, App\Entity\Equipe et2
                    WHERE t2.idChampionnat = :idChampionnat
                    AND c.idCompetiteur = t2.idCompetiteur
                    AND t2.idEquipe = et2.idEquipe
                ) as idEquipeAssociee');

        foreach ($numEquipes as $numEquipe) {
            $str = '';
            for ($i = 0; $i < $nbJoueurs; $i++) {
                $str .= 'p' . $numEquipe . '.idJoueur' . $i . ' = c.idCompetiteur';
                if ($i < $nbJoueurs - 1) $str .= ' OR ';
            }
            $brulages = $brulages
                ->addSelect('(SELECT COUNT(p' . $numEquipe . '.id) ' .
                                  'FROM App\Entity\Rencontre p' . $numEquipe . ', ' .
                                  'App\Entity\Equipe e' . $numEquipe . ' ' .
                                  'WHERE p' . $numEquipe . '.idChampionnat = :idChampionnat ' .
                                  'AND e' . $numEquipe . '.idChampionnat = :idChampionnat ' .
                                  'AND (' . $str . ') AND p' . $numEquipe . '.idJournee < :idJournee ' .
                                  'AND e' . $numEquipe . '.idEquipe = p' . $numEquipe . '.idEquipe ' .
                                  'AND e' . $numEquipe . '.numero = ' . $numEquipe . ' ' .
                                  'AND e' . $numEquipe . '.idDivision IS NOT NULL) AS E' . $numEquipe)
                ->setParameter('idJournee', $idJournee);
        }
        $brulages = $brulages
            ->addSelect('c.idCompetiteur')
            ->where('c.isCompetiteur = true')
            ->setParameter('idChampionnat', $idChampionnat)
            ->orderBy('numero')
            ->addOrderBy('c.classement_officiel', 'DESC')
            ->addOrderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();

        $allBrulage = [];
        foreach ($brulages as $brulage){
            $brulageJoueur = [];
            $brulageInt = [];
            foreach ($numEquipes as $numEquipe) {
                $brulageInt[$numEquipe] = intval($brulage['E'.$numEquipe]);
            }
            $brulageJoueur['brulage'] = $brulageInt;
            $brulageJoueur['numero'] = $brulage['numero'];
            $brulageJoueur['idEquipeAssociee'] = $brulage['idEquipeAssociee'];
            $brulageJoueur['idCompetiteur'] = $brulage['idCompetiteur'];
            $allBrulage[$brulage['nom'] . ' ' . $brulage['prenom']] = $brulageJoueur;
        }

        $brulagesParEquipe = [];
        foreach($allBrulage as $nomJoueur => $brulage) {
            $labelEquipe = $brulage['numero'] ? 'Équipe n°' . $brulage['numero'] : 'Sans équipe';
            $brulagesParEquipe[$brulage['idEquipeAssociee'] ?: 0]['joueurs'][$nomJoueur] = $brulage;
            $brulagesParEquipe[$brulage['idEquipeAssociee'] ?: 0]['nomEquipe'] = $labelEquipe;
            unset($brulagesParEquipe[$brulage['idEquipeAssociee'] ?: 0]['joueurs'][$nomJoueur]['idEquipeAssociee']);
            unset($brulagesParEquipe[$brulage['idEquipeAssociee'] ?: 0]['joueurs'][$nomJoueur]['numero']);
        }

        return $brulagesParEquipe;
    }

    /**
     * Brûlage des joueurs sélectionnables dans une composition d'équipe
     * @param Championnat $championnat
     * @param int $numero
     * @param int $idJournee
     * @param array $idEquipes
     * @param int $nbJoueurs
     * @param int $limiteBrulage
     * @return array
     */
    public function getBrulagesSelectionnables(Championnat $championnat, int $numero, int $idJournee, array $idEquipes, int $nbJoueurs, int $limiteBrulage): array
    {
        $journees = $championnat->getJournees()->toArray();
        $idFirstJournee = $journees[0]->getIdJournee();
        $j2Condition = (count($journees) >= 2 && $journees[1]->getIdJournee() == $idJournee) && $championnat->isJ2Rule();
        if ($j2Condition) $strJ2 = '';
        $strD = '';

        for ($j = 0; $j < $nbJoueurs; $j++) {
            if ($j2Condition) $strJ2 .= 'r.idJoueur' . $j . ' = c.idCompetiteur';
            $strD .= 'p.idJoueur' . $j . ' = c.idCompetiteur';
            if ($j < $nbJoueurs - 1){
                $strD .= ' OR ';
                if ($j2Condition) $strJ2 .= ' OR ';
            }
        }

        $brulages = $this->createQueryBuilder('c')
            ->select('c.nom')
            ->addSelect('c.prenom')
            ->addSelect('c.idCompetiteur')
            ->addSelect('(
                    SELECT et.numero
                    FROM App\Entity\Titularisation t, App\Entity\Equipe et
                    WHERE t.idChampionnat = :idChampionnat
                    AND c.idCompetiteur = t.idCompetiteur
                    AND t.idEquipe = et.idEquipe
                ) as numero');

        if ($j2Condition){
            $brulages = $brulages->addSelect('(SELECT COUNT(r.id)' .
                ' FROM App\Entity\Rencontre r, App\Entity\Equipe e' .
                ' WHERE e.idDivision IS NOT NULL' .
                ' AND e.numero < :numero' .
                ' AND e.idChampionnat = :idChampionnat' .
                ' AND r.idChampionnat = e.idChampionnat' .
                ' AND r.idJournee = ' . $idFirstJournee .
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
                                  ' AND r' . $equipe . '.idChampionnat = :idChampionnat' .
                                  ' AND e' . $equipe . '.idDivision IS NOT NULL) AS E' . $equipe);
        }
        $brulages = $brulages
            ->leftJoin('c.dispos', 'd')
            ->where('d.idChampionnat = :idChampionnat')
            ->andWhere('c.isCompetiteur = true')
            ->andWhere('d.idJournee = :idJournee')
            ->andWhere('d.disponibilite = 1');
        for ($j = 0; $j < $nbJoueurs; $j++) {
            $brulages = $brulages
                ->andWhere('c.idCompetiteur NOT IN (SELECT IF(p' . $j . '.idJoueur' . $j . ' IS NOT NULL, p' . $j . '.idJoueur' . $j . ', 0)' .
                                                  ' FROM App\Entity\Rencontre p' . $j . ', App\Entity\Equipe e' . $j .'e' .
                                                  ' WHERE p' . $j . '.idJournee = d.idJournee' .
                                                  ' AND p' . $j . '.idEquipe = e' . $j .'e.idEquipe'.
                                                  ' AND e' . $j .'e.numero <> :numero'.
                                                  ' AND p' . $j . '.idChampionnat = :idChampionnat)');
        }
        $brulages = $brulages
            ->andWhere('(SELECT COUNT(p.id) FROM App\Entity\Rencontre p, App\Entity\Equipe eBis' .
                       ' WHERE (' . $strD . ')' .
                       ' AND p.idJournee < :idJournee' .
                       ' AND p.idChampionnat = :idChampionnat' .
                       ' AND p.idEquipe = eBis.idEquipe' .
                       ' AND eBis.numero < :numero) < ' . $limiteBrulage)
            ->setParameter('idJournee', $idJournee)
            ->setParameter('numero', $numero)
            ->setParameter('idChampionnat', $championnat->getIdChampionnat())
            ->orderBy('numero')
            ->addOrderBy('c.classement_officiel', 'DESC')
            ->addOrderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();

        $allBrulage = [];
        foreach ($brulages as $brulage){
            /** On formate en associant le joueur à son brûlage par équipe */
            $brulageJoueur = [];
            $brulageInt = [];
            foreach ($idEquipes as $equipe) {
                $brulageInt[] = intval($brulage['E' . $equipe]);
            }
            $brulageJoueur['brulage'] = $brulageInt;
            $brulageJoueur['idCompetiteur'] = $brulage['idCompetiteur'];
            $nom = $brulage['nom'] . ' ' . $brulage['prenom'];

            $allBrulage[$nom] = $brulageJoueur;
            $allBrulage[$nom]['bruleJ2'] = (array_key_exists('bruleJ2', $brulage) && $brulage['bruleJ2']);
            $allBrulage[$nom]['numero'] = $brulage['numero'];

            /** On effectue le brûlage prévisionnel **/
            if (in_array($numero - 1, array_keys($allBrulage[$nom]['brulage'])))
                $allBrulage[$nom]['brulage'][$numero - 1]++;
        }

        $brulagesSelectionnablesParEquipe = [];
        foreach($allBrulage as $nomJoueur => $brulage) {
            $labelEquipe = $brulage['numero'] ? 'Équipe n°' . $brulage['numero'] : 'Sans équipe';
            $brulagesSelectionnablesParEquipe[$labelEquipe][$nomJoueur] = $brulage;
            unset($brulagesSelectionnablesParEquipe[$labelEquipe][$nomJoueur]['numero']);
        }

        return $brulagesSelectionnablesParEquipe;
    }

    /**
     * Joueurs brûlés pour une rencontre
     * @param int $numero
     * @param int $idJournee
     * @param int $idChampionnat
     * @param int $nbJoueurs
     * @param int $limiteBrulage
     * @return array
     */
    public function getBrulesDansEquipe(int $numero, int $idJournee, int $idChampionnat, int $nbJoueurs, int $limiteBrulage): array
    {
        $str = '';
        for ($j = 0; $j < $nbJoueurs; $j++) {
            $str .= 'r.idJoueur' . $j . ' = c.idCompetiteur';
            if ($j < $nbJoueurs - 1) $str .= ' OR ';
        }
        $query = $this->createQueryBuilder('c')
            ->select('c.nom')
            ->addSelect('c.prenom')
            ->addSelect('(
                    SELECT et.numero
                    FROM App\Entity\Titularisation t, App\Entity\Equipe et
                    WHERE t.idChampionnat = :idChampionnat
                    AND c.idCompetiteur = t.idCompetiteur
                    AND t.idEquipe = et.idEquipe
                ) as numero')
            ->where('c.isCompetiteur = true')
            ->andWhere('(SELECT COUNT(r.id) ' .
                       ' FROM App\Entity\Rencontre r, App\Entity\Equipe e' .
                       ' WHERE e.idDivision IS NOT NULL' .
                       ' AND e.numero < :numero' .
                       ' AND e.idChampionnat = :idChampionnat' .
                       ' AND r.idChampionnat = :idChampionnat' .
                       ' AND r.idJournee < :idJournee' .
	                   ' AND r.idEquipe = e.idEquipe' .
                       ' AND (' . $str . ')) >= ' . $limiteBrulage)
            ->setParameter('idJournee', $idJournee)
            ->setParameter('numero', $numero)
            ->setParameter('idChampionnat', $idChampionnat)
            ->orderBy('numero')
            ->addOrderBy('c.classement_officiel', 'DESC')
            ->addOrderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();

        $joueursBrules = [];
        foreach($query as $joueur) {
            $labelEquipe = $joueur['numero'] ? 'Équipe n°' . $joueur['numero'] : 'Sans équipe';
            $joueursBrules[$labelEquipe][] = $joueur['nom'] . ' ' . $joueur['prenom'];
        }
        return $joueursBrules;
    }

    /**
     * Liste des disponibilités de tous les joueurs
     * @param Championnat[] $allChampionnats
     * @return array
     * @throws Exception
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
            ->addSelect('champ.idChampionnat')
            ->addSelect('j.undefined')
            ->addSelect('(SELECT d1.idDisponibilite FROM App\Entity\Disponibilite d1 ' .
                              'WHERE c.idCompetiteur = d1.idCompetiteur ' .
                              'AND d1.idJournee = j.idJournee) AS idDisponibilite')
            ->addSelect('(SELECT d2.disponibilite FROM App\Entity\Disponibilite d2 ' .
                              'WHERE c.idCompetiteur = d2.idCompetiteur ' .
                              'AND d2.idJournee = j.idJournee) AS disponibilite')
            ->addSelect('j.dateJournee')
            ->addSelect('(SELECT MAX(pr.dateReport) FROM App\Entity\Rencontre pr ' .
                'WHERE pr.idJournee = j.idJournee) as latestDate')
            ->from('App:Journee', 'j')
            ->leftJoin('j.idChampionnat', 'champ')
            ->where('c.isCompetiteur = true')
            ->orderBy('c.nom', 'ASC')
            ->addOrderBy('c.prenom', 'ASC')
            ->addOrderBy('j.idJournee', 'ASC')
            ->getQuery()
            ->getResult();

        $queryTest = $queryFinal = [];
        foreach ($query as $key => $item) {
            $queryTest[$item['nomChamp']][$key] = $item;
        }

        foreach ($allChampionnats as $championnat) {
            $queryFinal[$championnat->getNom()] = [];
            foreach ($queryTest[$championnat->getNom()] as $item) {
                $nom = $item['nom'] . ' ' . $item['prenom'];
                if (!array_key_exists('idChampionnat', $queryFinal[$championnat->getNom()])) $queryFinal[$championnat->getNom()]['idChampionnat'] = $item['idChampionnat'];
                if (!array_key_exists('joueurs', $queryFinal[$championnat->getNom()])) $queryFinal[$championnat->getNom()]['joueurs'] = [];
                if (!array_key_exists($nom, $queryFinal[$championnat->getNom()]['joueurs'])){
                    $queryFinal[$championnat->getNom()]['joueurs'][$nom] = [];
                    $queryFinal[$championnat->getNom()]['joueurs'][$nom]['avatar'] = $item['avatar'];
                    $queryFinal[$championnat->getNom()]['joueurs'][$nom]['idCompetiteur'] = $item['idCompetiteur'];
                    $queryFinal[$championnat->getNom()]['joueurs'][$nom]['disponibilites'] = [];
                }
                $item['latestDate'] = max(new DateTime($item['latestDate']), $item['dateJournee']);
                $queryFinal[$championnat->getNom()]['joueurs'][$nom]['disponibilites'][] = $item;
            }
        }
        return $queryFinal;
    }

    /**
     * Indique si l'utilisateur existe pour reset son password
     * @param string $username
     * @param string $mail
     * @return array|null
     */
    public function findJoueurResetPassword(string $username, string $mail): ?Competiteur
    {
        $query = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.username = :username')
            ->andWhere('c.mail = :mail OR c.mail2 = :mail')
            ->setParameter('username', $username)
            ->setParameter('mail', $mail)
            ->getQuery()
            ->getResult();
        return $query[0] ?? null;
    }

    /**
     * Récupère tous les pseudos, noms et prénoms de tous les joueurs
     * @param boolean $justUsernames
     * @return array
     */
    public function findAllPseudos(bool $justUsernames): array
    {
        $query = $this->createQueryBuilder('c')
                ->select('c.username');
        if (!$justUsernames) {
            $query = $query
                ->addSelect('c.licence')
                ->addSelect('c.prenom')
                ->addSelect('c.nom');
        }
        $query = $query
            ->getQuery()
            ->getResult();

        $result = [];
        $result['usernames'] = array_map(function($pseudo) {
            return $pseudo['username'];
        }, $query);

        if (!$justUsernames) {
            $result['prenoms_noms'] = array_map(function($nomPrenom) {
                return $nomPrenom['prenom'] . $nomPrenom['nom'];
            }, $query);
            $result['licences'] = array_filter(array_map(function($licence) {
                return $licence['licence'];
            }, $query), function($licence) {
                return $licence;
            });
        }
        return $result;
    }

    /**
     * Retourne une liste de joueurs selon le rôle passé en paramètre
     * @param string|null $role Le paramètres correspond aux champs (boolean) dans la BDD
     * @param int|null $idRedacteur ID de l'user actif
     * @return int|mixed|string
     */
    public function findJoueursByRole(?string $role, ?int $idRedacteur)
    {
        $query = $this->createQueryBuilder('c')
            ->select('c');
        if ($idRedacteur) $query = $query->where('c.idCompetiteur <> :idRedacteur');

        if ($role) $query = $query->andWhere('c.is' . $role . ' = 1');
        else $query = $query->andWhere('c.isArchive <> 1');

        $query = $query
            ->orderBy('c.nom', 'ASC')
            ->addOrderBy('c.prenom', 'ASC');
        if ($idRedacteur) $query = $query->setParameter('idRedacteur', $idRedacteur);
        return $query
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la liste des joueurs sélectionnables pour la compo d'équipe avec opt groupe
     * @param int $nbMaxJoueurs
     * @param int $limiteBrulage
     * @param Rencontre $compo
     * @return array
     */
    public function getJoueursSelectionnablesOptGroup(int $nbMaxJoueurs, int $limiteBrulage, Rencontre $compo): array
    {
        $request = $this->createQueryBuilder('c')
            ->addSelect('(
                    SELECT et.numero
                    FROM App\Entity\Titularisation t, App\Entity\Equipe et
                    WHERE t.idChampionnat = :idChampionnat
                    AND c.idCompetiteur = t.idCompetiteur
                    AND t.idEquipe = et.idEquipe
                ) as numero')
            ->leftJoin('c.dispos', 'd')
            ->where('d.idJournee = :idJournee')
            ->andWhere('d.disponibilite = 1')
            ->andWhere('d.idChampionnat = :idChampionnat')
            ->andWhere('c.isCompetiteur = true');
        $str = '';
        for ($i = 0; $i < $nbMaxJoueurs; $i++) {
            $str .= 'p.idJoueur' . $i . ' = c.idCompetiteur';
            if ($i < $nbMaxJoueurs - 1) $str .= ' OR ';
            $request = $request
                ->andWhere('c.idCompetiteur NOT IN (SELECT IF(p' . $i . '.idJoueur' . $i . ' IS NOT NULL, p' . $i . '.idJoueur' . $i . ', 0) ' .
                    ' FROM App\Entity\Rencontre p' . $i . ', App\Entity\Equipe e' . $i .'e' .
                    ' WHERE p' . $i . '.idJournee = d.idJournee' .
                    ' AND p' . $i . '.idEquipe = e' . $i .'e.idEquipe'.
                    ' AND e' . $i .'e.numero <> :numero'.
                    ' AND p' . $i . '.idChampionnat = :idChampionnat)');
        }
        $request = $request
            ->andWhere('(SELECT COUNT(p.id) FROM App\Entity\Rencontre p, App\Entity\Equipe eBis' .
                ' WHERE (' . $str . ')' .
                ' AND p.idJournee < :idJournee' .
                ' AND p.idEquipe = eBis.idEquipe' .
                ' AND eBis.numero < :numero ' .
                ' AND p.idChampionnat = :idChampionnat) < ' . $limiteBrulage)
            ->setParameter('idJournee', $compo->getIdJournee()->getIdJournee())
            ->setParameter('numero', $compo->getIdEquipe()->getNumero())
            ->setParameter('idChampionnat', $compo->getIdChampionnat()->getIdChampionnat())
            ->orderBy('numero')
            ->addOrderBy('c.classement_officiel', 'DESC')
            ->addOrderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()->getResult();

        $brulagesParEquipe = [];
        foreach($request as $joueur) {
            $labelEquipe = $joueur['numero'] ? 'Équipe n°' . $joueur['numero'] : 'Sans équipe';
            $brulagesParEquipe[$labelEquipe][] = $joueur[0];
        }

        return $brulagesParEquipe;
    }
}