<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\DropboxService;

class DropboxCallbackController extends Controller
{
    private $dropboxService;

    public function __construct(DropboxService $dropboxService)
    {
        $this->dropboxService = $dropboxService;
    }

    #[Route('/dropbox/callback', name: 'dropbox_callback')]
    public function callback(Request $request)
    {
        return $this->dropboxService->callback($request);
    }
}