<?php

namespace App\Repository;

use App\Entity\OTP;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OTP>
 */
class OTPRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OTP::class);
    }

    /**
     * Find a valid OTP for a user with specific type and code
     */
    public function findValidOTP(User $user, string $type, string $code): ?OTP
    {
        $now = new \DateTimeImmutable();
        
        return $this->createQueryBuilder('o')
            ->where('o.user = :user')
            ->andWhere('o.type = :type')
            ->andWhere('o.code = :code')
            ->andWhere('o.expiryDate > :now')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('code', $code)
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
