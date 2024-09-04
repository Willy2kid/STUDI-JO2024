<?php

namespace App\Controller;

use App\Service\QrCodeCheck;
use App\Form\TicketVerificationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TicketCheckController extends AbstractController
{
    #[Route('/vérification', name: 'ticket_check')]
    public function index(QrCodeCheck $qrCodeCheck, Request $request)
    {
        $form = $this->createForm(TicketVerificationType::class);
        $form->handleRequest($request);

        $result = $request->get('result');

        if ($form->isSubmitted() && $form->isValid() || $request->query->has('qrCodeText')) {
            $qrCodeText = $form->isSubmitted() ? $form->get('qrCodeText')->getData() : $request->query->get('qrCodeText');
            $result = $qrCodeCheck->verifyQrCode($qrCodeText);

            return $this->redirectToRoute('ticket_check', ['result' => $result]);
        }
    
        return $this->render('ticket/index.html.twig', [
            'form' => $form->createView(),
            'result' => $result ?? null,
        ]);
    }
}