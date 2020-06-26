<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Factory\View\User\SelfUserInfoViewFactory;
use App\Model\View\User\SelfUserInfoView;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/users")
 */
final class InfoController extends AbstractController
{
    /**
     * Get self user info.
     *
     * @SWG\Tag(name="User")
     * @SWG\Response(
     *     response=200,
     *     description="User information",
     *     @Model(type=SelfUserInfoView::class)
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/self",
     *     name="api_user_get_info",
     *     methods={"GET"}
     * )
     */
    public function userInfo(SelfUserInfoViewFactory $viewFactory): ?SelfUserInfoView
    {
        return $this->getUser() instanceof User
            ? $viewFactory->createSingle($this->getUser())
            : null
        ;
    }
}
