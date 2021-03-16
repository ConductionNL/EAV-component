<?php

namespace App\Repository;

use App\Entity\ObjectCommunication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ObjectCommunication|null find($id, $lockMode = null, $lockVersion = null)
 * @method ObjectCommunication|null findOneBy(array $criteria, array $orderBy = null)
 * @method ObjectCommunication[]    findAll()
 * @method ObjectCommunication[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObjectCommunicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ObjectCommunication::class);
    }

    // /**
    //  * @return ObjectCommunication[] Returns an array of ObjectCommunication objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ObjectCommunication
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
