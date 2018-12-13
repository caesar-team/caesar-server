<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Factory\View\SelfUserInfoViewFactory;
use App\Factory\View\UserListViewFactory;
use App\Form\Query\UserQueryType;
use App\Form\Request\SaveKeysType;
use App\Model\Query\UserQuery;
use App\Model\View\User\SelfUserInfoView;
use App\Model\View\User\UserView;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @SWG\Tag(name="User")
     *
     * @SWG\Response(
     *     response=200,
     *     description="User information response",
     *     @Model(type="\App\Model\View\User\SelfUserInfoView")
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/user/self",
     *     name="api_user_get_info",
     *     methods={"GET"}
     * )
     *
     * @param SelfUserInfoViewFactory $viewFactory
     *
     * @return SelfUserInfoView|array
     */
    public function userInfoAction(SelfUserInfoViewFactory $viewFactory)
    {
        /** @var User $user */
        $user = $this->getUser();

        return $viewFactory->create($user);
    }

    /**
     * @SWG\Tag(name="User")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Users list",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="\App\Model\View\User\UserView")
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/user",
     *     name="api_users_list",
     *     methods={"GET"}
     * )
     *
     * @param Request             $request
     * @param UserListViewFactory $factory
     *
     * @return UserView[]|array|FormInterface
     */
    public function userListAction(Request $request, UserListViewFactory $factory)
    {
        $userQuery = new UserQuery($this->getUser());

        $form = $this->createForm(UserQueryType::class, $userQuery);
        $form->submit($request->query->all());
        if (!$form->isValid()) {
            return $form;
        }

        $userCollection = $this->getDoctrine()->getRepository(User::class)->getByQuery($userQuery);

        return $factory->create($userCollection);
    }

    /**
     * @SWG\Tag(name="Keys")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of user keys"
     * )
     * @SWG\Response(
     *     response=204,
     *     description="User has no keys"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/keys",
     *     name="api_keys_list",
     *     methods={"GET"}
     * )
     *
     * @return array|null
     */
    public function keyListAction()
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user->getKeys();
    }

    /**
     * @SWG\Tag(name="Keys")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\SaveKeysType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success keys update",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/keys",
     *     name="api_keys_save",
     *     methods={"POST"}
     * )
     *
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     *
     * @return FormInterface|null
     */
    public function saveKeysAction(Request $request, EntityManagerInterface $entityManager)
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(SaveKeysType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $user->setKeys($form->getData());
        $entityManager->flush();

        return null;
    }
}
