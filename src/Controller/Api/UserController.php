<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Factory\View\SelfUserInfoViewFactory;
use App\Factory\View\UserListViewFactory;
use App\Form\Query\UserQueryType;
use App\Model\Query\UserQuery;
use App\Model\View\User\SelfUserInfoView;
use App\Model\View\User\UserView;
use FOS\UserBundle\Model\UserManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends Controller
{
    /**
     * @SWG\Tag(name="user")
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
     * @SWG\Tag(name="user")
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
     * @SWG\Tag(name="user")
     *
     * @SWG\Parameter(
     *     name="master",
     *     in="body",
     *     description="master password",
     *     type="json",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="master",
     *             example="asdgaq34y4SD$6",
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success added pass"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized (invalid jwt)"
     * )
     *
     * @deprecated Only for demo functionality!
     *
     * @Route(
     *     path="/api/master/set",
     *     name="api_set_master",
     *     methods={"POST"}
     * )
     *
     * @param Request              $request
     * @param UserManagerInterface $userManager
     *
     * @return null
     */
    public function setMasterPasswordAction(Request $request, UserManagerInterface $userManager)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->isMasterCreated()) {
            throw new BadRequestHttpException('Master password already set');
        }

        $user->setPlainPassword($request->request->get('master'));
        $user->setMasterCreated(true);
        $userManager->updateUser($user);

        return null;
    }

    /**
     * @SWG\Tag(name="user")
     *
     * @SWG\Parameter(
     *     name="master",
     *     in="body",
     *     description="master password",
     *     type="json",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="master",
     *             example="asdgaq34y4SD$6",
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Password is correct"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized (invalid jwt)"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Forbidden (invalid master password)"
     * )
     *
     * @deprecated Only for demo functionality!
     *
     * @Route(
     *     path="/api/master/check",
     *     name="api_check_master",
     *     methods={"POST"}
     * )
     *
     * @param Request                      $request
     * @param EncoderFactoryInterface      $factory
     * @param UserPasswordEncoderInterface $encoder
     *
     * @return null
     */
    public function checkMasterPasswordAction(Request $request, EncoderFactoryInterface $factory, UserPasswordEncoderInterface $encoder)
    {
        /** @var User $user */
        $user = $this->getUser();
        $pass = $request->request->get('master');

        if (false === $encoder->isPasswordValid($user, $pass)) {
            return new Response(null, Response::HTTP_FORBIDDEN);
        }

        return null;
    }

    /**
     * @SWG\Tag(name="user")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Password created",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="created",
     *             example=true,
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @deprecated Only for demo functionality!
     *
     * @Route(
     *     path="/api/master/created",
     *     name="api_is_exist_master",
     *     methods={"POST"}
     * )
     */
    public function isSetMaster()
    {
        /** @var User $user */
        $user = $this->getUser();

        return [
            'created' => $user->isMasterCreated(),
        ];
    }
}
