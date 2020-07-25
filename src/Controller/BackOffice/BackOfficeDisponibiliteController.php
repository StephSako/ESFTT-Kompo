<?php

namespace App\Controller\BackOffice;

use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeDisponibiliteController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var DisponibiliteDepartementaleRepository
     */
    private $disponibiliteDepartementaleRepository;
    /**
     * @var DisponibiliteParisRepository
     */
    private $disponibiliteParisRepository;

    /**
     * BackOfficeController constructor.
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
    }

    /**
     * @Route("/backoffice/disponibilites", name="back_office.disponibilites")
     * @return Response
     */
    public function indexDisponibilites()
    {
        return $this->render('back_office/disponibilites/disponibilites.html.twig', [
            'disponibiliteDepartementales' => $this->disponibiliteDepartementaleRepository->findAllDispos(),
            'disponibiliteParis' => $this->disponibiliteParisRepository->findAllDispos(),
        ]);
    }

    /**
     * @Route("/journee/disponibilite/update/{journee}/{type}/{disposJoueur}/{dispo}", name="backoffice.journee.disponibilite.update")
     * @param string $type
     * @param $disposJoueur
     * @param bool $dispo
     * @param int $journee
     * @return Response
     */
    public function updateDisponibilites($type, $disposJoueur, bool $dispo, $journee) : Response
    {
        if ($type == 'departementale'){
            $disposJoueur = $this->disponibiliteDepartementaleRepository->find($disposJoueur);
            $disposJoueur->setDisponibiliteDepartementale($dispo);
        }
        else if ($type == 'paris'){
            $disposJoueur = $this->disponibiliteParisRepository->find($disposJoueur);
            $disposJoueur->setDisponibiliteParis($dispo);
        }

        $this->em->flush();
        $this->addFlash('success', 'Disponiblité modifiée avec succès !');

        return $this->redirectToRoute('back_office.disponibilites',
            array(
                'type' => $type,
                'id' => $journee
            )
        );
    }
}
