<?php

namespace App\Controller\BackOffice;

use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use Doctrine\DBAL\DBALException;
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
     * @var CompetiteurRepository
     */
    private $competiteurRepository;

    /**
     * BackOfficeController constructor.
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                CompetiteurRepository $competiteurRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
        $this->competiteurRepository = $competiteurRepository;
    }

    /**
     * @Route("/backoffice/disponibilites", name="back_office.disponibilites")
     * @return Response
     * @throws DBALException
     */
    public function indexDisponibilites()
    {
        return $this->render('back_office/disponibilites/disponibilites.html.twig', [
            'disponibiliteDepartementales' => $this->competiteurRepository->findAllDispos("departementale"),
            'disponibiliteParis' => $this->competiteurRepository->findAllDispos("paris")
        ]);
    }

    /**
     * @Route("/backoffice/disponibilite/update/{journee}/{type}/{disposJoueur}/{dispo}", name="backoffice.journee.disponibilite.update")
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
