<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MaintenanceListener {

    private $maintenanceFilePath;
    private $twig;

    /**
     * MaintenanceListener constructor.
     * @param string $maintenanceFilePath
     * @param Environment $twig
     */
    public function __construct(string $maintenanceFilePath, Environment $twig)
    {
        $this->maintenanceFilePath = $maintenanceFilePath;
        $this->twig = $twig;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function onKernelRequest(RequestEvent $event) {
        if (!file_exists($this->maintenanceFilePath)) return;
        $event->setResponse(
            new Response($this->twig->render('maintenance.html.twig'), Response::HTTP_SERVICE_UNAVAILABLE)
        );
        $event->stopPropagation();
    }
}