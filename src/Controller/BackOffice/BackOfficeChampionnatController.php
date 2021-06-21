<?php

namespace App\Controller\BackOffice;

use App\Entity\Championnat;
use App\Entity\Journee;
use App\Entity\Rencontre;
use App\Form\ChampionnatType;
use App\Repository\ChampionnatRepository;
use App\Repository\JourneeRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeChampionnatController extends AbstractController
{

    private $em;
    private $championnatRepository;
    private $journeeRepository;

    /**
     * BackOfficeChampionnatController constructor.
     * @param ChampionnatRepository $championnatRepository
     * @param JourneeRepository $journeeRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(ChampionnatRepository $championnatRepository,
                                JourneeRepository $journeeRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->championnatRepository = $championnatRepository;
        $this->journeeRepository = $journeeRepository;
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
     */
    public function new(Request $request): Response
    {
        $championnat = new Championnat();
        $form = $this->createForm(ChampionnatType::class, $championnat);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            try {
                $championnat->setNom(mb_convert_case($championnat->getNom(), MB_CASE_TITLE, "UTF-8"));
                $this->em->persist($championnat);

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
        $form = $this->createForm(ChampionnatType::class, $championnat);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                $championnat->setNom(mb_convert_case($championnat->getNom(), MB_CASE_TITLE, "UTF-8"));
                $journees = $championnat->getJournees()->toArray();

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
                                ->setDomicile(true)
                                ->setHosted(false)
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
