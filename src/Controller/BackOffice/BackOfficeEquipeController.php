<?php

namespace App\Controller\BackOffice;

use App\Entity\EquipeDepartementale;
use App\Entity\EquipeParis;
use App\Entity\RencontreDepartementale;
use App\Entity\RencontreParis;
use App\Form\EquipeDepartementaleType;
use App\Form\EquipeParisType;
use App\Repository\EquipeDepartementaleRepository;
use App\Repository\EquipeParisRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
use App\Repository\RencontreParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeEquipeController extends AbstractController
{
    private $em;
    private $equipeDepartementaleRepository;
    private $equipeParisRepository;
    private $journeeDepartementaleRepository;
    private $journeeParisRepository;
    private $rencontreParisRepository;

    /**
     * BackOfficeController constructor.
     * @param EquipeDepartementaleRepository $equipeDepartementaleRepository
     * @param EquipeParisRepository $equipeParisRepository
     * @param JourneeDepartementaleRepository $journeesDepartementaleRepository
     * @param JourneeParisRepository $journeesParisRepository
     * @param RencontreParisRepository $rencontreParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(EquipeDepartementaleRepository $equipeDepartementaleRepository,
                                EquipeParisRepository $equipeParisRepository,
                                JourneeDepartementaleRepository $journeesDepartementaleRepository,
                                JourneeParisRepository $journeesParisRepository,
                                RencontreParisRepository $rencontreParisRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->equipeDepartementaleRepository = $equipeDepartementaleRepository;
        $this->equipeParisRepository = $equipeParisRepository;
        $this->journeeDepartementaleRepository = $journeesDepartementaleRepository;
        $this->journeeParisRepository = $journeesParisRepository;
        $this->rencontreParisRepository = $rencontreParisRepository;
    }

    /**
     * @Route("/backoffice/equipes", name="backoffice.equipes")
     * @return Response
     */
    public function indexEquipes(): Response
    {
        return $this->render('backoffice/equipe/index.html.twig', [
            'equipesDepartementales' => $this->equipeDepartementaleRepository->findAll(),
            'equipesParis' => $this->equipeParisRepository->findAll()
        ]);
    }

    /**
     * @Route("/backoffice/equipe/{type}/new", name="backoffice.equipe.new")
     * @param string $type
     * @param Request $request
     * @return Response
     */
    public function new(string $type, Request $request): Response
    {
        if ($type != 'departementale' && $type != 'paris') throw $this->createNotFoundException('Championnat inexistant');
        $equipe = ($type == 'departementale' ? new EquipeDepartementale() : new EquipeParis());
        $form = $this->createForm(($type == 'departementale' ? EquipeDepartementaleType::class : EquipeParisType::class), $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()){
                $this->em->persist($equipe);
                $this->em->flush();

                // Créer les rencontres de l'équipe créée
                if ($type == 'departementale'){
                    $journees = $this->journeeDepartementaleRepository->findAll();
                    foreach ($journees as $journee){
                        $rencontre = new RencontreDepartementale();
                        $rencontre
                            ->setIdJournee($journee)
                            ->setIdEquipe($equipe)
                            ->setIdJoueur1(null)
                            ->setIdJoueur2(null)
                            ->setIdJoueur3(null)
                            ->setIdJoueur4(null)
                            ->setDomicile(true)
                            ->setHosted(false)
                            ->setDateReport($journee->getDate())
                            ->setReporte(false)
                            ->setAdversaire(null)
                            ->setExempt(false);
                        $this->em->persist($rencontre);
                    }
                }
                else if ($type == 'paris'){
                    $journees = $this->journeeParisRepository->findAll();
                    foreach ($journees as $journee){
                        $rencontre = new RencontreParis();
                        $rencontre
                            ->setIdJournee($journee)
                            ->setIdEquipe($equipe)
                            ->setIdJoueur1(null)
                            ->setIdJoueur2(null)
                            ->setIdJoueur3(null)
                            ->setIdJoueur4(null)
                            ->setIdJoueur5(null)
                            ->setIdJoueur6(null)
                            ->setIdJoueur7(null)
                            ->setIdJoueur8(null)
                            ->setIdJoueur9(null)
                            ->setDomicile(true)
                            ->setHosted(false)
                            ->setDateReport($journee->getDate())
                            ->setReporte(false)
                            ->setAdversaire(null)
                            ->setExempt(false);
                        $this->em->persist($rencontre);
                    }
                }
                $this->em->flush();

                $this->addFlash('success', 'Equipe créée avec succès !');
                return $this->redirectToRoute('backoffice.equipes');
            } else {
                $this->addFlash('fail', 'Une erreur est survenue ...');
            }
        }

        return $this->render('backoffice/equipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/equipe/edit/{type}/{idEquipe}", name="backoffice.equipe.edit")
     * @param int $idEquipe
     * @param string $type
     * @param Request $request
     * @return Response
     */
    public function edit(string $type, int $idEquipe, Request $request): Response
    {
        $form = null;
        if ($type == 'departementale'){
            if (!($equipe = $this->equipeDepartementaleRepository->find($idEquipe))) throw $this->createNotFoundException('Equipe inexistante');
            $form = $this->createForm(EquipeDepartementaleType::class, $equipe);
        }
        else if ($type == 'paris'){
            if (!($equipe = $this->equipeParisRepository->find($idEquipe))) throw $this->createNotFoundException('Equipe inexistante');
            $form = $this->createForm(EquipeParisType::class, $equipe);
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            // Désinscrire les joueurs superflus en cas de changement de division pour une équipe du championnat de Paris
            if ($type == 'paris') {
                $rencontres = $this->rencontreParisRepository->findBy(['idEquipe' => $equipe->getIdEquipe()]);
                foreach ($rencontres as $rencontre){
                    if ($equipe->getIdDivision()){
                        if ($equipe->getIdDivision()->getNbJoueursChampParis() <= 3) {
                            $rencontre
                                ->setIdJoueur4(null)
                                ->setIdJoueur5(null)
                                ->setIdJoueur6(null);
                        }
                        if ($equipe->getIdDivision()->getNbJoueursChampParis() <= 6) {
                            $rencontre
                                ->setIdJoueur7(null)
                                ->setIdJoueur8(null)
                                ->setIdJoueur9(null);
                        }
                        $this->em->persist($rencontre);
                    }
                }
            }

            $this->em->flush();
            $this->addFlash('success', 'Equipe modifiée avec succès !');
            return $this->redirectToRoute('backoffice.equipes');
        }

        return $this->render('backoffice/equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/equipe/delete/{type}/{idEquipe}", name="backoffice.equipe.delete", methods="DELETE")
     * @param int $idEquipe
     * @param string $type
     * @param Request $request
     * @return Response
     */
    public function delete(int $idEquipe, string $type, Request $request): Response
    {
        if ($type == 'departementale') $equipe = $this->equipeDepartementaleRepository->find($idEquipe);
        else if ($type == 'paris') $equipe = $this->equipeParisRepository->find($idEquipe);
        else throw $this->createNotFoundException('Championnat inexistant');

        if ($this->isCsrfTokenValid('delete' . $equipe->getIdEquipe(), $request->get('_token'))) {
            $this->em->remove($equipe);
            $this->em->flush();
            $this->addFlash('success', 'Équipe supprimée avec succès !');
        } else $this->addFlash('error', 'L\'équipe n\'a pas pu être supprimée');

        return $this->render('backoffice/equipe/index.html.twig', [
            'equipesDepartementales' => $this->equipeDepartementaleRepository->findAll(),
            'equipesParis' => $this->equipeParisRepository->findAll()
        ]);
    }
}
