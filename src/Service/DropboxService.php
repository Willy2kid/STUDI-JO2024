<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

class DropboxService
{
    private $client;

    public function __construct(Client $client)
    {
        $this->appKey = 't03ew4kslhdea50';
        $this->appSecret = 'lzizv35rwznpive';
        $this->redirectUri = 'https://studi-jo2024-d5bf273d2fbf.herokuapp.com/dropbox/callback';
        $this->client = $client;
    }

    public function getAccessCode()
    {
        $uri = 'https://www.dropbox.com/oauth2/authorize';
        $queryParams = [
            'client_id' => $this->appKey,
            'token_access_type' => 'offline',
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
        ];

        // Rediriger l'utilisateur vers la page d'autorisation
        $redirectUri = $uri . '?' . http_build_query($queryParams);
        return new RedirectResponse($redirectUri);
    }

    public function callback(Request $request)
    {
        // Récupérer le code d'accès
        $code = $request->query->get('code');
        
        // Échanger le code d'accès contre un token d'accès
        $token = $this->getAccessToken($code);
        
        // Stocker le token d'accès dans une variable de session
        $request->getSession()->set('dropbox_access_token', $token);
        
        return new Response('Callback réussi !');
    }

    private function getAccessToken($code)
    {
        $uri = 'https://api.dropboxapi.com/oauth2/token';
        $queryParams = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ];

        $headers = [
            'Authorization: Basic ' . base64_encode($this->appKey . ':' . $this->appSecret),
        ];

        $client = new Client();
        $response = $client->post($uri, ['query' => $queryParams, 'headers' => $headers]);

        if ($response->getStatusCode() === 200) {
            $token = json_decode($response->getBody()->getContents(), true)['access_token'];
            return $token;
        } else {
            echo "Erreur lors de la récupération du token d'accès";
        }
    }
}