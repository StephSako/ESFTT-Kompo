<?php

namespace App\Repository;

use App\Entity\Division;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Division|null find($id, $lockMode = null, $lockVersion = null)
 * @method Division|null findOneBy(array $criteria, array $orderBy = null)
 * @method Division[]    findAll()
 * @method Division[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DivisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Division::class);
    }

    /**
     * Retourne le nombre maximal de joueurs de toutes les divisions
     * @return array
     * @throws NonUniqueResultException
     */
    public function getNbJoueursMax(): array
    {
        return $this->createQueryBuilder('d')
            ->select('MAX(d.nbJoueurs) as nbMaxJoueurs')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Select de divisions avec optGroups pour le form Equipe dans le back-office
     * @return array
     */
    public function getDivisionsOptgroup(): array
    {
        $data = $this->createQueryBuilder('d')
            ->addSelect('c')
            ->leftJoin('d.idChampionnat', 'c')
            ->orderBy('c.nom', 'ASC')
            ->addOrderBy('d.nbJoueurs', 'DESC')
            ->addOrderBy('d.longName', 'ASC')
            ->addOrderBy('d.shortName', 'ASC')
            ->getQuery()
            ->getResult();

        $querySorted = [];
        foreach ($data as $item) {
            if (!array_key_exists($item->getIdChampionnat()->getNom(), $querySorted)) $querySorted[$item->getIdChampionnat()->getNom()] = [];
            if ($item->getLongName()) $querySorted[$item->getIdChampionnat()->getNom()][$item->getLongName()] = $item;
        }
        return $querySorted;
    }

    /**
     * Retourne la liste formattée d'une liste de groupes d'organismes triées selon le type
     * LIBELLE => ID_ORGANISME
     * @param array $groupesOrganismes
     * @return array
     */
    public function getOrganismesFormatted(array $groupesOrganismes): array
    {
        $organismes = [];
        foreach ($groupesOrganismes as $nomGroupeOrganismes => $groupeOrganismes) {
            usort($groupeOrganismes, function ($orga1, $orga2) {
                return $orga1->getLibelle() > $orga2->getLibelle();
            });

            $organismesGroupe = [];
            foreach ($groupeOrganismes as $organisme) {
                $organismesGroupe[mb_convert_case($organisme->getLibelle(), MB_CASE_UPPER, "UTF-8")] = $organisme->getId();
            }
            $organismes[$nomGroupeOrganismes] = $organismesGroupe;
        }
        return $organismes;
    }

    /**
     * Retourne la liste formattée d'une liste d'organismes
     * ID_ORGANISME => LIBELLE
     * @param array $organismesRaw
     * @return array
     */
    public function getOrganismesBasic(array $organismesRaw): array
    {
        $organismes = [];
        foreach ($organismesRaw as $organisme) {
            $organismes[$organisme->getId()] = mb_convert_case($organisme->getLibelle(), MB_CASE_UPPER, "UTF-8");
        }
        return $organismes;
    }
}
