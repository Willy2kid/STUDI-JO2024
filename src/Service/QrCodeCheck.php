<?php

namespace App\Service;

// use App\Entity\User;
// use App\Entity\OrderItem;
use App\Repository\UserRepository;
use App\Repository\OrderItemRepository;
use Symfony\Component\Uid\Uuid;

class QrCodeCheck
{
    private UserRepository $userRepository;
    private OrderItemRepository $orderItemRepository;
    
    public function __construct(UserRepository $userRepository, OrderItemRepository $orderItemRepository)
    {
        $this->userRepository = $userRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function verifyQrCode($qrCodeText)
    {
        if (substr_count($qrCodeText, '_') !== 1) {
            return ['error' => 'Invalid QR code format : wrong number of "_" characters'];
        }
        $parts = explode('_', $qrCodeText);
        $userId = $parts[0];
        $ticket = $parts[1];

        if (!Uuid::isValid($userId) || !Uuid::isValid($ticket)) {
            return ['error' => 'Invalid QR code format : missing uuid'];
        }

        $user = $this->userRepository->findOneBy(['id' => $userId]);
        $orderItem = $this->orderItemRepository->findOneBy(['ticket' => $ticket]);

        if (!$user || !$orderItem) {
            return ['error' => 'Invalid QR code'];
        } else {
            $userName = $user->getUsername();
            $userFirstname = $user->getFirstname();
            $userLastname = $user->getLastname();
            $productName = $orderItem->getProduct()->getName();
            $productOffer = $orderItem->getOffer();
        }

        return [
            'error' => null,
            'userName' => $userName,
            'userFirstname' => $userFirstname,
            'userLastname' => $userLastname,
            'productName' => $productName,
            'productOffer' => $productOffer,
        ];
    }
}