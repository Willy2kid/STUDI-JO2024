<?php

namespace App\Service;

use App\Storage\CartSessionStorage;
use Endroid\QrCode\Builder\BuilderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class QrCodeGenerator
{
    private $cartSessionStorage;
    private $qrCodeBuilder;
    private $kernel;
    private $security;
    private $urlGenerator;

    public function __construct(
        CartSessionStorage $cartStorage,
        BuilderInterface $qrCodeBuilder,
        Security $security,
        KernelInterface $kernel,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->cartSessionStorage = $cartStorage;
        $this->qrCodeBuilder = $qrCodeBuilder;
        $this->security = $security;
        $this->kernel = $kernel;
        $this->urlGenerator = $urlGenerator;
    }

    public function generateQrCode()
    {
        $cart = $this->cartSessionStorage->getCart();
        $userId = $this->security->getUser()->getId();
        $qrCodeDir = $this->kernel->getProjectDir() . '/public/images/qrcode/';

        if (!is_dir($qrCodeDir)) {
            mkdir($qrCodeDir, 0777, true);
        }

        foreach ($cart->getItems() as $item) {
                $item->generateTicket();
                $data = $userId . '_' . $item->getTicket();

                $verificationUrl = 'https:' . $this->urlGenerator->generate('ticket_check', ['qrCodeText' => $data], UrlGeneratorInterface::NETWORK_PATH);

                $qrCode = $this->qrCodeBuilder
                    ->size(400)
                    ->margin(20)
                    ->data($verificationUrl)
                    ->build();
                
                $fileUrl = $qrCodeDir . $item->getTicket() . '.png';
                $qrCode->saveToFile($fileUrl);
        }
    }
}