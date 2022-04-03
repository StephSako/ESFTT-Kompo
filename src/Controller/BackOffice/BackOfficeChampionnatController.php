<?php

namespace App\Controller\BackOffice;

use App\Controller\UtilController;
use App\Entity\Championnat;
use App\Entity\Journee;
use App\Entity\Rencontre;
use App\Form\ChampionnatType;
use App\Repository\ChampionnatRepository;
use App\Repository\JourneeRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FFTTApi\Exception\InvalidURIParametersException;
use FFTTApi\Exception\NoFFTTResponseException;
use FFTTApi\Exception\URIPartNotValidException;
use FFTTApi\FFTTApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeChampionnatController extends AbstractController
{

    private $em;
    private $championnatRepository;
    private $journeeRepository;
    private $utilController;

    /**
     * BackOfficeChampionnatController constructor.
     * @param ChampionnatRepository $championnatRepository
     * @param UtilController $utilController
     * @param JourneeRepository $journeeRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(ChampionnatRepository $championnatRepository,
                                UtilController $utilController,
                                JourneeRepository $journeeRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->championnatRepository = $championnatRepository;
        $this->journeeRepository = $journeeRepository;
        $this->utilController = $utilController;
    }

    /**
     * @Route("/backoffice/championnats", name="backoffice.championnats")
     */
    public function index(): Response
    {
        return $this->render('backoffice/championnat/index.html.twig', [
            'championnats' => $this->championnatRepository->findBy([], ['nom' => 'ASC'])
        ]);
    }

    /**
     * @Route("/backoffice/championnat/new", name="backoffice.championnat.new")
     * @param Request $request
     * @return Response
     * @throws InvalidURIParametersException
     * @throws NoFFTTResponseException
     * @throws URIPartNotValidException
     */
    public function new(Request $request): Response
    {
        $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
        $organismes = $this->championnatRepository->getOrganismesFormatted(
            [
                'Ligue' => $api->getOrganismes('L'),
                'Département' => $api->getOrganismes('D')
            ]);

        $championnat = new Championnat();
        $form = $this->createForm(ChampionnatType::class, $championnat, [
            'organismesOptGroup' => $organismes
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            try {
                $championnat->setNom($championnat->getNom());
                $this->em->persist($championnat);

                if ($championnat->getNbJournees() < 2) $championnat->setJ2Rule(false);

                /** On créé les n journées du championnat */
                for ($i = 0; $i < $championnat->getNbJournees(); $i++) {
                    $journee = new Journee();
                    $journee->setIdChampionnat($championnat);
                    $journee->setUndefined(true);
                    $journee->setDateJournee((new DateTime())->modify('+' . $i . ' day'));
                    $this->em->persist($journee);
                }

                $this->em->flush();
                $this->addFlash('success', 'Championnat créé');
                return $this->redirectToRoute('backoffice.championnats');
            } catch(Exception $e){
                if ($e->getPrevious()->getCode() == "23000"){
                    if (str_contains($e->getPrevious()->getMessage(), 'nom')) $this->addFlash('fail', 'Le nom \'' . $championnat->getNom() . '\' est déjà attribué');
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
                else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/new.html.twig', [
            'form' => $form->createView(),
            'title' => 'championnats',
            'macro' => 'championnat'
        ]);
    }

    /**
     * @Route("/backoffice/championnat/edit/{idChampionnat}", name="backoffice.championnat.edit", requirements={"idChampionnat"="\d+"})
     * @param int $idChampionnat
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(int $idChampionnat, Request $request): Response
    {
        if (!($championnat = $this->championnatRepository->find($idChampionnat))) {
            $this->addFlash('fail', 'Championnat inexistant');
            return $this->redirectToRoute('backoffice.championnats');
        }
        $limiteBrulage = $championnat->getLimiteBrulage();

        $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
        $organismes = $this->championnatRepository->getOrganismesFormatted(
            [
                'Ligue' => $api->getOrganismes('L'),
                'Département' => $api->getOrganismes('D')
            ]);
        $form = $this->createForm(ChampionnatType::class, $championnat, [
            'organismesOptGroup' => $organismes
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                $journees = $championnat->getJournees()->toArray();

                /** Si la limite du brûlage diminue, on recalcule tous les brûlages des joueurs */
                if ($limiteBrulage > $championnat->getLimiteBrulage()){
                    $journeesToRecalcul = array_slice($journees, 0, count($journees));

                    $nbMaxJoueurs = max(array_map(function($division) {
                        return $division->getNbJoueurs();
                    }, $championnat->getDivisions()->toArray()));

                    foreach ($journeesToRecalcul as $journee){
                        foreach ($journee->getRencontres()->toArray() as $rencontre){
                            for ($j = 0; $j < $rencontre->getIdEquipe()->getIdDivision()->getNbJoueurs(); $j++) {
                                if ($rencontre->getIdJoueurN($j)) $this->utilController->checkInvalidSelection($championnat->getLimiteBrulage(), $championnat->getIdChampionnat(), $rencontre->getIdJoueurN($j)->getIdCompetiteur(), $nbMaxJoueurs, $journee->getIdJournee());
                            }
                            $rencontre->sortComposition();
                        }
                    }
                }

                if ($championnat->getNbJournees() < 2) $championnat->setJ2Rule(false);
                $championnat->setNom($championnat->getNom());

                /** Si nbJournees diminue, on supprime les rencontres, sinon on en créé */
                if ($championnat->getNbJournees() < count($journees)){
                    for ($i = $championnat->getNbJournees(); $i < count($journees); $i++) {
                        $this->em->remove($journees[$i]);
                    }
                } else if ($championnat->getNbJournees() > count($journees)){
                    $equipes = $championnat->getEquipes()->toArray();
                    $earliestDate = $this->journeeRepository->findEarlistDate($idChampionnat);
                    for ($i = count($journees); $i < $championnat->getNbJournees(); $i++) {
                        $journee = new Journee();
                        $journee->setIdChampionnat($championnat);
                        $journee->setUndefined(true);
                        $journee->setDateJournee($earliestDate->modify('+1 day'));
                        $this->em->persist($journee);
                        $this->em->flush();

                        foreach ($equipes as $equipe){
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
                            $this->em->flush();
                        }
                    }
                }

                $this->em->flush();
                $this->addFlash('success', 'Championnat modifié');
                return $this->redirectToRoute('backoffice.championnats');
            } catch(Exception $e){
                if ($e->getPrevious()->getCode() == "23000"){
                    if (str_contains($e->getPrevious()->getMessage(), 'nom')) $this->addFlash('fail', 'Le nom \'' . $championnat->getNom() . '\' est déjà attribué');
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/edit.html.twig', [
            'championnat' => $championnat,
            'form' => $form->createView(),
            'title' => 'Modifier le championnat',
            'macro' => 'championnat',
            'textForm' => 'Modifier'
        ]);
    }

    /**
     * @Route("/backoffice/championnat/delete/{idChampionnat}", name="backoffice.championnat.delete", methods="DELETE", requirements={"idChampionnat"="\d+"})
     * @param int $idChampionnat
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function delete(int $idChampionnat, Request $request): Response
    {
        if (!($championnat = $this->championnatRepository->find($idChampionnat))) {
            $this->addFlash('fail', 'Championnat inexistant');
            return $this->redirectToRoute('backoffice.championnats');
        }

        if ($this->isCsrfTokenValid('delete' . $championnat->getIdChampionnat(), $request->get('_token'))) {
            $this->em->remove($championnat);
            $this->em->flush();
            $this->addFlash('success', 'Championnat supprimé');
        } else $this->addFlash('error', 'Le championnat n\'a pas pu être supprimé');

        return $this->redirectToRoute('backoffice.championnats');
    }
}
