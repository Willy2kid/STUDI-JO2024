<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\DropboxService;

class DropboxCallbackController extends AbstractController
{
    private $dropboxService;

    public function __construct(DropboxService $dropboxService)
    {
        $this->dropboxService = $dropboxService;
    }

    #[Route('/dropbox/callback', name: 'dropbox_callback')]
    public function index(Request $request)
    {
        $this->dropboxService->callback();
        return $this->dropboxService->callback($request);
    }
}