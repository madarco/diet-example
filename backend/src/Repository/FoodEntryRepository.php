<?php

namespace App\Repository;

use App\Entity\FoodEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FoodEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method FoodEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method FoodEntry[]    findAll()
 * @method FoodEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoodEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FoodEntry::class);
    }

    /**
     * @return FoodEntry[] Returns an array of FoodEntry objects
    */
    public function search($userId = null, \DateTime $dateFrom = null, \DateTime $dateTo = null)
    {
        $q = $this->createQueryBuilder('f');

        if ($userId) {
            $q->andWhere('f.user = :user')->setParameter('user', $userId);
        }
        if ($dateFrom) {
            $q->andWhere('f.eatDate >= :dateFrom')->setParameter('dateFrom', $dateFrom);
        }
        if ($dateTo) {
            $q->andWhere('f.eatDate <= :dateTo')->setParameter('dateTo', $dateTo);
        }

        return $q->orderBy('f.eatDate', 'DESC')
            ->setMaxResults(200)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array Returns an array with the daily stats in the given range
    */
    public function getCalories($userId, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DATE(f.eat_date) as dateDay, SUM(f.calories) as calories FROM food_entry f
            WHERE
            f.user = :userId
                AND f.skip_diet <> 1
                AND f.eat_date >= :dateFrom AND f.eat_date <= :dateTo
            GROUP BY dateDay
            ORDER BY dateDay DESC
            ';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'userId' => $userId,
            'dateFrom' => $dateFrom->format(\DateTimeInterface::ISO8601 ),
            'dateTo' => $dateTo->format(\DateTimeInterface::ISO8601 )
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $result->fetchAllAssociative();
    }

    /**
     * @return int Returns the number of entries in the timespan
     */
    public function statsEntries(\DateTime $dateFrom, \DateTime $dateTo)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT COUNT(*) as entries FROM food_entry f
            WHERE f.eat_date >= :dateFrom AND f.eat_date <= :dateTo
            ';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'dateFrom' => $dateFrom->format(\DateTimeInterface::ISO8601 ),
            'dateTo' => $dateTo->format(\DateTimeInterface::ISO8601 )
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $result->fetchOne();
    }

    /**
     * @return mixed Returns the stats
     */
    public function statsCalories(\DateTime $dateFrom, \DateTime $dateTo)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT f.user, SUM(f.calories) / 7 as calories, COUNT(f.id) as entries FROM food_entry f
            WHERE f.eat_date >= :dateFrom AND f.eat_date <= :dateTo
            GROUP BY f.user
            ';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'dateFrom' => $dateFrom->format(\DateTimeInterface::ISO8601 ),
            'dateTo' => $dateTo->format(\DateTimeInterface::ISO8601 )
        ]);

        return $result->fetchAllAssociative();
    }
}
