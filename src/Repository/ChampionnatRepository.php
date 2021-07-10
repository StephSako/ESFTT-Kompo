<?php

namespace App\Repository;

use App\Entity\Championnat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Championnat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Championnat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Championnat[]    findAll()
 * @method Championnat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChampionnatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Championnat::class);
    }

    /**
     * Liste des championnats pour le select de la form des divisions dans le back-office
     * @return array
     */
    public function getAllChampionnats(): array
    {
        $data = $this->createQueryBuilder('c')
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();

        $querySorted = [];
        foreach ($data as $item) {
            $querySorted[$item->getNom()] = $item;
        }
        return $querySorted;
    }

    /**
     * Retourne le premier championnat
     * @return Championnat|string
     */
    public function getFirstChampionnatAvailable()
    {
        $firstChamp = $this->createQueryBuilder('c')
            ->setMaxResults(1)
            ->orderBy('c.nom')
            ->getQuery()
            ->getResult();

        return count($firstChamp) ? $firstChamp[0] : null ;
    }

    /**
     * @return array
     */
    public function getAllEquipes(): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('e.idEquipe')
            ->addSelect('e.numero')
            ->addSelect('p.poule')
            ->addSelect('d.shortName as divShortName')
            ->addSelect('c.nom')
            ->leftJoin('c.equipes', 'e')
            ->leftJoin('e.idDivision', 'd')
            ->leftJoin('e.idPoule', 'p')
            ->orderBy('c.nom')
            ->addOrderBy('e.numero')
            ->getQuery()
            ->getResult();

        $querySorted = [];
        foreach ($query as $key => $item) {
            if (!array_key_exists($item['nom'], $querySorted)) $querySorted[$item['nom']] = [];
            if ($item['numero']) $querySorted[$item['nom']][$key] = $item;
        }
        return $querySorted;
    }

    /**
     * @return array
     */
    public function getAllDivisions(): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('d.longName')
            ->addSelect('d.shortName')
            ->addSelect('COUNT(e) as nbEquipes')
            ->addSelect('c.nom')
            ->addSelect('d.nbJoueurs')
            ->addSelect('d.idDivision')
            ->addSelect('c.idChampionnat')
            ->leftJoin('c.divisions', 'd')
            ->leftJoin('d.equipes', 'e')
            ->groupBy('d.idDivision')
            ->orderBy('c.nom')
            ->addOrderBy('d.nbJoueurs', 'DESC')
            ->addOrderBy('d.longName')
            ->getQuery()
            ->getResult();

        $querySorted = [];
        foreach ($query as $key => $item) {
            if (!array_key_exists($item['nom'], $querySorted)) $querySorted[$item['nom']] = [];
            if ($item['longName']) $querySorted[$item['nom']][$key] = $item;
        }
        return $querySorted;
    }

    /**
     * Liste des rencontres dans le backoffice
     * @return array
     */
    public function getAllRencontres(): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('e.numero')
            ->addSelect('j.idJournee')
            ->addSelect('c.nom')
            ->addSelect('j.dateJournee')
            ->addSelect('j.undefined')
            ->addSelect('r.adversaire')
            ->addSelect('r.domicile')
            ->addSelect('r.hosted')
            ->addSelect('d.idDivision')
            ->addSelect('r.reporte')
            ->addSelect('r.exempt')
            ->addSelect('r.dateReport')
            ->addSelect('r.id')
            ->leftJoin('c.rencontres', 'r')
            ->leftJoin('r.idJournee', 'j')
            ->leftJoin('r.idEquipe', 'e')
            ->leftJoin('e.idDivision', 'd')
            ->orderBy('c.nom')
            ->addOrderBy('j.dateJournee')
            ->addOrderBy('r.idJournee')
            ->addOrderBy('e.numero')
            ->getQuery()
            ->getResult();

        $querySorted = [];
        foreach ($query as $key => $item) {
            if (!array_key_exists($item['nom'], $querySorted)) $querySorted[$item['nom']] = [];
            if ($item['id']) $querySorted[$item['nom']][$item['numero']][$key] = $item;
        }
        return $querySorted;
    }

    /**
     * Supprimer des données
     * @param string $table
     * @param int $idChampionnat
     * @return int|mixed|string
     */
    public function deleteData(string $table, int $idChampionnat)
    {
        return $this->createQueryBuilder('c')
            ->delete('App\Entity\\' . $table, 't')
            ->where('t.idChampionnat = :idChampionnat')
            ->setParameter('idChampionnat', $idChampionnat)
            ->getQuery()
            ->execute();
    }
}
