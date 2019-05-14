<?php

namespace App\Controller;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RecaptchaController extends AbstractController
{
    /**
     * @Route("/recaptcha", name="recaptcha_get", methods={"GET"})
     */
    public function index()
    {
        return $this->render('recaptcha/index.html.twig', [
            'controller_name' => 'RecaptchaController',
        ]);
    }

    /**
     * @Route("/recaptcha", name="recaptcha_post", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        /** @var \GuzzleHttp\Client $client */
        $client   = new Client(['base_uri' => 'https://www.google.com']);
        $token = $request->get('_token');
        $response = $client->post('/recaptcha/api/siteverify', [
            'form_params' => [
                'secret' => getenv('GOOGLE_RECAPTCHA_SECRET'),
                'response' => $token,
            ]
        ]);

        return new JsonResponse(json_decode($response->getBody()->getContents()));
    }
}
