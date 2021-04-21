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
     * @param ChampionnatRepository $championnatepository
     * @param EntityManagerInterface $em
     */
    public function __construct(EquipeRepository $equipeRepository,
                                ChampionnatRepository $championnatepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->equipeRepository = $equipeRepository;
        $this->championnatRepository = $championnatepository;
    }

    /**
     * @Route("/backoffice/equipes", name="backoffice.equipes")
     * @return Response
     */
    public function indexEquipes(): Response
    {
        return $this->render('backoffice/equipe/index.html.twig', [
            'equipes' => $this->equipeRepository->getAllEquipes()
        ]);
    }

    /**
     * @Route("/backoffice/equipe/new/{type}", name="backoffice.equipe.new")
     * @param string $type
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function new(string $type, Request $request): Response
    {
        if (!($championnat = $this->championnatRepository->findOneBy(['nom' => $type]))) throw new Exception('Ce championnat est inexistant', 500);

        $equipe = new Equipe($championnat);
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()){
                try {
                    $this->em->persist($equipe);

                    /** On créé toutes les rencontres de la nouvelle équipe **/
                    foreach ($championnat->getJournees()->toArray() as $journee){
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
                    return $this->render('backoffice/equipe/new.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/equipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/equipe/edit/{idEquipe}", name="backoffice.equipe.edit")
     * @param Equipe $equipeForm
     * @param int $idEquipe
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(Equipe $equipeForm, int $idEquipe, Request $request): Response
    {
        if (!($equipe = $this->equipeRepository->find($idEquipe))) throw new Exception('Cette équipe est inexistante', 500);
        $lastNbJoueursDivision = $equipe->getIdDivision()->getNbJoueurs();

        $form = $this->createForm(EquipeType::class, $equipeForm);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                /** Désinscrire les joueurs superflus en cas de changement de division **/
                if ($equipeForm->getIdDivision() && $lastNbJoueursDivision > $equipeForm->getIdDivision()->getNbJoueurs()){
                    try {
                        foreach ($equipeForm->getRencontres()->toArray() as $rencontre){
                            for ($i = $equipeForm->getIdDivision()->getNbJoueurs(); $i < $lastNbJoueursDivision; $i++){
                                $rencontre->setIdJoueurN($i, null);
                            }
                        }
                    } catch(Exception $e){
                        if ($e->getPrevious()->getCode() == "23000") $this->addFlash('fail', 'Le numéro \'' . $equipeForm->getNumero() . '\' est déjà attribué');
                        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                        return $this->render('backoffice/equipe/edit.html.twig', [
                            'equipe' => $equipeForm,
                            'form' => $form->createView()
                        ]);
                    }
                }

                $this->em->flush();
                $this->addFlash('success', 'Equipe modifiée avec succès !');
                return $this->redirectToRoute('backoffice.equipes');
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/equipe/edit.html.twig', [
            'equipe' => $equipeForm,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/equipe/delete/{idEquipe}", name="backoffice.equipe.delete", methods="DELETE")
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
