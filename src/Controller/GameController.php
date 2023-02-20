<?php

namespace App\Controller;

use App\Entity\Config;
use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\Bundle\DoctrineBundle\ManagerConfigurator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/game')]

class GameController extends AbstractController
{
    #[Route('/', name: 'app_game')]
    public function game(ManagerRegistry $doctrine)
    {
        $config=$doctrine->getManager()->getRepository(Config::class)->findAll();
        $config[3]->setValue(1);
        $entityManager = $doctrine->getManager();
        $entityManager->persist( $config[3]);
        $entityManager->flush();

        //shell_exec("sudo service core stop");
        //shell_exec("sudo service snake stop");
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

        if(file_exists("../dirExchange/score.txt")){
            unlink("../dirExchange/score.txt");
        }
       // shell_exec("sudo .././core.sh");

        /*while (shell_exec("sudo systemctl is-active core")!="active" && shell_exec("sudo systemctl is-active snake")!="active"){
            usleep($config[1]->getValue()*5);
        }*/

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
        $config=$doctrine->getManager()->getRepository(Config::class)->findAll();

        while (!isset($doctrine->getManager()->getRepository(Player::class)->findPlayer()[0])){
            usleep($config[1]->getValue()*10);
        }
        return new JsonResponse([
            'stopped' => true,
        ]);
    }


    #[Route('/{id}/update', name: 'app_game_update')]
    public function update(ManagerRegistry $doctrine, Player $player)
    {
        $config=$doctrine->getManager()->getRepository(Config::class)->findAll();

        while (!file_exists("../dirExchange/score.txt")){
            usleep($config[1]->getValue()*10);
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
        $config=$doctrine->getManager()->getRepository(Config::class)->findAll();

        shell_exec("sudo service core stop");
        if(file_exists("../dirExchange/score.txt")){
            unlink("../dirExchange/score.txt");
        }
        usleep($config[1]->getValue()*500);
        return $this->redirectToRoute("app_game_start");
    }

    #[Route('/endgame', name: 'app_game_endgame')]
    public function endgame(ManagerRegistry $doctrine): Response
    {
        $config=$doctrine->getManager()->getRepository(Config::class)->findAll();
        $config[3]->setValue(0);
        $entityManager = $doctrine->getManager();
        $entityManager->persist( $config[3]);
        $entityManager->flush();
        shell_exec("sudo service core stop");
        shell_exec("sudo service snack stop");
        if(file_exists("../dirExchange/score.txt")){
            unlink("../dirExchange/score.txt");
        }
        return $this->redirectToRoute("app_main");
    }
}
