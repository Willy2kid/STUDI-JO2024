<?php

namespace App\Controller;

use App\Manager\CartManager;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\SecurityBundle\Security;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/login_in_progress', name: 'login_in_progress')]
    public function loginInProgress(
        CartManager $cartManager,
        OrderRepository $orderRepository,
        Security $security,
        Request $request,): Response
    {
        $security->getUser()->setIsVerified(true);

        if ($guestCartId = $request->cookies->get('cart_id')?? null)
        {
            $guestCart = $orderRepository->findOneBy(['id' => $guestCartId]);
            $userCart = $cartManager->getCurrentCart();

            if ($guestCart !== null) {
                foreach ($guestCart->getItems() as $item) {
                    $userCart
                        ->addItem($item)
                        ->setUpdatedAt(new \DateTime());
                }
            }

            $cartManager->save($userCart);
            $response = new Response();
            $response->headers->clearCookie('cart_id');
        }

        if ($request->cookies->has('checkout'))
        {
            $response = new Response();
            $response->headers->clearCookie('checkout'); 
            return $this->redirectToRoute('app_cart');
        }
        else {return $this->redirectToRoute('home');  }
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on the firewall');
    }
}
