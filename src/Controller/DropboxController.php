<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

class DropboxController extends AbstractController
{
    #[Route('/dropbox', name: 'dropbox')]
    public function index(Request $request)
    {
        if (ENV == 'prod') {
            $accessToken = getenv('DROPBOX_ACCESS_TOKEN');
            $dropboxApp = new DropboxApp("t03ew4kslhdea50", "lzizv35rwznpive", $accessToken);
            $dropbox = new Dropbox($dropboxApp);

            $path = '/1.png';
            $temporaryLink = $dropbox->getTemporaryLink($path);

            if ($request->getMethod() === 'POST') {
                $file = $request->files->get('image');
                $dropboxFile = new DropboxFile($file->getPathname());
                $uploadedFile = $dropbox->upload($dropboxFile, "/images/{$file->getClientOriginalName()}", ['mode' => 'overwrite']);

                return new Response("Fichier uploadé avec succès !");
            }
        }

        return $this->render('dropbox/image.html.twig', [
            // 'temporaryLink' => $temporaryLink,
            'temporaryLink' => $temporaryLink->link ?? null,
        ]);
    }
}