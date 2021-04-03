<?php

namespace App\Controller\BackOffice;

use App\Entity\EquipeDepartementale;
use App\Entity\EquipeParis;
use App\Entity\RencontreDepartementale;
use App\Entity\RencontreParis;
use App\Form\EquipeDepartementaleType;
use App\Form\EquipeParisType;
use App\Repository\DivisionRepository;
use App\Repository\EquipeDepartementaleRepository;
use App\Repository\EquipeParisRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
    private $rencontreDepartementalRepository;
    private $rencontreParisRepository;
    private $divisionRepository;

    /**
     * BackOfficeController constructor.
     * @param EquipeDepartementaleRepository $equipeDepartementaleRepository
     * @param EquipeParisRepository $equipeParisRepository
     * @param JourneeDepartementaleRepository $journeesDepartementaleRepository
     * @param JourneeParisRepository $journeesParisRepository
     * @param RencontreParisRepository $rencontreParisRepository
     * @param RencontreDepartementaleRepository $rencontreDepartementalRepository
     * @param DivisionRepository $divisionRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(EquipeDepartementaleRepository $equipeDepartementaleRepository,
                                EquipeParisRepository $equipeParisRepository,
                                JourneeDepartementaleRepository $journeesDepartementaleRepository,
                                JourneeParisRepository $journeesParisRepository,
                                RencontreParisRepository $rencontreParisRepository,
                                RencontreDepartementaleRepository $rencontreDepartementalRepository,
                                DivisionRepository $divisionRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->equipeDepartementaleRepository = $equipeDepartementaleRepository;
        $this->equipeParisRepository = $equipeParisRepository;
        $this->journeeDepartementaleRepository = $journeesDepartementaleRepository;
        $this->journeeParisRepository = $journeesParisRepository;
        $this->rencontreParisRepository = $rencontreParisRepository;
        $this->divisionRepository = $divisionRepository;
        $this->rencontreDepartementalRepository = $rencontreDepartementalRepository;
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
     * @throws Exception
     */
    public function new(string $type, Request $request): Response
    {
        if ($type != 'departementale' && $type != 'paris') throw new Exception('Ce championnat est inexistant', 500);
        $equipe = ($type == 'departementale' ? new EquipeDepartementale() : new EquipeParis());
        $form = $this->createForm(($type == 'departementale' ? EquipeDepartementaleType::class : EquipeParisType::class), $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()){
                try {
                    $this->em->persist($equipe);
                    $this->em->flush();

                    // Créer les rencontres de l'équipe créée
                    if ($type == 'departementale') $journees = $this->journeeDepartementaleRepository->findAll();
                    else if ($type == 'paris') $journees = $this->journeeParisRepository->findAll();
                    else throw new Exception('Ce championnat est inexistant', 500);
                    $nbMaxJoueurs = $this->divisionRepository->getMaxNbJoueursChamp($type);

                    foreach ($journees as $journee){
                        if ($type == 'departementale'){
                            $rencontre = new RencontreDepartementale();
                            $nbJoueurs = ($equipe->getIdDivision() ? $equipe->getIdDivision()->getNbJoueursChampDepartementale() : $nbMaxJoueurs);
                        }
                        else if ($type == 'paris'){
                            $rencontre = new RencontreParis();
                            $nbJoueurs = ($equipe->getIdDivision() ? $equipe->getIdDivision()->getNbJoueursChampParis() : $nbMaxJoueurs);
                        }
                        else throw new Exception('Ce championnat est inexistant', 500);

                        $rencontre
                            ->setIdJournee($journee)
                            ->setIdEquipe($equipe)
                            ->setDomicile(true)
                            ->setHosted(false)
                            ->setDateReport($journee->getDateJournee())
                            ->setReporte(false)
                            ->setAdversaire(null)
                            ->setExempt(false);

                        for ($i = 0; $i < $nbJoueurs; $i++){
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
     * @param string $type
     * @param int $idEquipe
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(string $type, int $idEquipe, Request $request): Response
    {
        $form = null;
        if ($type == 'departementale'){
            if (!($equipe = $this->equipeDepartementaleRepository->find($idEquipe))) throw new Exception('Cette équipe est inexistante', 500);
            $form = $this->createForm(EquipeDepartementaleType::class, $equipe);
        }
        else if ($type == 'paris'){
            if (!($equipe = $this->equipeParisRepository->find($idEquipe))) throw new Exception('Cette équipe est inexistante', 500);
            $form = $this->createForm(EquipeParisType::class, $equipe);
        }
        else throw new Exception('Ce championnat est inexistant', 500);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Désinscrire les joueurs superflus en cas de changement de division
                if ($type == 'departementale') $rencontres = $this->rencontreDepartementalRepository->findBy(['idEquipe' => $equipe->getIdEquipe()]);
                else if ($type == 'paris') $rencontres = $this->rencontreParisRepository->findBy(['idEquipe' => $equipe->getIdEquipe()]);
                else throw new Exception('Ce championnat est inexistant', 500);

                try {
                    $this->em->flush();
                    if ($equipe->getIdDivision()){
                        $nbMaxJoueurs = $this->divisionRepository->getMaxNbJoueursChamp($type);
                        foreach ($rencontres as $rencontre){
                            for ($i = $equipe->getIdDivision()->getNbJoueursChampParis() + 1; $i <= $nbMaxJoueurs; $i++){
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
     * @param string $type
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function delete(int $idEquipe, string $type, Request $request): Response
    {
        if ($type == 'departementale') $equipe = $this->equipeDepartementaleRepository->find($idEquipe);
        else if ($type == 'paris') $equipe = $this->equipeParisRepository->find($idEquipe);
        else throw new Exception('Ce championnat est inexistant', 500);

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
