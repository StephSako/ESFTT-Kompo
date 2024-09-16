<?php

namespace App\Repository;

use App\Entity\Rencontre;
use App\Entity\Titularisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Titularisation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Titularisation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Titularisation[]    findAll()
 * @method Titularisation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TitularisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Titularisation::class);
    }

    /**
     * Supprime toutes les titularisations d'un joueur devenant loisir ou archivé
     * @param int $idCompetiteur
     * @return int|mixed|string
     */
    public function setDeleteTitularisation(int $idCompetiteur)
    {
        return $this->createQueryBuilder('dd')
            ->delete('App\Entity\Titularisation', 'tt')
            ->where('tt.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->getQuery()
            ->execute();
    }

    public function getRencontresParTitularisation(array $journeesClassiquesChampionnat): array
    {
        $allTitularisations = array_map(function ($titularisation) {
            return array_map(function (Rencontre $rencontre) use ($titularisation) {
                return [
                    'undefined' => $rencontre->isUndefined(),
                    'dateRencontre' => $rencontre->getDateRencontre(),
                    'idJournee' => $rencontre->getIdJournee()->getIdJournee(),
                    'idChampionnat' => $rencontre->getIdChampionnat()->getIdChampionnat(),
                    'idCompetiteur' => $titularisation->getIdCompetiteur()->getIdCompetiteur()
                ];
            }, $titularisation->getIdEquipe()->getRencontres()->toArray());
        }, $this->findAll());
        return $allTitularisations;
    }
}
