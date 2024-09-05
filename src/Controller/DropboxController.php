<?php

namespace App\Controller;

use Kunnu\Dropbox\Dropbox;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DropboxController extends Controller
{
    #[Route('/dropbox', name: 'dropbox')]
    public function index()
    {
        $accessToken = getenv('DROPBOX_ACCESS_TOKEN');
        $dropbox = new Dropbox($accessToken);

        $path = '/1.png';
        $temporaryLink = $dropbox->getTemporaryLink($path);

        return $this->render('dropbox/image.html.twig', [
            'temporaryLink' => $temporaryLink,
        ]);
    }
}