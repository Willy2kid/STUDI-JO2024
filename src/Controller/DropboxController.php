<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;

class DropboxController extends AbstractController
{
    #[Route('/dropbox', name: 'dropbox')]
    public function index()
    {
        $accessToken = getenv('DROPBOX_ACCESS_TOKEN');
        $app = new DropboxApp("t03ew4kslhdea50", "lzizv35rwznpive", $accessToken);
        $dropbox = new Dropbox($dropboxApp);

        $path = '/1.png';
        $temporaryLink = $dropbox->getTemporaryLink($path);

        return $this->render('dropbox/image.html.twig', [
            'temporaryLink' => $temporaryLink,
        ]);
    }
}