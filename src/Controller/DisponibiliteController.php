<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Entity\JourneeDepartementale;
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
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/journee/disponibilite/create/{journee}/{dispo}", name="journee.disponibilite.new")
     * @param JourneeDepartementale $journee
     * @param $dispo
     * @return Response
     */
    public function new(JourneeDepartementale $journee, $dispo):Response
    {
        $dispo = new Disponibilite($this->getUser(), $journee, $dispo);
        $this->em->persist($dispo);
        $this->em->flush();
        return $this->redirectToRoute('journee.show',
            array(
                'id' => $journee->getNJournee()
            )
        );
    }

    /**
     * @Route("/journee/disponibilite/update/{journee}/{disposJoueur}/{dispo}", name="journee.disponibilite.update")
     * @param JourneeDepartementale $journee
     * @param Disponibilite $disposJoueur
     * @param int $dispo
     * @return Response
     */
    public function edit(JourneeDepartementale $journee, Disponibilite $disposJoueur, $dispo) : Response
    {
        $disposJoueur->setDisponibilite($dispo);
        $this->em->persist($disposJoueur);
        $this->em->flush();

        return $this->redirectToRoute('journee.show',
            array(
                'id' => $journee->getNJournee()
            )
        );
    }
}
