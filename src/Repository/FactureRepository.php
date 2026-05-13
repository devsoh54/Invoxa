<?php

namespace App\Repository;

use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Facture>
 */
class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    public function findAllFacturesByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.client', 'c')
            ->andWhere('c.User = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findMaxId(): ?int
    {
        return $this->createQueryBuilder('f')
            ->select('MAX(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalByUser(User $user): float
    {
        return (float) $this->createQueryBuilder('f')
            ->select('SUM(f.total)')
            ->join('f.client', 'c')
            ->where('c.User = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    public function countByStatusAndUser(string $status, User $user): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->join('f.client', 'c')
            ->where('c.User = :user')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCAParSemaineByUser(User $user): array
    {
        $results = $this->createQueryBuilder('f')
            ->select('YEAR(f.date) as annee, WEEK(f.date) as semaine, SUM(f.total) as total')
            ->join('f.client', 'c')
            ->where('c.User = :user')
            ->andWhere('f.date >= :debut')
            ->setParameter('user', $user)
            ->setParameter('debut', new \DateTimeImmutable('-12 weeks'))
            ->groupBy('annee, semaine')
            ->orderBy('annee', 'ASC')
            ->addOrderBy('semaine', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $results;
    }

    //    /**
    //     * @return Facture[] Returns an array of Facture objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Facture
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
