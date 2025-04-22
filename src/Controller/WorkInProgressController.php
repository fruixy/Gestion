<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WorkInProgressController extends AbstractController
{
    #[Route('/work/in/progress', name: 'app_work_in_progress')]
    public function index(): Response
    {
        return $this->render('work_in_progress/index.html.twig', [
            'controller_name' => 'WorkInProgressController',
        ]);
    }
}
