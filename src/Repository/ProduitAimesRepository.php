<?php

namespace App\Repository;

use App\Entity\ProduitAimes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProduitAimes>
 *
 * @method ProduitAimes|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProduitAimes|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProduitAimes[]    findAll()
 * @method ProduitAimes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitAimesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProduitAimes::class);
    }

//    /**
//     * @return ProduitAimes[] Returns an array of ProduitAimes objects
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

//    public function findOneBySomeField($value): ?ProduitAimes
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
