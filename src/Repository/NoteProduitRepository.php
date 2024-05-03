<?php

namespace App\Repository;

use App\Entity\NoteProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NoteProduit>
 *
 * @method NoteProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method NoteProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method NoteProduit[]    findAll()
 * @method NoteProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NoteProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NoteProduit::class);
    }

//    /**
//     * @return NoteProduit[] Returns an array of NoteProduit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?NoteProduit
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
