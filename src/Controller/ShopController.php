<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AddToCartType;
use App\Manager\CartManager;
use App\Factory\OrderFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

class ShopController extends AbstractController
{
    #[Route('/shop', name: 'shop')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        CartManager $cartManager,
        OrderFactory $orderFactory
        ): Response
        {
        $products = $productRepository->findAll();
        $forms = [];

        foreach ($products as $product) {
            $form = $this->createForm(AddToCartType::class);
            $form->get('productId')->setData($product->getId());
            $forms[$product->getId()] = $form->createView();
        }

        $cart = $cartManager->getCurrentCart();

        return $this->render('shop/index.html.twig', [
            'products' => $products,
            'forms' => $forms,
            'cart' => $cart,
        ]);
    }

    #[Route('/update-cart', name: 'update_cart')]
    public function updateCart(
        Request $request,
        ProductRepository $productRepository,
        CartManager $cartManager,
        OrderFactory $orderFactory,)
    {
        $formData = $request->request->all();

        $productId = $formData['add_to_cart']['productId'];
        $product = $productRepository->findOneBy(['id' => $productId]);
        $offer = $formData['offer'];
        $quantities = ['solo' => 1,'duo' => 2,'famille' => 4,];
        $quantity = $quantities[$offer]?? 0;

        $item = $orderFactory->createItem($product, $quantity, $offer);
        $cart = $cartManager->getCurrentCart();
        $cart
            ->addItem($item)
            ->setUpdatedAt(new \DateTime());
        $cartManager->save($cart);

        $cartData = $cart->getCartData();

        return new JsonResponse($cartData);
    }
}