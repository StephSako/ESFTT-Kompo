<?php

namespace App\Controller\BackOffice;

use App\Entity\Equipe;
use App\Entity\Rencontre;
use App\Form\EquipeType;
use App\Repository\ChampionnatRepository;
use App\Repository\EquipeRepository;
use App\Repository\JourneeRepository;
use App\Repository\RencontreRepository;
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
    private $journeeRepository;
    private $rencontreRepository;
    private $championnatRepository;

    /**
     * BackOfficeController constructor.
     * @param EquipeRepository $equipeRepository
     * @param JourneeRepository $journeesRepository
     * @param RencontreRepository $rencontreRepository
     * @param ChampionnatRepository $championnatRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(EquipeRepository $equipeRepository,
                                JourneeRepository $journeesRepository,
                                RencontreRepository $rencontreRepository,
                                ChampionnatRepository $championnatRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->equipeRepository = $equipeRepository;
        $this->journeeRepository = $journeesRepository;
        $this->championnatRepository = $championnatRepository;
        $this->rencontreRepository = $rencontreRepository;
    }

    /**
     * @Route("/backoffice/equipes", name="backoffice.equipes")
     * @return Response
     */
    public function indexEquipes(): Response
    {
        return $this->render('backoffice/equipe/index.html.twig', [
            // TODO Classer selon les champioonats
            'equipes' => $this->equipeRepository->findAll()
        ]);
    }

    /**
     * @Route("/backoffice/equipe/{type}/new", name="backoffice.equipe.new")
     * @param int $type
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function new(int $type, Request $request): Response
    {
        if (!($championnat = $this->championnatRepository->find($type))) throw new Exception('Ce championnat est inexistant', 500);
        $equipe = new Equipe($championnat);
        $form = $this->createForm(EquipeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()){
                try {
                    $this->em->persist($equipe);
                    $this->em->flush();

                    // Créer les rencontres de l'équipe créée
                    $journees = $this->journeeRepository->findAll();

                    foreach ($journees as $journee){
                        $rencontre = new Rencontre($championnat);

                        $rencontre
                            ->setIdJournee($journee)
                            ->setIdEquipe($equipe)
                            ->setDomicile(true)
                            ->setHosted(false)
                            ->setDateReport($journee->getDateJournee())
                            ->setReporte(false)
                            ->setAdversaire(null)
                            ->setExempt(false);

                        for ($i = 0; $i < $this->getParameter('nb_max_joueurs'); $i++){
                            $rencontre->setIdJoueurN($i, null);
                        }
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
     * @Route("/backoffice/equipe/edit/{type}/{idEquipe}", name="backoffice.equipe.edit")
     * @param int $type
     * @param int $idEquipe
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(int $type, int $idEquipe, Request $request): Response
    {
        if (!$this->championnatRepository->find($type)) throw new Exception('Ce championnat est inexistant', 500);
        if (!($equipe = $this->equipeRepository->find($idEquipe))) throw new Exception('Cette équipe est inexistante', 500);

        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Désinscrire les joueurs superflus en cas de changement de division
                $rencontres = $this->rencontreRepository->findBy(['idEquipe' => $equipe->getIdEquipe()]);

                try {
                    $this->em->flush();
                    if ($equipe->getIdDivision()){
                        foreach ($rencontres as $rencontre){
                            for ($i = $equipe->getIdDivision()->getNbJoueurs() + 1; $i <= $this->getParameter('nb_max_joueurs'); $i++){
                                $rencontre->setIdJoueurN($i, null);
                            }
                        }
                        $this->em->flush();
                    }
                    $this->addFlash('success', 'Equipe modifiée avec succès !');
                    return $this->redirectToRoute('backoffice.equipes');
                } catch(Exception $e){
                    if ($e->getPrevious()->getCode() == "23000") $this->addFlash('fail', 'Le numéro \'' . $equipe->getNumero() . '\' est déjà attribué');
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    return $this->render('backoffice/equipe/edit.html.twig', [
                        'equipe' => $equipe,
                        'form' => $form->createView()
                    ]);
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/equipe/delete/{type}/{idEquipe}", name="backoffice.equipe.delete", methods="DELETE")
     * @param int $idEquipe
     * @param int $type
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function delete(int $idEquipe, int $type, Request $request): Response
    {
        if (!$this->championnatRepository->find($type)) throw new Exception('Ce championnat est inexistant', 500);
        $equipe = $this->equipeRepository->find($idEquipe);

        if ($this->isCsrfTokenValid('delete' . $equipe->getIdEquipe(), $request->get('_token'))) {
            $this->em->remove($equipe);
            $this->em->flush();
            $this->addFlash('success', 'Équipe supprimée avec succès !');
        } else $this->addFlash('error', 'L\'équipe n\'a pas pu être supprimée');

        return $this->render('backoffice/equipe/index.html.twig', [
            // TODO Classer selon les championnats
            'equipes' => $this->equipeRepository->findAll()
        ]);
    }
}
