<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="root")
     */
    public function index()
    {
        return $this->redirectToRoute('app.swagger_ui');
    }

    /**
     * @Route("/status", name="status")
     */
    public function statusAction()
    {
        return [
            'database' => $this->checkDB(),
        ];
    }

    /**
     * @Route("/query2json", name="query2json")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function query2Json(Request $request)
    {
        return new JsonResponse($request->query->all());
    }

    private function checkDB(): bool
    {
        try {
            $this->getDoctrine()->getRepository(User::class)->findOneBy([]);

            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
