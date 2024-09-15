<?php

namespace App\Repository;

use App\Entity\OrderItem;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderItem>
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    public function deleteOrderItemsById(int $id)
    {
        $queryBuilder = $this->createQueryBuilder('oi');
        $queryBuilder->delete()
            ->where('oi.product = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    public function countOrderItemsByProduct()
    {
        $qb = $this->createQueryBuilder('oi')
            ->select('p.id, SUM(oi.quantity) as count')
            ->join('oi.product', 'p')
            ->join('oi.orderRef', 'o')
            ->where('o.status = :status')
            ->setParameter('status', Order::STATUS_PAID)
            ->groupBy('p.id');
    
        return $qb->getQuery()->getResult();
    }
}
