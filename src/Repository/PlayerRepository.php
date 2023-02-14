<?php

namespace App\Repository;

use App\Entity\Player;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 *
 * @method Player|null find($id, $lockMode = null, $lockVersion = null)
 * @method Player|null findOneBy(array $criteria, array $orderBy = null)
 * @method Player[]    findAll()
 * @method Player[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function save(Player $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Player $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Player[] Returns an array of Player objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Player
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @throws NonUniqueResultException
     */
    public function findPlayer()
    {
        return $this->createQueryBuilder('p')
            ->where('p.score is NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findLast()
    {
        return $this->createQueryBuilder('p')
            ->where("p.starttime is not NULL")
            ->orderBy("p.starttime","DESC")
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function find5maxPlayer()
    {
        return $this->createQueryBuilder('p')
            ->where("p.score is not NULL")
            ->orderBy("p.score","DESC")
            ->getQuery()
            ->setMaxResults(5)
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function SumScore(DateTime $date, $config)
    {
        return $this->createQueryBuilder('p')
            ->where('p.starttime BETWEEN :startDate AND :endDate OR 1 =:conf')
            ->setParameter('startDate', $date->format('Y-m-d 00:00:00'))
            ->setParameter('endDate', $date->format('Y-m-d 23:59:59'))
            ->setParameter('conf', $config)
            ->select('SUM(p.score) as dayScore')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function FindRank($value, DateTime $date, $config)
    {
        return $this->createQueryBuilder('p')
            ->where('p.score >:val AND (p.starttime BETWEEN :startDate AND :endDate OR 1 =:conf)')
            ->setParameter('startDate', $date->format('Y-m-d 00:00:00'))
            ->setParameter('endDate', $date->format('Y-m-d 23:59:59'))
            ->setParameter('conf', $config)
            ->setParameter('val', $value)
            ->select('COUNT(p)+1 as rank')
            ->getQuery()
            ->getResult();

    }

    /**
     * @throws NonUniqueResultException
     */
    public function findRanking()
    {
        return $this->createQueryBuilder('p')
            ->orderBy("p.score","DESC")
            ->getQuery()
            ->getResult();
    }

}
