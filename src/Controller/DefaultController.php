<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="root")
     */
    public function index()
    {
        return $this->redirectToRoute('app.swagger_ui');
    }

    /**
     * @Route("/api/service/status", name="status")
     */
    public function statusAction()
    {
        return [
            'database' => $this->checkDB(),
        ];
    }

    /**
     * @Route("/api/service/version", name="version")
     */
    public function versionAction()
    {
        $dir = $this->getParameter('kernel.project_dir');

        $result = shell_exec("cd $dir && git log -n 1 --pretty=format:'%H|%aD'");
        $response = [];
        [$response['version'], $response['date']] = explode('|', $result);

        return $response;
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
