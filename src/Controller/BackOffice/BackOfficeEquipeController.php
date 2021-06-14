<?php

namespace App\Controller\BackOffice;

use App\Entity\Equipe;
use App\Entity\Rencontre;
use App\Form\EquipeEditType;
use App\Form\EquipeNewType;
use App\Repository\ChampionnatRepository;
use App\Repository\DivisionRepository;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeEquipeController extends AbstractController
{
    private $em;
    private $equipeRepository;
    private $championnatRepository;
    private $divisionRepository;

    /**
     * BackOfficeController constructor.
     * @param EquipeRepository $equipeRepository
     * @param ChampionnatRepository $championnatRepository
     * @param DivisionRepository $divisionRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(EquipeRepository $equipeRepository,
                                ChampionnatRepository $championnatRepository,
                                DivisionRepository $divisionRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->equipeRepository = $equipeRepository;
        $this->divisionRepository = $divisionRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/backoffice/equipes/{focusedTab?}", name="backoffice.equipes")
     * @param string|null $focusedTab
     * @return Response
     */
    public function index(?string $focusedTab): Response
    {
        return $this->render('backoffice/equipe/index.html.twig', [
            'equipes' => $this->championnatRepository->getAllEquipes(),
            'focusedTab' => $focusedTab
        ]);
    }

    /**
     * @Route("/backoffice/equipe/new/", name="backoffice.equipe.new")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function new(Request $request): Response
    {
        $equipe = new Equipe();
        $divisions = $this->divisionRepository->getDivisionsOptgroup();
        $form = $this->createForm(EquipeNewType::class, $equipe, [
            'divisionsOptGroup' => $divisions
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $divisions){
            if ($form->isValid()){
                try {
                    $equipe->setIdChampionnat($equipe->getIdDivision()->getIdChampionnat());
                    $this->em->persist($equipe);

                    /** On créé toutes les rencontres de la nouvelle équipe **/
                    $journees = $equipe->getIdChampionnat()->getJournees()->toArray();
                    foreach ($journees as $journee){
                        $rencontre = new Rencontre($equipe->getIdChampionnat());
                        $rencontre
                            ->setIdJournee($journee)
                            ->setIdEquipe($equipe)
                            ->setDomicile(true)
                            ->setHosted(false)
                            ->setDateReport($journee->getDateJournee())
                            ->setReporte(false)
                            ->setAdversaire(null)
                            ->setExempt(false);
                        $this->em->persist($rencontre);
                    }

                    $this->em->flush();
                    $this->addFlash('success', 'Equipe créée');
                    return $this->redirectToRoute('backoffice.equipes');
                } catch(Exception $e){
                    if ($e->getPrevious()->getCode() == "23000"){
                        if (str_contains($e->getPrevious()->getMessage(), 'numero')) $this->addFlash('fail', 'Le numéro \'' . $equipe->getNumero() . '\' est déjà attribué');
                        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/equipe/new.html.twig', [
            'form' => $form->createView(),
            'champHasDivisions' => count($divisions) > 0
        ]);
    }

    /**
     * @Route("/backoffice/equipe/edit/{idEquipe}", name="backoffice.equipe.edit", requirements={"idEquipe"="\d+"})
     * @param int $idEquipe
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(int $idEquipe, Request $request): Response
    {
        if (!($equipe = $this->equipeRepository->find($idEquipe))) throw new Exception('Cette équipe est inexistante', 500);
        $champHasDivisions = count($equipe->getIdChampionnat()->getDivisions()->toArray()) > 0;
        $form = $this->createForm(EquipeEditType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $champHasDivisions) {
            if ($form->isValid()) {
                    try {
                        $lastNbJoueursDivision = $equipe->getIdDivision()->getNbJoueurs();
                        /** Désinscrire les joueurs superflus en cas de changement de division **/
                        if ($equipe->getIdDivision() && $lastNbJoueursDivision > $equipe->getIdDivision()->getNbJoueurs()){
                            foreach ($equipe->getRencontres()->toArray() as $rencontre){
                                for ($i = $equipe->getIdDivision()->getNbJoueurs(); $i < $lastNbJoueursDivision; $i++){
                                    $rencontre->setIdJoueurN($i, null);
                                }
                            }
                        }

                        $this->em->flush();
                        $this->addFlash('success', 'Equipe modifiée');
                        return $this->redirectToRoute('backoffice.equipes');
                    } catch(Exception $e){
                        if ($e->getPrevious()->getCode() == "23000"){
                            if (str_contains($e->getPrevious()->getMessage(), 'numero')) $this->addFlash('fail', 'Le numéro \'' . $equipe->getNumero() . '\' est déjà attribué');
                            else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                        } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView(),
            'champHasDivisions' => $champHasDivisions
        ]);
    }

    /**
     * @Route("/backoffice/equipe/delete/{idEquipe}", name="backoffice.equipe.delete", methods="DELETE", requirements={"idEquipe"="\d+"})
     * @param int $idEquipe
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function delete(int $idEquipe, Request $request): Response
    {
        $equipe = $this->equipeRepository->find($idEquipe);

        if ($this->isCsrfTokenValid('delete' . $equipe->getIdEquipe(), $request->get('_token'))) {
            $this->em->remove($equipe);
            $this->em->flush();
            $this->addFlash('success', 'Équipe supprimée');
        } else $this->addFlash('error', 'L\'équipe n\'a pas pu être supprimée');

        return $this->redirectToRoute('backoffice.equipes');
    }
}
