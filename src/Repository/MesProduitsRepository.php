<?php

namespace App\Repository;

use App\Entity\MesProduits;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MesProduits>
 *
 * @method MesProduits|null find($id, $lockMode = null, $lockVersion = null)
 * @method MesProduits|null findOneBy(array $criteria, array $orderBy = null)
 * @method MesProduits[]    findAll()
 * @method MesProduits[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MesProduitsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MesProduits::class);
    }

//    /**
//     * @return MesProduits[] Returns an array of MesProduits objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MesProduits
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
