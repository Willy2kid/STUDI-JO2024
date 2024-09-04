<?php

namespace App\Factory;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Class OrderFactory
 * @package App\Factory
 */

class OrderFactory
{
    /**
     * @var Security
     */
    private $security;
    
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Creates an order.
     *
     * @return Order
     */
    public function create(): Order
    {
        $order = new Order();
        $order
            ->setStatus(Order::STATUS_CART)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $user = $this->security->getUser();
        if ($user !== null) {
            $order->setUser($user);
        }

        return $order;
    }

    /**
     * Creates an item for a product.
     *
     * @param Product $product
     *
     * @return OrderItem
     */
    public function createItem(Product $product, $quantity, $offer): OrderItem
    {
        $item = new OrderItem();
        $item->setProduct($product);
        $item->setQuantity($quantity);
        $item->setOffer($offer);

        return $item;
    }
}