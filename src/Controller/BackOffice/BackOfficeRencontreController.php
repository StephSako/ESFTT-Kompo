<?php

namespace App\Controller\BackOffice;

use App\Form\BackOfficeRencontreType;
use App\Repository\ChampionnatRepository;
use App\Repository\RencontreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeRencontreController extends AbstractController
{
    private $em;
    private $rencontreRepository;
    private $championnatRepository;

    /**
     * BackOfficeController constructor.
     * @param RencontreRepository $rencontreRepository
     * @param ChampionnatRepository $championnatRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(RencontreRepository $rencontreRepository,
                                ChampionnatRepository $championnatRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreRepository = $rencontreRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/backoffice/rencontres", name="backoffice.rencontres")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('backoffice/rencontre/index.html.twig', [
            'rencontres' => $this->championnatRepository->getAllRencontres()
        ]);
    }

    /**
     * @Route("/backoffice/rencontre/edit/{idRencontre}", name="backoffice.rencontre.edit", requirements={"idRencontre"="\d+"})
     * @param int $idRencontre
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(int $idRencontre, Request $request): Response
    {
        if (!($rencontre = $this->rencontreRepository->find($idRencontre))) {
            $this->addFlash('fail', 'Rencontre inexistante');
            return $this->redirectToRoute('backoffice.rencontres');
        }
        $form = $this->createForm(BackOfficeRencontreType::class, $rencontre);

        $form->handleRequest($request);
        $domicile = ($rencontre->getDomicile() ? "D" : "E");

        if ($form->isSubmitted()){
            if ($form->isValid()){
                try {
                    /** On récupère la valeur du switch du template **/
                    $rencontre->setDomicile(($request->get('lieu_rencontre') == 'on' ? 0 : 1 ));
                    $rencontre->setAdversaire(mb_convert_case($rencontre->getAdversaire(), MB_CASE_TITLE, "UTF-8"));

                    /** Si la rencontre n'est pas ou plus reportée, la date redevient celle de la journée associée **/
                    if ($rencontre->isReporte()) {
                        if ($rencontre->getDateReport() == $rencontre->getIdJournee()->getDateJournee()) throw new Exception('Renseignez une date de report différente de la date initiale', 12345);

                        /** On ne peut pas mélanger les dates */
                        $journeesBefore = array_filter($rencontre->getIdChampionnat()->getJournees()->toArray(), function ($rencChamp) use ($rencontre) {
                            return $rencChamp->getDateJournee() <= $rencontre->getDateReport() && $rencChamp->getDateJournee() != $rencontre->getIdJournee()->getDateJournee();
                        });
                        $journeesAfter = array_filter($rencontre->getIdChampionnat()->getJournees()->toArray(), function ($rencChamp) use ($rencontre) {
                            return $rencChamp->getDateJournee() >= $rencontre->getDateReport() && $rencChamp->getDateJournee() != $rencontre->getIdJournee()->getDateJournee();
                        });
                        $journeeBefore = $journeesBefore ? end($journeesBefore) : null;
                        $journeeAfter = $journeesAfter ? array_shift($journeesAfter) : null;

                        if ($journeeBefore && $journeeBefore->getIdJournee() > $rencontre->getIdJournee()->getIdJournee()) $this->addFlash('fail', 'La date de report ne peut pas être ultèrieure ou égale à la date de journées suivantes');
                        else if ($journeeAfter && $journeeAfter->getIdJournee() < $rencontre->getIdJournee()->getIdJournee()) $this->addFlash('fail', 'La date de report ne peut pas être postèrieure ou égale à la date de journées précédentes');
                        else {
                            $this->em->flush();
                            $this->addFlash('success', 'Rencontre modifiée');
                            return $this->redirectToRoute('backoffice.rencontres');
                        }
                    } else {
                        $rencontre->setDateReport($rencontre->getIdJournee()->getDateJournee());
                        $this->em->flush();
                        $this->addFlash('success', 'Rencontre modifiée');
                        return $this->redirectToRoute('backoffice.rencontres');
                    }
                } catch(Exception $e){
                    if ($e->getCode() == "12345"){
                        $this->addFlash('fail', $e->getMessage());
                    }
                    else if ($e->getPrevious()->getCode() == "23000"){
                        if (str_contains($e->getPrevious()->getMessage(), 'adversaire'))  $this->addFlash('fail', 'L\'adversaire \'' . $rencontre->getAdversaire() . '\' est déjà attribué');
                        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/rencontre/edit.html.twig', [
            'form' => $form->createView(),
            'domicile' => $domicile,
            'rencontre' => $rencontre
        ]);
    }
}
