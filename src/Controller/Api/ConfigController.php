<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Factory\View\ConfigViewFactory;
use App\Repository\ConfigRepository;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/config")
 */
final class ConfigController extends AbstractController
{
    /**
     * @SWG\Tag(name="Config")
     * @SWG\Response(
     *     response=200,
     *     description="Configs, returned key=>value config",
     *     @SWG\Schema(type="array", @SWG\Items(type="string"))
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns filter error"
     * )
     *
     * @Route(path="", name="api_config",  methods={"GET"})
     *
     * @return array<string, string|null>
     */
    public function list(ConfigRepository $repository, ConfigViewFactory $factory): array
    {
        return $factory->createCollection($repository->findAll());
    }
}
