<?php

namespace App\Controller;

use App\Entity\User;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class ServiceController extends AbstractController
{
    /**
     * @Route("/", name="root")
     */
    public function index()
    {
        return $this->redirectToRoute('app.swagger_ui');
    }

    /**
     * @SWG\Tag(name="Service")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns application health status",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="boolean",
     *             property="database"
     *         )
     *     )
     * )
     *
     * @Route(
     *     "/api/service/status",
     *     name="status",
     *     methods={"GET"}
     * )
     *
     * @return array
     */
    public function statusAction()
    {
        return [
            'database' => $this->checkDB(),
        ];
    }

    /**
     * @SWG\Tag(name="Service")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns application health status",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="version",
     *             example="bb45d8e19ab97f104fe3f4ab0e5ab4d97b36f7c3"
     *         ),
     *         @SWG\Property(
     *             type="string",
     *             property="date",
     *             example="Tue, 22 Jan 2019 15:45:36 +0300"
     *         )
     *     )
     * )
     *
     * @Route(
     *     "/api/service/version",
     *     name="version",
     *     methods={"GET"}
     * )
     *
     * @return array
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
