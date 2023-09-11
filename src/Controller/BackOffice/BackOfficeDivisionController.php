<?php

namespace App\Controller\BackOffice;

use App\Controller\UtilController;
use App\Entity\Division;
use App\Form\DivisionType;
use App\Repository\ChampionnatRepository;
use App\Repository\DivisionRepository;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeDivisionController extends AbstractController
{
    private $em;
    private $divisionRepository;
    private $equipeRepository;
    private $championnatRepository;

    /**
     * BackOfficeController constructor.
     * @param DivisionRepository $divisionRepository
     * @param EntityManagerInterface $em
     * @param ChampionnatRepository $championnatRepository
     * @param EquipeRepository $equipeRepository
     */
    public function __construct(DivisionRepository     $divisionRepository,
                                EntityManagerInterface $em,
                                ChampionnatRepository  $championnatRepository,
                                EquipeRepository       $equipeRepository)
    {
        $this->em = $em;
        $this->divisionRepository = $divisionRepository;
        $this->equipeRepository = $equipeRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/backoffice/divisions", name="backoffice.divisions")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->render('backoffice/division/index.html.twig', [
            'divisions' => $this->championnatRepository->getAllDivisions(),
            'active' => $request->query->get('active')
        ]);
    }

    /**
     * @Route("/backoffice/division/new", name="backoffice.division.new")
     * @param Request $request
     * @param UtilController $utilController
     * @return Response
     */
    public function new(Request $request, UtilController $utilController): Response
    {
        $division = new Division();
        $listChamps = $this->championnatRepository->getAllChampionnats();
        $form = $this->createForm(DivisionType::class, $division, [
            'listChamps' => $listChamps,
            'nbMaxJoueurs' => $this->getParameter('nb_joueurs_default_division')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $listChamps) {
            if ($form->isValid()) {
                try {
                    $division->setLongName($division->getLongName());
                    $division->setShortName($division->getShortName());
                    $division->setLastUpdate($utilController->getAdminUpdateLog('Créée par '));

                    $this->em->persist($division);
                    $this->em->flush();
                    $this->addFlash('success', 'Division créée');
                    return $this->redirectToRoute('backoffice.divisions', [
                        'active' => $division->getIdChampionnat()->getIdChampionnat()
                    ]);
                } catch (Exception $e) {
                    if ($e->getPrevious()->getCode() == "23000") {
                        if (str_contains($e->getPrevious()->getMessage(), 'short_name')) $this->addFlash('fail', "Le diminutif '" . $division->getShortName() . "' est déjà attribué");
                        else if (str_contains($e->getPrevious()->getMessage(), 'long_name')) $this->addFlash('fail', "Le nom '" . $division->getLongName() . "' est déjà attribué");
                        else $this->addFlash('fail', "Le formulaire n'est pas valide");
                    } else $this->addFlash('fail', "Le formulaire n'est pas valide");
                }
            } else $this->addFlash('fail', "Le formulaire n'est pas valide");
        }

        return $this->render('backoffice/division/new.html.twig', [
            'form' => $form->createView(),
            'hasChampionnats' => count($listChamps) > 0
        ]);
    }

    /**
     * @Route("/backoffice/division/edit/{idDivision}", name="backoffice.division.edit", requirements={"idDivision"="\d+"})
     * @param int $idDivision
     * @param Request $request
     * @param UtilController $utilController
     * @return Response
     */
    public function edit(int $idDivision, Request $request, UtilController $utilController): Response
    {
        if (!($division = $this->divisionRepository->find($idDivision))) {
            $this->addFlash('fail', 'Division inexistante');
            return $this->redirectToRoute('backoffice.divisions');
        }

        $nbJoueurs = $division->getNbJoueurs();
        $form = $this->createForm(DivisionType::class, $division, [
            'nbMaxJoueurs' => $this->getParameter('nb_joueurs_default_division')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $division->setLongName($division->getLongName());
                    $division->setShortName($division->getShortName());
                    $division->setLastUpdate($utilController->getAdminUpdateLog('Modifiée par '));

                    /** Si nbJoueurs diminue, on supprime les joueurs superflux des rencontres des équipes affiliées */
                    if ($nbJoueurs > $form->getData()->getNbJoueurs()) {
                        $commposWithPlayerSuperflux = array_filter($division->getIdChampionnat()->getRencontres()->toArray(), function ($rencontre) use ($division) {
                            return $rencontre->getIdEquipe()->getIdDivision()->getIdDivision() == $division->getIdDivision();
                        });

                        foreach ($commposWithPlayerSuperflux as $compo) {
                            for ($i = $form->getData()->getNbJoueurs(); $i < $nbJoueurs; $i++) {
                                $compo->setIdJoueurN($i, null);
                            }
                        }
                    }

                    $this->em->flush();
                    $this->addFlash('success', 'Division modifiée');
                    return $this->redirectToRoute('backoffice.divisions', [
                        'active' => $division->getIdChampionnat()->getIdChampionnat()
                    ]);
                } catch (Exception $e) {
                    if ($e->getPrevious()->getCode() == "23000") {
                        if (str_contains($e->getPrevious()->getMessage(), 'short_name')) $this->addFlash('fail', "Le diminutif '" . $division->getShortName() . "' est déjà attribué");
                        else if (str_contains($e->getPrevious()->getMessage(), 'long_name')) $this->addFlash('fail', "Le nom '" . $division->getLongName() . "' est déjà attribué");
                        else $this->addFlash('fail', "Le formulaire n'est pas valide");
                    } else $this->addFlash('fail', "Le formulaire n'est pas valide");
                }
            } else $this->addFlash('fail', "Le formulaire n'est pas valide");
        }

        return $this->render('backoffice/division/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/backoffice/division/delete/{idDivision}", name="backoffice.division.delete", methods="DELETE", requirements={"idDivision"="\d+"})
     * @param int $idDivision
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function delete(int $idDivision, Request $request): Response
    {
        if (!($division = $this->divisionRepository->find($idDivision))) {
            $this->addFlash('fail', 'Division inexistante');
            return $this->redirectToRoute('backoffice.divisions');
        }

        if ($this->isCsrfTokenValid('delete' . $division->getIdDivision(), $request->get('_token'))) {

            /** On vide les compositions des équipes affiliées à la division supprimée car une équipe sans division n'est pas editable **/
            foreach ($division->getEquipes()->toArray() as $equipes) {
                foreach ($equipes->getRencontres() as $compo) {
                    for ($i = 0; $i < $compo->getIdEquipe()->getIdDivision()->getNbJoueurs(); $i++) {
                        $compo->setIdJoueurN($i, null);
                    }
                }
            }

            /** On set la division des équipes affiliées à NULL **/
            $this->equipeRepository->setDeletedDivisionToNull($idDivision);

            $this->em->remove($division);
            $this->em->flush();
            $this->addFlash('success', 'Division supprimée');
        } else $this->addFlash('fail', "La division n'a pas pu être supprimée");

        return $this->redirectToRoute('backoffice.divisions', [
            'active' => $division->getIdChampionnat()->getIdChampionnat()
        ]);
    }
}
