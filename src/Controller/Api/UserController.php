<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Srp;
use App\Entity\User;
use App\Factory\View\SecurityBootstrapViewFactory;
use App\Factory\View\SelfUserInfoViewFactory;
use App\Factory\View\UserKeysViewFactory;
use App\Factory\View\UserListViewFactory;
use App\Factory\View\UserSecurityInfoViewFactory;
use App\Form\Query\UserQueryType;
use App\Form\Request\CreateUserType;
use App\Form\Request\SaveKeysType;
use App\Model\Query\UserQuery;
use App\Model\View\User\SecurityBootstrapView;
use App\Model\View\User\SelfUserInfoView;
use App\Model\View\User\UserKeysView;
use App\Model\View\User\UserSecurityInfoView;
use App\Model\View\User\UserView;
use App\Repository\UserRepository;
use App\Services\GroupManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class UserController extends AbstractController
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
     * @return SelfUserInfoView
     */
    public function userInfoAction(SelfUserInfoViewFactory $viewFactory)
    {
        /** @var User $user */
        $user = $this->getUser();

        return $viewFactory->create($user);
    }

    /**
     * @SWG\Tag(name="Invitation")
     *
     * @SWG\Response(
     *     response=200,
     *     description="User public key",
     *     @Model(type="App\Model\View\User\UserKeysView", groups={"public"})
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/key/{email}",
     *     name="api_user_get_public_key",
     *     methods={"GET"}
     * )
     * @Entity(
     *     "user",
     *     expr="repository.findByEmail(email)"
     * )
     * @Rest\View(serializerGroups={"public"})
     *
     * @param User                $user
     * @param UserKeysViewFactory $viewFactory
     *
     * @return UserKeysView
     */
    public function publicKeyAction(User $user, UserKeysViewFactory $viewFactory)
    {
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
     *     description="List of user keys",
     *     @Model(type="\App\Model\View\User\UserKeysView", groups={"key_detail_read"})
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
     * @Rest\View(serializerGroups={"key_detail_read"})
     *
     * @param UserKeysViewFactory $viewFactory
     *
     * @return UserKeysView|null
     */
    public function keyListAction(UserKeysViewFactory $viewFactory)
    {
        /** @var User $user */
        $user = $this->getUser();

        return $viewFactory->create($user);
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

        $form = $this->createForm(SaveKeysType::class, $user);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        /** @var User $oldUser */
        $oldUser = $entityManager->getUnitOfWork()->getOriginalEntityData($user);
        if ($user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            $user->setFlowStatus(User::FLOW_STATUS_FINISHED);

        } else {
            $this->setFlowStatusByPrivateKeys($oldUser, $user);
        }

        $entityManager->flush();

        return null;
    }

    /**
     * @SWG\Tag(name="Invitation")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\CreateUserType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success user created update",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="user",
     *             example="553d9b8d-fce0-4a53-8cba-f7d334160bc4"
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/user",
     *     name="api_user_create",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     *
     * @param GroupManager $groupManager
     * @return array|FormInterface
     * @throws \Exception
     */
    public function createUser(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        GroupManager $groupManager
    )
    {
        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => $request->request->get('email')]);
        if (!$user) {
            $user = new User(new Srp());
        } elseif (null !== $user->getPublicKey()) {
            throw new BadRequestHttpException('User already exists');
        }

        $form = $this->createForm(CreateUserType::class, $user);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        if (!$user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            $groupManager->addGroupToUser($user);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return [
            'user' => $user->getId()->toString(),
        ];
    }

    /**
     * @SWG\Tag(name="Security")
     *
     * @SWG\Response(
     *     response=200,
     *     description="User's permissions",
     *     @Model(type="\App\Model\View\User\UserSecurityInfoView")
     * )
     * )
     *
     * @SWG\Response(
     *     response=401,
     *     description="Access denied"
     * )
     *
     * @Route(
     *     path="/api/user/permissions",
     *     name="api_user_permissions",
     *     methods={"GET"}
     * )
     *
     * @param UserSecurityInfoViewFactory $infoViewFactory
     * @return JsonResponse
     */
    public function permissions(UserSecurityInfoViewFactory $infoViewFactory): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return new JsonResponse($infoViewFactory->create($user));
    }

    /**
     * @SWG\Tag(name="Security")
     *
     * @SWG\Response(
     *     response=200,
     *     description="User's security bootstrap",
     *     @Model(type="\App\Model\View\User\SecurityBootstrapView")
     * )
     * )
     *
     * @SWG\Response(
     *     response=401,
     *     description="Access denied"
     * )
     *
     * @Route(
     *     path="/api/user/security/bootstrap",
     *     name="api_user_security_bootstrap",
     *     methods={"GET"}
     * )
     *
     * @param SecurityBootstrapViewFactory $bootstrapViewFactory
     * @return JsonResponse
     */
    public function bootstrap(SecurityBootstrapViewFactory $bootstrapViewFactory): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return new JsonResponse($bootstrapViewFactory->create($user));
    }

    private function setFlowStatus(string $currentFlowStatus): string
    {
        if (User::FLOW_STATUS_CHANGE_PASSWORD === $currentFlowStatus) {
            return $currentFlowStatus;
        }

        return User::FLOW_STATUS_FINISHED;
    }

    private function setFlowStatusByPrivateKeys($oldUser, User $user)
    {
        if ($oldUser['encryptedPrivateKey'] !== $user->getEncryptedPrivateKey()) {
            $user->setFlowStatus($this->setFlowStatus($user->getFlowStatus()));
        }
    }
}
