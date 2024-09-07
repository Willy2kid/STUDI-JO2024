<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\DropboxService;

class DropboxCallbackController extends AbstractController
{
    #[Route('/dropbox/callback', name: 'dropbox_callback')]
    public function callback(DropboxService $dropboxService): Response
    {
        $dropboxService->callback();
        // return $dropboxService->callback($request);
    }
}