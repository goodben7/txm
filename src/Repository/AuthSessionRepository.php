<?php

namespace App\Repository;

use App\Entity\AuthSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuthSession>
 */
class AuthSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthSession::class);
    }

    public function findValidSession(string $phone, string $otp): ?AuthSession
    {
        return $this->createQueryBuilder('s')
            ->where('s.phone = :phone')
            ->andWhere('s.otpCode = :otp')
            ->andWhere('s.expiresAt > :now')
            ->andWhere('s.isValidated = false')
            ->setParameter('phone', $phone)
            ->setParameter('otp', $otp)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    //    /**
    //     * @return AuthSession[] Returns an array of AuthSession objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?AuthSession
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
