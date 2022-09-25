<?php

namespace App\Controller\BackOffice;

use App\Entity\Equipe;
use App\Entity\Rencontre;
use App\Form\EquipeType;
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
     * @Route("/backoffice/equipes", name="backoffice.equipes")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->render('backoffice/equipe/index.html.twig', [
            'equipes' => $this->championnatRepository->getAllEquipes(),
            'active' => $request->query->get('active')
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
        $form = $this->createForm(EquipeType::class, $equipe, [
            'divisionsOptGroup' => $divisions,
            'newEquipe' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $divisions){
            if ($form->isValid()){
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
                    $this->createEquipeAndRencontres($equipe);

                    $this->em->flush();
                    $this->addFlash('success', 'Équipe créée');
                    return $this->redirectToRoute('backoffice.equipes', [
                        'active' => $equipe->getIdChampionnat()->getIdChampionnat()
                    ]);
                } catch(Exception $e){
                    if ($e->getPrevious()) {
                        if ($e->getPrevious()->getCode() == "23000") {
                            if (str_contains($e->getPrevious()->getMessage(), 'numero')) $this->addFlash('fail', 'Le numéro \'' . $equipe->getNumero() . '\' est déjà attribué');
                            else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                        } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    } else if (in_array($e->getCode(), ["12340", "12341", "12342", "12343"]))  $this->addFlash('fail', $e->getMessage());
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
                        if ($equipe->getIdDivision() && $lastNbJoueursDivision > $equipe->getIdDivision()->getNbJoueurs()){
                            foreach ($equipe->getRencontres()->toArray() as $rencontre){
                                for ($i = $equipe->getIdDivision()->getNbJoueurs(); $i < $lastNbJoueursDivision; $i++){
                                    $rencontre->setIdJoueurN($i, null);
                                }
                            }
                        }

                        $this->em->flush();
                        $this->addFlash('success', 'Équipe modifiée');
                        return $this->redirectToRoute('backoffice.equipes', [
                            'active' => $equipe->getIdChampionnat()->getIdChampionnat()
                        ]);
                    } catch(Exception $e){
                        if ($e->getPrevious() && $e->getPrevious()->getCode() == "23000"){
                            if (str_contains($e->getPrevious()->getMessage(), 'numero')) $this->addFlash('fail', 'Le numéro ' . $equipe->getNumero() . ' est déjà attribué');
                            else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                        } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/equipe/edit.html.twig', [
            'form' => $form->createView(),
            'listeEquipesSameDivision' => $equipe->getIdDivision() ? $this->equipeRepository->getEquipesWithSameDivision($equipe) : [],
            'champHasDivisions' => $champHasDivisions
        ]);
    }

    /**
     * @Route("/backoffice/equipe/switch/{idEquipe}", name="backoffice.equipe.switch", requirements={"idEquipe"="\d+"})
     * @param int $idEquipe
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function switch(int $idEquipe, Request $request): Response
    {
        if (!($equipe = $this->equipeRepository->find($idEquipe))) {
            $this->addFlash('fail', 'Équipe inexistante');
            return $this->redirectToRoute('backoffice.equipes');
        }

        if (!($equipeToSwitch = $this->equipeRepository->find($request->request->get('listEquipeSwitch')))) {
            $this->addFlash('fail', 'Équipe inexistante');
            return $this->redirectToRoute('backoffice.equipes');
        }

        try {
            // On bypass la contrainte d'unicité sur le numéro
            $numEquipeToSwitch = $equipeToSwitch->getNumero();
            $numEquipe = $equipe->getNumero();
            $lienDivisionEquipeToSwitch = $equipeToSwitch->getLienDivision();
            $lienDivisionEquipe = $equipe->getLienDivision();
            $equipeToSwitch->setNumero(101); // TODO Fix le 100
            $this->em->flush($equipeToSwitch);

            $equipe->setNumero($numEquipeToSwitch);
            $equipe->setLienDivision($lienDivisionEquipeToSwitch);
            $this->em->flush();

            $equipeToSwitch->setNumero($numEquipe);
            $equipeToSwitch->setLienDivision($lienDivisionEquipe);
            $this->em->flush();

            $this->addFlash('success', 'Les équipes ' .$equipe->getNumero() . ' et ' . $equipeToSwitch->getNumero() . ' de ' . $equipe->getIdDivision()->getShortName() . ' ont été switchées');
        } catch(Exception $e){
            if ($e->getPrevious() && $e->getPrevious()->getCode() == "23000"){
                if (str_contains($e->getPrevious()->getMessage(), 'numero')) $this->addFlash('fail', 'Le numéro ' . $equipe->getNumero() . ' est déjà attribué');
                else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }
        return $this->redirectToRoute('backoffice.equipe.edit', [
            'idEquipe' => $idEquipe
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
     * @param Equipe $equipe
     */
    public function createEquipeAndRencontres(Equipe $equipe) {
        $this->em->persist($equipe);

        /** On créé toutes les rencontres de la nouvelle équipe **/
        $journees = $equipe->getIdChampionnat()->getJournees()->toArray();
        foreach ($journees as $journee){
            $rencontre = new Rencontre($equipe->getIdChampionnat());
            $rencontre
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
     * Retourne le numéro de la prochaine équipe à créer du championnat
     * @param array $numEquipes
     * @return int|null
     */
    public function getLastNumero(array $numEquipes): ?int {
        $lastNumero = array_map(function($numero) { return $numero;}, $numEquipes);
        return $lastNumero ? max($lastNumero) + 1 : null;
    }

    /**
     * Retourne les numéros des équipes si y en a des manquantes
     * @param array $numEquipes
     * @return array
     */
    public function getAllowedNumbers(Array $numEquipes): array {
        $numEquipes = array_map(function ($numero) {
            return $numero;
        }, $numEquipes);
        $range = range(1, $numEquipes ? max($numEquipes) : 1);
        return array_diff($range, $numEquipes);
    }
}
