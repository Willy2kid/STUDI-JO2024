<?php

namespace App\Storage;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Psr\Log\LoggerInterface;

class CartSessionStorage
{
    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * The cart repository.
     *
     * @var OrderRepository
     */
    private $cartRepository;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var string
     */
    const CART_KEY_NAME = 'cart_id';

    /**
     * CartSessionStorage constructor.
     *
     * @param RequestStack $requestStack
     * @param OrderRepository $cartRepository
     */
    public function __construct(RequestStack $requestStack, OrderRepository $cartRepository, Security $security)
    {
        $this->requestStack = $requestStack;
        $this->cartRepository = $cartRepository;
        $this->security = $security;
    }

    /**
     * Gets the cart in session.
     *
     * @return Order|null
     */
    public function getCart(): ?Order
    {
        if ($this->security->isGranted('IS_AUTHENTICATED')) {
            $userCart = $this->security->getUser()->getOrder();
            return $this->cartRepository->findOneBy([
                'id' => $userCart,
                'status' => Order::STATUS_CART
            ]);
        }
        else {
            return $this->cartRepository->findOneBy([
                'id' => $this->getCartId(),
                'status' => Order::STATUS_CART
            ]);
        }
    }

    /**
     * Sets the cart in session.
     *
     * @param Order $cart
     */
    public function setCart(Order $cart): void
    {
        $this->getSession()->set(self::CART_KEY_NAME, $cart->getId());
    }

    /**
     * Returns the cart id.
     *
     * @return int|null
     */
    public function getCartId(): ?int
    {
        return $this->getSession()->get(self::CART_KEY_NAME);
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}