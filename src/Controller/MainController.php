<?php

namespace App\Controller;

use App\Entity\Config;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $config=$doctrine->getManager()->getRepository(Config::class)->findAll();

        return $this->render('main/index.html.twig', [
            'game_is_run'=>true,
            'config' => $config,
        ]);
    }
}
