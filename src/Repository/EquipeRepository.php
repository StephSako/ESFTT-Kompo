<?php

namespace App\Repository;

use App\Entity\Equipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Equipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Equipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Equipe[]    findAll()
 * @method Equipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipe::class);
    }

    /**
     * @param int $idDeletedDivision
     * @return int|mixed|string
     */
    public function setDeletedDivisionToNull(int $idDeletedDivision)
    {
        return $this->createQueryBuilder('e')
            ->update('App\Entity\Equipe', 'e')
            ->set('e.idDivision', 'NULL')
            ->where('e.idDivision = :idDeletedDivision')
            ->setParameter('idDeletedDivision', $idDeletedDivision)
            ->getQuery()
            ->execute();
    }

    /**
     * Select des équipes avec optGroups pour le form Competiteur dans le back-office
     * @return array
     */
    public function getEquipesOptgroup(): array
    {
        $data = $this->createQueryBuilder('e')
            ->addSelect('c')
            ->leftJoin('e.idChampionnat', 'c')
            ->orderBy('e.numero', 'ASC')
            ->getQuery()
            ->getResult();

        $querySorted = [];
        foreach ($data as $item) {
            if (!array_key_exists($item->getIdChampionnat()->getNom(), $querySorted)) $querySorted[$item->getIdChampionnat()->getNom()] = [];
            $querySorted[$item->getIdChampionnat()->getNom()]['idChampionnat'] = $item->getIdChampionnat();
            $querySorted[$item->getIdChampionnat()->getNom()]['listeEquipes']['Équipe ' . $item->getNumero()] = $item;
        }
        return $querySorted;
    }

    /**
     * Retourne toutes les équipes triées par championnat
     * @return array
     */
    public function getAllEquipes(): array
    {
        $query = $this->createQueryBuilder('e')
            ->leftJoin('e.idChampionnat', 'c')
            ->orderBy('c.nom')
            ->addOrderBy('e.numero')
            ->getQuery()
            ->getResult();
        $querySorted = [];
        foreach ($query as $equipe) {
            $querySorted[$equipe->getIdChampionnat()->getNom()]['idChampionnat'] = $equipe->getIdChampionnat()->getIdChampionnat();
            $querySorted[$equipe->getIdChampionnat()->getNom()]['equipes'][] = $equipe;
        }
        return $querySorted;
    }
}
