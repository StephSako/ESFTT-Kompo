<?php

namespace App\Controller\BackOffice;

use App\Entity\Competiteur;
use App\Form\CompetiteurType;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\EquipeDepartementaleRepository;
use App\Repository\EquipeParisRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BackOfficeController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var JourneeDepartementaleRepository
     */
    private $journeeDepartementaleRepository;
    /**
     * @var JourneeParisRepository
     */
    private $journeeParisRepository;
    /**
     * @var DisponibiliteDepartementaleRepository
     */
    private $disponibiliteDepartementaleRepository;
    /**
     * @var DisponibiliteParisRepository
     */
    private $disponibiliteParisRepository;
    /**
     * @var CompetiteurRepository
     */
    private $competiteurRepository;
    /**
     * @var EquipeDepartementaleRepository
     */
    private $equipeDepartementaleRepository;
    /**
     * @var EquipeParisRepository
     */
    private $equipeParisRepository;

    /**
     * BackOfficeController constructor.
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param JourneeParisRepository $journeeParisRepository
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param EquipeDepartementaleRepository $equipeDepartementaleRepository
     * @param EquipeParisRepository $equipeParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                JourneeParisRepository $journeeParisRepository,
                                DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                CompetiteurRepository $competiteurRepository,
                                EquipeDepartementaleRepository $equipeDepartementaleRepository,
                                EquipeParisRepository $equipeParisRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->equipeDepartementaleRepository = $equipeDepartementaleRepository;
        $this->equipeParisRepository = $equipeParisRepository;
    }

    /**
     * @Route("/backoffice", name="back_office")
     * @return Response
     */
    public function index(){ return $this->redirectToRoute('back_office.disponibilites'); }

    /**
     * @Route("/backoffice/disponibilites", name="back_office.disponibilites")
     * @return Response
     */
    public function indexDisponibilites()
    {
        return $this->render('back_office/disponibilites.html.twig', [
            'disponibiliteDepartementales' => $this->disponibiliteDepartementaleRepository->findAllDispos(),
            'disponibiliteParis' => $this->disponibiliteParisRepository->findAllDispos(),
        ]);
    }

    /**
     * @Route("/backoffice/competiteurs", name="back_office.competiteurs")
     * @return Response
     */
    public function indexCompetiteurs()
    {
        return $this->render('back_office/competiteurs.html.twig', [
            'competiteurs' => $this->competiteurRepository->findBy([], ['nom' => 'ASC']),
        ]);
    }

    /**
     * @Route("/backoffice/equipes", name="back_office.equipes")
     * @return Response
     */
    public function indexEquipes()
    {
        return $this->render('back_office/equipes.html.twig', [
            'equipesDepartementales' => $this->equipeDepartementaleRepository->findAll(),
            'equipesParis' => $this->equipeParisRepository->findAll(),
        ]);
    }

    /**
     * @Route("/backoffice/competiteur/{id}", name="backoffice.account.edit")
     * @param Competiteur $competiteur
     * @param Request $request
     * @return Response
     */
    public function editCompetiteurAccount(Competiteur $competiteur, Request $request)
    {
        $form = $this->createForm(CompetiteurType::class, $competiteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->em->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès !');
            return $this->redirectToRoute('back_office.competiteurs');
        }

        return $this->render('security/edit.html.twig', [
            'type' => 'backoffice',
            'path' => 'backoffice.password.edit',
            'competiteur' => $competiteur,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/compte/update_password/{id}", name="backoffice.password.edit")
     * @param Competiteur $competiteur
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function updateCompetiteurPassword(Competiteur $competiteur, Request $request, UserPasswordEncoderInterface $encoder){
        $form = $this->createForm(CompetiteurType::class, $competiteur);
        $form->handleRequest($request);

        if ($request->request->get('new_password') == $request->request->get('new_password_validate')) {
            $password = $encoder->encodePassword($competiteur, $request->get('new_password'));
            $competiteur->setPassword($password);

            $this->em->flush();
            $this->addFlash('success', 'Mot de passe de l\'utilisateur modifié !');
            return $this->redirectToRoute('back_office.competiteurs');
        }
        else {
            $this->addFlash('fail', 'Les mots de passe ne correspond pas');
        }

        return $this->render('security/edit.html.twig', [
            'competiteur' => $competiteur,
            'path' => 'backoffice.password.edit',
            'form' => $form->createView()
        ]);
    }
}
