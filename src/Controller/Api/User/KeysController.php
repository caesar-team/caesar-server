<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Factory\View\User\PublicUserKeyViewFactory;
use App\Factory\View\UserKeysViewFactory;
use App\Form\Type\Request\Key\PublicKeysRequestType;
use App\Form\Type\Request\User\SaveKeysRequestType;
use App\Model\View\User\PublicUserKeyView;
use App\Model\View\User\UserKeysView;
use App\Repository\UserRepository;
use App\Request\Key\PublicKeysRequest;
use App\Request\User\SaveKeysRequest;
use App\Security\Voter\UserVoter;
use App\User\UserKeysUpdater;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api")
 * @SWG\Response(
 *     response=401,
 *     description="Unauthorized"
 * )
 */
final class KeysController extends AbstractController
{
    /**
     * Get user public key.
     *
     * @SWG\Tag(name="Keys")
     * @SWG\Response(
     *     response=200,
     *     description="User public key",
     *     @Model(type=PublicUserKeyView::class)
     * )
     *
     * @Route(
     *     path="/key/{email}",
     *     name="api_user_get_public_key",
     *     methods={"GET"}
     * )
     * @Entity("user", expr="repository.findOneByEmail(email)")
     */
    public function publicKey(User $user, PublicUserKeyViewFactory $viewFactory): PublicUserKeyView
    {
        return $viewFactory->createSingle($user);
    }

    /**
     * Get users public keys by emails.
     *
     * @SWG\Tag(name="Keys")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=PublicKeysRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="User public key",
     *     @SWG\Schema(type="array", @Model(type=PublicUserKeyView::class))
     * )
     *
     * @Route(
     *     path="/key/batch",
     *     methods={"POST"}
     * )
     *
     * @return array<PublicUserKeyView>
     */
    public function batchPublicKeyAction(Request $request, PublicUserKeyViewFactory $factory, UserRepository $repository): array
    {
        $keysRequest = new PublicKeysRequest();
        $form = $this->createForm(PublicKeysRequestType::class, $keysRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        return $factory->createCollection(
            $repository->getUsersWithKeysByEmails($keysRequest->getEmails())
        );
    }

    /**
     * @SWG\Tag(name="Keys")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of user keys",
     *     @Model(type=UserKeysView::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="User has no keys"
     * )
     *
     * @Route(
     *     path="/keys",
     *     name="api_keys_list",
     *     methods={"GET"}
     * )
     *
     * @return UserKeysView|null
     */
    public function keyList(UserKeysViewFactory $viewFactory)
    {
        /** @var User $user */
        $user = $this->getUser();
        //@todo @frontend candidate to refactoring, always return View
        if (!$user->hasKeys()) {
            return null;
        }

        return $viewFactory->createSingle($user);
    }

    /**
     * Update keys.
     *
     * @SWG\Tag(name="Keys")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=SaveKeysRequestType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success keys update",
     * )
     *
     * @Route(
     *     path="/keys",
     *     name="api_keys_save",
     *     methods={"POST"}
     * )
     *
     * @throws \Exception
     */
    public function saveKeys(Request $request, UserKeysUpdater $updater): void
    {
        $saveRequest = new SaveKeysRequest($this->getUser());
        $form = $this->createForm(SaveKeysRequestType::class, $saveRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $updater->saveKeysFromRequest($saveRequest);
    }

    /**
     * Update keys for user without keys.
     *
     * @SWG\Tag(name="Keys")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=SaveKeysRequestType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success keys update",
     * )
     *
     * @Route(
     *     path="/keys/{email}",
     *     name="api_user_update_keys",
     *     methods={"POST"}
     * )
     * @Entity("user", expr="repository.findOneByEmail(email)")
     */
    public function updateKeys(Request $request, User $user, UserKeysUpdater $updater): void
    {
        $this->denyAccessUnlessGranted(UserVoter::UPDATE_KEY, $user);

        $saveRequest = new SaveKeysRequest($user);
        $form = $this->createForm(SaveKeysRequestType::class, $saveRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $updater->updateKeysFromRequest($saveRequest);
    }
}
