<?php

namespace App\Controller\BackOffice;

use App\Controller\UtilController;
use App\Entity\Rencontre;
use App\Form\RencontreType;
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
    public function __construct(RencontreRepository    $rencontreRepository,
                                ChampionnatRepository  $championnatRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreRepository = $rencontreRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/backoffice/rencontres", name="backoffice.rencontres")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->render('backoffice/rencontre/index.html.twig', [
            'rencontres' => $this->championnatRepository->getAllRencontres(),
            'active' => $request->query->get('active')
        ]);
    }

    /**
     * @Route("/backoffice/rencontre/edit/{idRencontre}", name="backoffice.rencontre.edit", requirements={"idRencontre"="\d+"})
     * @param int $idRencontre
     * @param Request $request
     * @param UtilController $utilController
     * @return Response
     */
    public function edit(int $idRencontre, Request $request, UtilController $utilController): Response
    {
        if (!($rencontre = $this->rencontreRepository->find($idRencontre))) {
            $this->addFlash('fail', 'Rencontre inexistante');
            return $this->redirectToRoute('backoffice.rencontres');
        }
        $form = $this->createForm(RencontreType::class, $rencontre);
        $form->handleRequest($request);

        $journees = $rencontre->getIdChampionnat()->getJournees()->toArray();
        $journee = $rencontre->getIDJournee();
        $posJournee = array_keys(array_filter($journees, function ($journeeChamp) use ($journee) {
            return $journeeChamp->getDateJournee() == $journee->getDateJournee();
        }))[0] += 1;

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    /** Si l'équipe est exemptée, on remet les champs à zéro et on vide la composition d'équipe */
                    if ($rencontre->isExempt()) {
                        $rencontre->setAdversaire(null);
                        $rencontre->setVilleHost(null);
                        $rencontre->setReporte(false);
                        $rencontre->emptyCompo();
                        $rencontre->setDateReport($rencontre->getIdJournee()->getDateJournee());
                        $rencontre->setLastUpdate($utilController->getAdminUpdateLog('Modifiée par '));

                        $this->em->flush();
                        $this->addFlash('success', 'Rencontre modifiée');
                        return $this->redirectToRoute('backoffice.rencontres', [
                            'active' => $rencontre->getIdChampionnat()->getIdChampionnat()
                        ]);
                    } else {
                        $rencontre->setAdversaire($rencontre->getAdversaire());

                        /** Si la rencontre n'est pas ou plus avancée/reportée, la date redevient celle de la journée associée **/
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
                                $rencontre->setLastUpdate($utilController->getAdminUpdateLog('Modifiée par '));
                                $this->em->flush();
                                $this->addFlash('success', 'Rencontre modifiée');
                                return $this->redirectToRoute('backoffice.rencontres', [
                                    'active' => $rencontre->getIdChampionnat()->getIdChampionnat()
                                ]);
                            }
                        } else {
                            $rencontre->setDateReport($rencontre->getIdJournee()->getDateJournee());
                            $rencontre->setLastUpdate($utilController->getAdminUpdateLog('Modifiée par '));
                            $this->em->flush();
                            $this->addFlash('success', 'Rencontre modifiée');
                            return $this->redirectToRoute('backoffice.rencontres', [
                                'active' => $rencontre->getIdChampionnat()->getIdChampionnat()
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    if ($e->getCode() == "12345") $this->addFlash('fail', $e->getMessage());
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/rencontre/edit.html.twig', [
            'form' => $form->createView(),
            'idJournee' => $posJournee
        ]);
    }

    /**
     * @Route("/backoffice/rencontre/update/validation/{rencontre}", name="backoffice.rencontre.update.validation", methods={"POST"})
     * @param Rencontre $rencontre
     * @return Response
     */
    public function toggleCompoValidation(Rencontre $rencontre): Response
    {
        try {
            if ($rencontre->isOver()) throw new Exception('La rencontre est déjà passée', 504);
            $rencontre->toggleCompValidation();
            $json = json_encode(['status' => true, 'isValide' => $rencontre->isValidationCompo()]);
            $this->em->flush();
        } catch (Exception $e) {
            $json = json_encode(['status' => false, 'isValide' => null, 'message' => $e->getCode() == 504 ? $e->getMessage() : 'Une erreur est survenue']);
        }

        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
