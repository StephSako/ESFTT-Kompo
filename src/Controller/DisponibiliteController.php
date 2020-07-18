<?php

namespace App\Controller;

use App\Entity\DisponibiliteDepartementale;
use App\Entity\DisponibiliteParis;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DisponibiliteController
 * @package App\Controller
 */
class DisponibiliteController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var JourneeParisRepository
     */
    private $journeeParisRepository;
    /**
     * @var JourneeDepartementaleRepository
     */
    private $journeeDepartementaleRepository;
    /**
     * @var DisponibiliteDepartementaleRepository
     */
    private $disponibiliteDepartementaleRepository;
    /**
     * @var DisponibiliteParisRepository
     */
    private $disponibiliteParisRepository;

    /**
     * @param EntityManagerInterface $em
     * @param JourneeParisRepository $journeeParisRepository
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     */
    public function __construct(EntityManagerInterface $em,
                                JourneeParisRepository $journeeParisRepository,
                                JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository)
    {
        $this->em = $em;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
    }

    /**
     * @Route("/journee/disponibilite/new/{journee}/{type}/{dispo}", name="journee.disponibilite.new")
     * @param $journee
     * @param string $type
     * @param int $dispo
     * @return Response
     */
    public function newDispoDepartementale($journee, $type, $dispo):Response
    {
        if ($type == 'departementale'){
            $journee = $this->journeeDepartementaleRepository->find($journee);
            $dispo = new DisponibiliteDepartementale($this->getUser(), $journee, $dispo);
        }
        else if ($type == 'paris'){
            $journee = $this->journeeParisRepository->find($journee);
            $dispo = new DisponibiliteParis($this->getUser(), $journee, $dispo);
        }

        $this->em->persist($dispo);
        $this->em->flush();

        return $this->redirectToRoute('journee.show',
            array(
                'type' => $type,
                'id' => $journee->getIdJournee()
            )
        );
    }

    /**
     * @Route("/journee/disponibilite/update/{journee}/{type}/{disposJoueur}/{dispo}/{NJournee}", name="journee.disponibilite.update")
     * @param string $type
     * @param $disposJoueur
     * @param bool $dispo
     * @param int $NJournee
     * @return Response
     */
    public function update($type, $disposJoueur, bool $dispo, $NJournee) : Response
    {
        if ($type == 'departementale'){
            $disposJoueur = $this->disponibiliteDepartementaleRepository->find($disposJoueur);
            $disposJoueur->setDisponibiliteDepartementale($dispo);
        }
        else if ($type == 'paris'){
            $disposJoueur = $this->disponibiliteParisRepository->find($disposJoueur);
            $disposJoueur->setDisponibiliteParis($dispo);
        }

        $this->em->persist($disposJoueur);
        $this->em->flush();

        return $this->redirectToRoute('journee.show',
            array(
                'type' => $type,
                'id' => $NJournee
            )
        );
    }
}
