<?php

namespace App\Controller\BackOffice;

use App\Entity\Equipe;
use App\Entity\Rencontre;
use App\Form\EquipeType;
use App\Repository\ChampionnatRepository;
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

    /**
     * BackOfficeController constructor.
     * @param EquipeRepository $equipeRepository
     * @param ChampionnatRepository $championnatRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(EquipeRepository $equipeRepository,
                                ChampionnatRepository $championnatRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->equipeRepository = $equipeRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/backoffice/equipes/{focusedTab?}", name="backoffice.equipes")
     * @param string|null $focusedTab
     * @return Response
     */
    public function indexEquipes(?string $focusedTab): Response
    {
        return $this->render('backoffice/equipe/index.html.twig', [
            'equipes' => $this->championnatRepository->getAllEquipes(),
            'focusedTab' => $focusedTab
        ]);
    }

    /**
     * //TOOD Changer sans prendre le championnat en paramètre
     * @Route("/backoffice/equipe/new/", name="backoffice.equipe.new")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function new(Request $request): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()){
                try {
                    $equipe->setIdChampionnat($equipe->getIdDivision()->getIdChampionnat());
                    $this->em->persist($equipe);

                    /** On créé toutes les rencontres de la nouvelle équipe **/
                    foreach ($equipe->getIdChampionnat()->getJournees()->toArray() as $journee){
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
                    $this->addFlash('success', 'Equipe créée avec succès !');
                    return $this->redirectToRoute('backoffice.equipes');
                } catch(Exception $e){
                    if ($e->getPrevious()->getCode() == "23000") $this->addFlash('fail', 'Le numéro \'' . $equipe->getNumero() . '\' est déjà attribué');
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/new.html.twig', [
            'form' => $form->createView(),
            'title' => 'équipes',
            'macro' => 'equipe'
        ]);
    }

    /**
     * @Route("/backoffice/equipe/edit/{idEquipe}", name="backoffice.equipe.edit", requirements={"idEquipe"="\d+"})
     * @param Equipe $equipeForm
     * @param int $idEquipe
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(Equipe $equipeForm, int $idEquipe, Request $request): Response
    {
        if (!($equipe = $this->equipeRepository->find($idEquipe))) throw new Exception('Cette équipe est inexistante', 500);
        $form = $this->createForm(EquipeType::class, $equipeForm);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                if ($equipeForm->getIdDivision()){
                    try {
                        $lastNbJoueursDivision = $equipe->getIdDivision()->getNbJoueurs();
                        /** Désinscrire les joueurs superflus en cas de changement de division **/
                        if ($equipeForm->getIdDivision() && $lastNbJoueursDivision > $equipeForm->getIdDivision()->getNbJoueurs()){
                            foreach ($equipeForm->getRencontres()->toArray() as $rencontre){
                                for ($i = $equipeForm->getIdDivision()->getNbJoueurs(); $i < $lastNbJoueursDivision; $i++){
                                    $rencontre->setIdJoueurN($i, null);
                                }
                            }
                        }

                        $this->em->flush();
                        $this->addFlash('success', 'Equipe modifiée avec succès !');
                        return $this->redirectToRoute('backoffice.equipes');
                    } catch(Exception $e){
                        if ($e->getPrevious()->getCode() == "23000") $this->addFlash('fail', 'Le numéro \'' . $equipeForm->getNumero() . '\' est déjà attribué');
                        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    }
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/edit.html.twig', [
            'equipe' => $equipeForm,
            'form' => $form->createView(),
            'title' => 'Modifier l\'équipe',
            'macro' => 'equipe',
            'textForm' => 'Modifier'
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
            $this->addFlash('success', 'Équipe supprimée avec succès !');
        } else $this->addFlash('error', 'L\'équipe n\'a pas pu être supprimée');

        return $this->redirectToRoute('backoffice.equipes');
    }
}
