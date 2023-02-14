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
        $config=$doctrine->getManager()->getRepository(Config::class)->findAll();

        $playerRankng=$doctrine->getManager()->getRepository(Player::class)->find5maxPlayer();

        if (isset($doctrine->getManager()->getRepository(Player::class)->findLast()[0])){
            $lastPlayer=$doctrine->getManager()->getRepository(Player::class)->findLast()[0];
            $lastRank=$doctrine->getManager()->getRepository(Player::class)->FindRank($lastPlayer->getScore(),(new \DateTime()),$config[2]->getValue())[0]["rank"];
        }
        else{
            $lastPlayer= new Player();
            $lastRank=0;
        }

        $dayScore=$doctrine->getManager()->getRepository(Player::class)->SumScore((new \DateTime()),$config[2]->getValue())[0]["dayScore"];

        if (isset($doctrine->getManager()->getRepository(Player::class)->findPlayer()[0])){
            $player=$doctrine->getManager()->getRepository(Player::class)->findPlayer()[0];
        }
        else{
            return $this->render('game/wait.html.twig', [
                'config' => $config,
                'playerRankng' => $playerRankng,
                'lastPlayer' => $lastPlayer,
                'lastRank' => $lastRank,
                'dayScore' => $dayScore,
                'counter_value' => "0"
            ]);
        }


        unlink("../dirExchange/score.txt");

        return $this->render('game/index.html.twig', [
            'player' => $player,
            'config' => $config,
            'playerRankng' => $playerRankng,
            'lastPlayer' => $lastPlayer,
            'lastRank' => $lastRank,
            'dayScore' => $dayScore,
            'counter_value' => "0"
        ]);
    }

    #[Route('/wait', name: 'app_game_wait')]
    public function wait(ManagerRegistry $doctrine)
    {
        while (!isset($doctrine->getManager()->getRepository(Player::class)->findPlayer()[0])){
            usleep(10000);
        }
        return new JsonResponse([
            'stopped' => true,
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
            if (!ctype_digit($value)) {
                $value=0;
            }
            return new JsonResponse([
                'value' => $value,
                'stopped' => false,
                'time' => $player->getStarttime()->getTimestamp()-time()+$_GET["duration"]
            ]);
        }
        else {
            if (!ctype_digit($value)) {
                $player->setScore(0);
                $value=0;
            }
            else{
                $player->setScore($value);
            }
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
