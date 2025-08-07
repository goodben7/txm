<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * Récupère le document le plus récent attaché à un customer par son holderId
     * 
     * @param string $holderId L'identifiant du customer
     * @return Document|null Returns a Document object or null if not found
     */
    public function findByCustomerHolderId(string $holderId): ?Document
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.holderId = :holderId')
            ->setParameter('holderId', $holderId)
            ->orderBy('d.uploadedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Récupère tous les documents attachés à un customer par son holderId
     * 
     * @param string $holderId L'identifiant du customer
     * @return Document[] Returns an array of Document objects
     */
    public function findAllByCustomerHolderId(string $holderId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.holderId = :holderId')
            ->setParameter('holderId', $holderId)
            ->orderBy('d.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Document[] Returns an array of Document objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Document
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
