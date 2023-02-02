<?php

namespace App\Controller;

use App\Entity\Config;
use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\Bundle\DoctrineBundle\ManagerConfigurator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/game')]

class GameController extends AbstractController
{
    #[Route('/', name: 'app_game')]
    public function game()
    {
        //lunch core.py
        fopen("../dirExchange/score.txt","w+");
        return $this->redirectToRoute("app_game_start");
    }

    #[Route('/start', name: 'app_game_start')]
    public function start(ManagerRegistry $doctrine): Response
    {
        $player=$doctrine->getManager()->getRepository(Player::class)->findPlayer()[0];

        //Sinon non player rendre en attente de joeur + stat et classement

        $config=$doctrine->getManager()->getRepository(Config::class)->findAll();
        unlink("../dirExchange/score.txt");


        return $this->render('game/index.html.twig', [
            'player' => $player,
            'config' => $config,
            'counter_value' => "0"
        ]);


    }

    #[Route('/{id}/firtupdate', name: 'app_game_firstupdate')]
    public function firstupdate(ManagerRegistry $doctrine, Player $player)
    {
        while (!file_exists("../dirExchange/score.txt")){
            usleep(10000);
        }
        $player->setStarttime((new \DateTime()));
        $entityManager = $doctrine->getManager();
        $entityManager->persist($player);
        $entityManager->flush();
        return new JsonResponse([
            'value' => 1,
            'stopped' => false
        ]);
    }


    #[Route('/{id}/update', name: 'app_game_update')]
    public function update(ManagerRegistry $doctrine, Player $player)
    {

        while (!file_exists("../dirExchange/score.txt")){
            sleep(1);
        }
        $value = file_get_contents('../dirExchange/score.txt');

        if($player->getStarttime()==null){
            $player->setStarttime((new \DateTime()));
            $entityManager = $doctrine->getManager();
            $entityManager->persist($player);
            $entityManager->flush();
        }

        if (time() - $player->getStarttime()->getTimestamp() <= $_GET["duration"]) {
            return new JsonResponse([
                'value' => $value,
                'stopped' => false,
                'time' => $player->getStarttime()->getTimestamp()-time()+$_GET["duration"]
            ]);
        }
        else {
            $player->setScore($value);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($player);
            $entityManager->flush();
            return new JsonResponse([
                'value' => $value,
                'stopped' => true,
                'time' => 0
            ]);
        }

    }

    #[Route('/{id}/stop', name: 'app_game_stop')]
    public function stop(ManagerRegistry $doctrine,Player $player): Response
    {
        sleep(5);
        return $this->redirectToRoute("app_game_start");
    }

    #[Route('/endgame', name: 'app_game_endgame')]
    public function endgame(): Response
    {
        //end core.py
        return $this->redirectToRoute("app_main");
    }
}
