<?php

namespace App\Repository;

use App\Entity\ListTable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ListTable|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListTable|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListTable[]    findAll()
 * @method ListTable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListTableRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ListTable::class);
    }

     /**
      * @return ListTable[] Returns an array of ListTable objects
      */

    public function findByUser($user,$model)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.id_user = :id_user')
            ->setParameter('id_user', $user)
            ->andWhere('l.id_model = :model')
            ->setParameter('model', $model)
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?ListTable
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
