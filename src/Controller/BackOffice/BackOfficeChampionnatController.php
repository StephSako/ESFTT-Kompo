<?php

namespace App\Controller\BackOffice;

use App\Entity\Championnat;
use App\Form\ChampionnatType;
use App\Repository\ChampionnatRepository;
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

    public function __construct(ChampionnatRepository $championnatRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/backoffice/championnats", name="back_office.championnats")
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
            if ($form->isValid()){
                try {
                    $championnat->setNom(ucwords(strtolower($championnat->getNom())));
                    $this->em->persist($championnat);
                    $this->em->flush();
                    $this->addFlash('success', 'Championnat créé avec succès !');
                    return $this->redirectToRoute('back_office.championnats');
                } catch(Exception $e){
                    if ($e->getPrevious()->getCode() == "23000"){
                        if (str_contains($e->getMessage(), 'nom')) $this->addFlash('fail', 'Le nom \'' . $championnat->getNom() . '\' est déjà attribué');
                    }
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    return $this->render('backoffice/championnat/new.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/championnat/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/championnat/edit/{idChampionnat}", name="backoffice.championnat.edit")
     * @param int $idChampionnat
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(int $idChampionnat, Request $request): Response
    {
        if (!($championnat = $this->championnatRepository->find($idChampionnat))) throw new Exception('Ce championnat est inexistant', 500);
        $form = $this->createForm(ChampionnatType::class, $championnat);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $championnat->setNom(ucwords(strtolower($championnat->getNom())));
                    $this->em->flush();
                    $this->addFlash('success', 'Championnat modifié avec succès !');
                    return $this->redirectToRoute('back_office.championnats');
                } catch(Exception $e){
                    if ($e->getPrevious()->getCode() == "23000"){
                        if (str_contains($e->getMessage(), 'nom')) $this->addFlash('fail', 'Le nom \'' . $championnat->getNom() . '\' est déjà attribué');
                    }
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    return $this->render('backoffice/edit.html.twig', [
                        'championnat' => $championnat,
                        'form' => $form->createView(),
                        'title' => 'Modifier le championnat',
                        'macro' => 'championnat',
                        'textForm' => 'Modifier'
                    ]);
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
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
     * @Route("/backoffice/championnat/delete/{idChampionnat}", name="backoffice.championnat.delete", methods="DELETE")
     * @param int $idChampionnat
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function delete(int $idChampionnat, Request $request): Response
    {
        if (!($championnat = $this->championnatRepository->find($idChampionnat))) throw new Exception('Cette championnat est inexistante', 500);

        if ($this->isCsrfTokenValid('delete' . $championnat->getIdChampionnat(), $request->get('_token'))) {
            $this->em->remove($championnat);
            $this->em->flush();
            $this->addFlash('success', 'Championnat supprimé avec succès !');
        } else $this->addFlash('error', 'Le championnat n\'a pas pu être supprimé');

        return $this->redirectToRoute('back_office.championnats');
    }
}
