<?php

namespace App\Controller\BackOffice;

use App\Controller\UtilController;
use App\Entity\Equipe;
use App\Entity\Rencontre;
use App\Entity\Titularisation;
use App\Form\EquipeType;
use App\Repository\CompetiteurRepository;
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
    private $divisionRepository;
    private $competiteurRepository;

    /**
     * BackOfficeController constructor.
     * @param EquipeRepository $equipeRepository
     * @param DivisionRepository $divisionRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(EquipeRepository       $equipeRepository,
                                DivisionRepository     $divisionRepository,
                                CompetiteurRepository  $competiteurRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->equipeRepository = $equipeRepository;
        $this->divisionRepository = $divisionRepository;
        $this->competiteurRepository = $competiteurRepository;
    }

    /**
     * @Route("/backoffice/equipes", name="backoffice.equipes")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->render('backoffice/equipe/index.html.twig', [
            'equipes' => $this->equipeRepository->getAllEquipes(),
            'active' => $request->query->get('active')
        ]);
    }

    /**
     * @Route("/backoffice/equipe/new/", name="backoffice.equipe.new")
     * @param Request $request
     * @param UtilController $utilController
     * @return Response
     */
    public function new(Request $request, UtilController $utilController): Response
    {
        $equipe = new Equipe();
        $divisions = $this->divisionRepository->getDivisionsOptgroup();
        $form = $this->createForm(EquipeType::class, $equipe, [
            'divisionsOptGroup' => $divisions,
            'newEquipe' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $divisions) {
            if ($form->isValid()) {
                try {
                    if (!$equipe->getIdDivision()) throw new Exception('Renseignez une division', 12342);

                    $numEquipesChamp = array_map(function ($eq) {
                        return $eq->getNumero();
                    }, $equipe->getIdDivision()->getIdChampionnat()->getEquipes()->toArray());
                    $numerosManquants = $this->getAllowedNumbers($numEquipesChamp);
                    $lastNumero = $this->getLastNumero($numEquipesChamp);

                    /** On vérifie que le numéro ne soit pas déjà attribué */
                    if (in_array($equipe->getNumero(), $numEquipesChamp))
                        throw new Exception('Le numéro ' . $equipe->getNumero() . ' est déjà attribué', 12340);
                    /** On vérifie qu'il ne manque pas des numéros d'équipes */
                    else if ($numerosManquants && !in_array($equipe->getNumero(), $numerosManquants)) {
                        $nbMissingEquipes = count($numerosManquants);
                        $str = $nbMissingEquipes > 1 ? 'Les équipes ' : 'L\'équipe ';

                        foreach (array_values($numerosManquants) as $i => $numEquipe) {
                            $str .= $numEquipe;
                            if ($i == $nbMissingEquipes - 2) $str .= ' et ';
                            elseif ($i < $nbMissingEquipes - 1) $str .= ', ';
                        }

                        $str .= $nbMissingEquipes > 1 ? ' doivent d\'abord être créées' : ' doit d\'abord être créée';
                        throw new Exception($str, 12341);
                        /** Sinon le numéro attribué doit être le suivant de la dernière équipe */
                    } else if ($equipe->getNumero() != $lastNumero && !$numerosManquants) throw new Exception('Le prochain numéro d\'équipe pour ce championnat doit être le ' . $lastNumero, 12343);

                    $equipe->setIdChampionnat($equipe->getIdDivision()->getIdChampionnat());
                    $equipe->setLastUpdate($utilController->getAdminUpdateLog('Créée par '));
                    $this->createEquipeAndRencontres($equipe);

                    $this->em->flush();
                    $this->addFlash('success', 'Équipe créée');
                    return $this->redirectToRoute('backoffice.equipes', [
                        'active' => $equipe->getIdChampionnat()->getIdChampionnat()
                    ]);
                } catch (Exception $e) {
                    if ($e->getPrevious()) {
                        if ($e->getPrevious()->getCode() == "23000") {
                            if (str_contains($e->getPrevious()->getMessage(), 'numero')) $this->addFlash('fail', 'Le numéro \'' . $equipe->getNumero() . '\' est déjà attribué');
                            else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                        } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    } else if (in_array($e->getCode(), ["12340", "12341", "12342", "12343"])) $this->addFlash('fail', $e->getMessage());
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/equipe/new.html.twig', [
            'form' => $form->createView(),
            'champHasDivisions' => count($divisions) > 0
        ]);
    }

    /**
     * Retourne les numéros des équipes si y en a des manquantes
     * @param array $numEquipes
     * @return array
     */
    public function getAllowedNumbers(array $numEquipes): array
    {
        $numEquipes = array_map(function ($numero) {
            return $numero;
        }, $numEquipes);
        $range = range(1, $numEquipes ? max($numEquipes) : 1);
        return array_diff($range, $numEquipes);
    }

    /**
     * Retourne le numéro de la prochaine équipe à créer du championnat
     * @param array $numEquipes
     * @return int|null
     */
    public function getLastNumero(array $numEquipes): ?int
    {
        $lastNumero = array_map(function ($numero) {
            return $numero;
        }, $numEquipes);
        return $lastNumero ? max($lastNumero) + 1 : null;
    }

    /**
     * @param Equipe $equipe
     */
    public function createEquipeAndRencontres(Equipe $equipe)
    {
        $this->em->persist($equipe);

        /** On créé toutes les rencontres de la nouvelle équipe **/
        $journees = $equipe->getIdChampionnat()->getJournees()->toArray();
        foreach ($journees as $journee) {
            $rencontre = new Rencontre($equipe->getIdChampionnat());
            $rencontre
                ->setValidationCompo(false)
                ->setIdJournee($journee)
                ->setIdEquipe($equipe)
                ->setDomicile(null)
                ->setVilleHost(false)
                ->setDateReport($journee->getDateJournee())
                ->setReporte(false)
                ->setAdversaire(null)
                ->setExempt(false);
            $this->em->persist($rencontre);
        }
    }

    /**
     * @Route("/backoffice/equipe/edit/{idEquipe}", name="backoffice.equipe.edit", requirements={"idEquipe"="\d+"})
     * @param int $idEquipe
     * @param Request $request
     * @param UtilController $utilController
     * @return Response
     */
    public function edit(int $idEquipe, Request $request, UtilController $utilController): Response
    {
        if (!($equipe = $this->equipeRepository->find($idEquipe))) {
            $this->addFlash('fail', 'Équipe inexistante');
            return $this->redirectToRoute('backoffice.equipes');
        }
        $champHasDivisions = count($equipe->getIdChampionnat()->getDivisions()->toArray()) > 0;
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $champHasDivisions) {
            if ($form->isValid()) {
                try {
                    $lastNbJoueursDivision = $equipe->getIdDivision()->getNbJoueurs();
                    /** Désinscrire les joueurs superflus en cas de changement de division **/
                    if ($equipe->getIdDivision() && $lastNbJoueursDivision > $equipe->getIdDivision()->getNbJoueurs()) {
                        foreach ($equipe->getRencontres()->toArray() as $rencontre) {
                            for ($i = $equipe->getIdDivision()->getNbJoueurs(); $i < $lastNbJoueursDivision; $i++) {
                                $rencontre->setIdJoueurN($i, null);
                            }
                        }
                    }
                    $equipe->setLastUpdate($utilController->getAdminUpdateLog('Modifiée par '));

                    $this->em->flush();
                    $this->addFlash('success', 'Équipe modifiée');
                    return $this->redirectToRoute('backoffice.equipes', [
                        'active' => $equipe->getIdChampionnat()->getIdChampionnat()
                    ]);
                } catch (Exception $e) {
                    if ($e->getPrevious() && $e->getPrevious()->getCode() == "23000") {
                        if (str_contains($e->getPrevious()->getMessage(), 'numero')) $this->addFlash('fail', 'Le numéro ' . $equipe->getNumero() . ' est déjà attribué');
                        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/equipe/edit.html.twig', [
            'championnat' => $equipe->getIdChampionnat()->getNom(),
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
        if (!($equipe = $this->equipeRepository->find($idEquipe))) {
            $this->addFlash('fail', 'Equipe inexistante');
            return $this->redirectToRoute('backoffice.equipes');
        }

        if ($this->isCsrfTokenValid('delete' . $equipe->getIdEquipe(), $request->get('_token'))) {
            $this->em->remove($equipe);
            $this->em->flush();
            $this->addFlash('success', 'Équipe supprimée');
        } else $this->addFlash('error', 'L\'équipe n\'a pas pu être supprimée');

        return $this->redirectToRoute('backoffice.equipes', [
            'active' => $equipe->getIdChampionnat()->getIdChampionnat()
        ]);
    }

    /**
     * @Route("/backoffice/equipe/edit/players/{idEquipe}", name="backoffice.equipe.edit.players", requirements={"idEquipe"="\d+"})
     * @param int $idEquipe
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function editPlayers(int $idEquipe, Request $request): Response
    {
        if (!($equipe = $this->equipeRepository->find($idEquipe))) {
            $this->addFlash('fail', 'Équipe inexistante');
            return $this->redirectToRoute('backoffice.equipes');
        }

        $oldTitularisations = $equipe->getJoueursAssocies()->toArray();

        $form = $this->createForm(EquipeType::class, $equipe, [
            'editListeTitulaires' => true,
            'choices' => $this->competiteurRepository->findBy(['isCompetiteur' => true], ['classement_officiel' => 'DESC', 'nom' => 'ASC', 'prenom' => 'ASC'])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    /** IDs des joueurs sélectionnés */
                    $idsSelectedPlayers = array_map(function ($j) {
                        return $j->getIdCompetiteur();
                    }, $equipe->getJoueursAssocies()->toArray());

                    /** On supprime les titularisations si des joueurs sont déjà affectés dans une autre équipe */
                    $titus = $equipe->getIdChampionnat()->getTitularisations()->toArray();
                    $oldTitularisationsToRemove = array_filter($titus, function ($titu) use ($equipe, $idsSelectedPlayers) {
                        return $titu->getIdChampionnat()->getIdChampionnat() == $equipe->getIdChampionnat()->getIdChampionnat()
                            && $titu->getIdEquipe()->getIdEquipe() != $equipe->getIdEquipe()
                            && in_array($titu->getIdCompetiteur()->getIdCompetiteur(), $idsSelectedPlayers);
                    });
                    foreach ($oldTitularisationsToRemove as $oldTitularisationToRemove) {
                        $this->em->remove($oldTitularisationToRemove);
                    }
                    $this->em->flush();

                    $idsOldTitularisations = array_map(function ($j) {
                        return $j->getIdCompetiteur();
                    }, $oldTitularisations);

                    $joueursToRemove = array_filter($oldTitularisations, function ($joueur) use ($oldTitularisations, $idsSelectedPlayers) {
                        return !in_array($joueur->getIdCompetiteur(), $idsSelectedPlayers);
                    });
                    $idsJoueursToRemove = array_map(function ($j) {
                        return $j->getIdCompetiteur();
                    }, $joueursToRemove);

                    $titularisationsToRemove = array_filter($equipe->getTitularisations()->toArray(), function ($j) use ($idsJoueursToRemove) {
                        return in_array($j->getIdCompetiteur()->getIdCompetiteur(), $idsJoueursToRemove);
                    });

                    $joueursToAdd = array_filter($equipe->getJoueursAssocies()->toArray(), function ($joueur) use ($oldTitularisations, $idsOldTitularisations) {
                        return !in_array($joueur->getIdCompetiteur(), $idsOldTitularisations);
                    });

                    foreach ($titularisationsToRemove as $titularisatipn) {
                        $this->em->remove($titularisatipn);
                    }
                    $this->em->flush();

                    foreach ($joueursToAdd as $joueur) {
                        $newTitularisation = new Titularisation($joueur, $equipe, $equipe->getIdChampionnat());
                        $this->em->persist($newTitularisation);
                    }
                    $this->em->flush();

                    $this->addFlash('success', 'Titulaires de l\'équipe ' . $equipe->getNumero() . ' modifiés');

                    return $this->redirectToRoute('backoffice.equipes', [
                        'active' => $equipe->getIdChampionnat()->getIdChampionnat()
                    ]);
                } catch (Exception $e) {
                    $this->addFlash('fail', 'Une erreur est survenue');
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/equipe/editPlayers.html.twig', [
            'championnat' => $equipe->getIdChampionnat(),
            'form' => $form->createView()
        ]);
    }
}
