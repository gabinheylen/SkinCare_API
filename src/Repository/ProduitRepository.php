<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function searchProducts(string $query): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('LOWER(p.Nom) LIKE LOWER(:query)')
            ->orWhere('LOWER(p.Marque) LIKE LOWER(:query)')
            ->orWhere('LOWER(p.Description) LIKE LOWER(:query)')
            ->orWhere('LOWER(p.Code) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $query . '%');

        return $qb->getQuery()->getArrayResult();
    }

}
