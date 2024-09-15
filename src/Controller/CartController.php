<?php

namespace App\Controller;

use App\Manager\CartManager;
use App\Form\CartType;
use App\Utils\MockPaymentServer;
use App\Service\QrCodeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Cookie;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(
        CartManager $cartManager,
        Security $security,
        Request $request): Response
    {
        $cart = $cartManager->getCurrentCart();
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        $oldCarts = null;
        if ($security->isGranted('IS_AUTHENTICATED')) {
            $oldCarts = $security->getUser()->getOldOrders();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $cart->setUpdatedAt(new \DateTime());
            $cartManager->save($cart);
        
            return $this->redirectToRoute('app_cart');
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
            'oldCarts' => $oldCarts,
        ]);
    }

    #[Route('/paiement', name: 'app_checkout')]
    public function checkout(
        Request $request,
        CartManager $cartManager,
        MockPaymentServer $paymentServer,
        Security $security,
        QrCodeGenerator $qrCodeGenerator)
    {
        if ($security->isGranted('IS_AUTHENTICATED')) {
            $paymentResponse = $paymentServer->handleRequest();

            if ($paymentResponse->getStatusCode() === 200) {
                $qrCodeGenerator->generateQrCode();
                $cartManager->validateCart();
                $this->addFlash('success', 'Paiement réussi.');
            }

            else {
                $this->addFlash('danger', 'Echec du paiement, veuillez réessayer.');
            }    

            return $this->redirectToRoute('app_cart');
        }

        else {
            $response = new Response();
            $response->headers->setCookie(new Cookie('checkout', true, time() + 3600 * 24 * 30, '/', '', false, true));
            $response->setStatusCode(302);
            $response->headers->set('Location', $this->generateUrl('app_login'));
            return $response;
        }
    }
}
