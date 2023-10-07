<?php

namespace App\Controller\BackOffice;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeSettingsController extends AbstractController
{
    /**
     * BackOfficeChampionnatController constructor.
     */
    public function __construct()
    {
    }

    /**
     * @Route("/backoffice/settings/logs", name="backoffice.settings.logs")
     */
    public function index(): Response
    {
        $fileContent = '';
        $hasError = false;
        try {
            $fileContent = str_replace("\n", "<br><br>", (new SplFileInfo(__DIR__ . $this->getParameter('log_file_path'), '', ''))->getContents());
        } catch (Exception $e) {
            $hasError = true;
            $this->addFlash("fail", "Les logs n'ont pas pu être récupérés");
        }
        return $this->render('backoffice/settings/logs.html.twig', [
            'logs' => $fileContent,
            'hasError' => $hasError
        ]);
    }
}
