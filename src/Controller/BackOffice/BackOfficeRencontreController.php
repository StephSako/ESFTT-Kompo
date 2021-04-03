<?php

namespace App\Controller\BackOffice;

use App\Form\BackOfficeRencontreDepartementaleType;
use App\Form\BackOfficeRencontreParisType;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeRencontreController extends AbstractController
{
    private $em;
    private $rencontreDepartementaleRepository;
    private $rencontreParisRepository;

    /**
     * BackOfficeController constructor.
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param RencontreParisRepository $rencontreParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(RencontreParisRepository $rencontreParisRepository,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreParisRepository = $rencontreParisRepository;
        $this->rencontreDepartementaleRepository = $rencontreDepartementaleRepository;
    }

    /**
     * @Route("/backoffice/rencontres", name="backoffice.rencontres")
     * @return Response
     */
    public function indexRencontre(): Response
    {
        return $this->render('backoffice/rencontre/index.html.twig', [
            'rencontreDepartementales' => $this->rencontreDepartementaleRepository->getOrderedRencontres(),
            'rencontreParis' => $this->rencontreParisRepository->getOrderedRencontres()
        ]);
    }

    /**
     * @Route("/backoffice/rencontre/edit/{type}/{idRencontre}", name="backoffice.rencontre.edit")
     * @param $type
     * @param $idRencontre
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function editRencontre($type, $idRencontre, Request $request): Response
    {
        if ($type == 'departementale'){
            if (!($rencontre = $this->rencontreDepartementaleRepository->find($idRencontre))) throw new Exception('Cette rencontre est inexistante', 500);
            $form = $this->createForm(BackOfficeRencontreDepartementaleType::class, $rencontre);
        }
        else if ($type == 'paris'){
            if (!($rencontre = $this->rencontreParisRepository->find($idRencontre))) throw new Exception('Cette rencontre est inexistante', 500);
            $form = $this->createForm(BackOfficeRencontreParisType::class, $rencontre);
        }
        else throw new Exception('Ce championnat est inexistant', 500);

        $form->handleRequest($request);
        $domicile = ($rencontre->getDomicile() ? "D" : "E");

        if ($form->isSubmitted()){
            if ($form->isValid()){
                try {
                    /** On récupère la valeur du switch du template **/
                    $rencontre->setDomicile(($request->get('lieu_rencontre') == 'on' ? 0 : 1 ));

                    /** Si la rencontre n'est pas ou plus reportée, la date redevient celle de la journée associée **/
                    if (!$rencontre->isReporte()) $rencontre->setDateReport($rencontre->getIdJournee()->getDateJournee());

                    $rencontre->setAdversaire(ucwords(strtolower($rencontre->getAdversaire())));
                    $this->em->flush();
                    $this->addFlash('success', 'Rencontre modifiée avec succès !');
                    return $this->redirectToRoute('backoffice.rencontres');
                } catch(Exception $e){
                    if ($e->getPrevious()->getCode() == "23000") $this->addFlash('fail', 'L\'adversaire \'' . $rencontre->getAdversaire() . '\' est déjà attribué');
                    else $this->addFlash('fail', 'Une erreur est survenue');
                    return $this->render('backoffice/rencontre/edit.html.twig', [
                        'form' => $form->createView(),
                        'type' => $type,
                        'domicile' => $domicile,
                        'dateJournee' => $rencontre->getIdJournee()->getDateJournee(),
                        'idJournee' => $rencontre->getIdJournee()->getIdJournee(),
                        'idEquipe' => $rencontre->getIdEquipe()->getNumero()
                    ]);
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/rencontre/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'domicile' => $domicile,
            'dateJournee' => $rencontre->getIdJournee()->getDateJournee(),
            'idJournee' => $rencontre->getIdJournee()->getIdJournee(),
            'idEquipe' => $rencontre->getIdEquipe()->getNumero()
        ]);
    }
}
