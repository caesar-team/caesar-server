<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Share;
use App\Entity\User;
use App\Event\EventSubscriber\ShareLinkCreatedSubscriber;
use App\Factory\View\Share\ShareViewFactory;
use App\Form\Request\BatchCreateShareType;
use App\Form\Request\BatchEditShareType;
use App\Form\Request\CreateShareType;
use App\Form\Request\UpdateShareType;
use App\Security\Voter\ShareVoter;
use App\Share\ShareManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class ShareController extends AbstractController
{
    /**
     * Create share by email.
     *
     * @SWG\Tag(name="Share")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\CreateShareType")
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Success share created",
     *     @Model(type="\App\Model\View\Share\ShareView", groups={"share_create"})
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns share errors",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @Model(type="\App\Form\Request\CreateShareType")
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route("/api/share", name="api_share_create", methods={"POST"})
     *
     * @param Request                        $request
     * @param ShareManager                   $shareManager
     * @param ShareViewFactory               $shareViewFactory
     * @param SerializerInterface|Serializer $serializer
     *
     * @return array|\Symfony\Component\Form\FormInterface
     */
    public function create(
        Request $request,
        ShareManager $shareManager,
        ShareViewFactory $shareViewFactory,
        SerializerInterface $serializer
    ) {
        $share = new Share();
        $form = $this->createForm(CreateShareType::class, $share);

        $form->submit($request->request->all());
        if ($form->isValid()) {
            $share = $shareManager->updateShare($share, $form->get('user')->getData());

            return $serializer->normalize($shareViewFactory->create($share), 'array', ['groups' => 'share_create']);
        }

        return $form;
    }

    /**
     * Batch create share by email.
     *
     * @SWG\Tag(name="Share")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\BatchCreateShareType")
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Success share created",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="\App\Model\View\Share\ShareView", groups={"share_create"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns share errors",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @Model(type="\App\Form\Request\BatchCreateShareType")
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route("/api/shares", name="api_share_batch_create", methods={"POST"})
     *
     * @param Request                        $request
     * @param ShareManager                   $shareManager
     * @param ShareViewFactory               $shareViewFactory
     * @param SerializerInterface|Serializer $serializer
     *
     * @return array|\Symfony\Component\Form\FormInterface
     */
    public function batchCreate(
        Request $request,
        ShareManager $shareManager,
        ShareViewFactory $shareViewFactory,
        SerializerInterface $serializer
    ) {
        $form = $this->createForm(BatchCreateShareType::class);

        $form->submit($request->request->all());
        if ($form->isValid()) {
            $shares = [];
            foreach ($form->get('shares') as $item) {
                $share = $shareManager->updateShare($item->getData(), $item->get('user')->getData());

                $shares[] = $serializer->normalize($shareViewFactory->create($share), 'array', ['groups' => 'share_create']);
            }

            return $shares;
        }

        return $form;
    }

    /**
     * Get share by id.
     *
     * @SWG\Tag(name="Share")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Share data",
     *     @Model(type="\App\Model\View\Share\ShareView", groups={"share_read"})
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Your access was revoked."
     * )
     *
     * @Route("/api/share/{id}", name="api_share_get", methods={"GET"})
     *
     * @param Share                          $share
     * @param ShareViewFactory               $shareViewFactory
     * @param SerializerInterface|Serializer $serializer
     * @param EntityManagerInterface         $entityManager
     *
     * @return \App\Model\View\Share\ShareView
     */
    public function share(
        Share $share,
        ShareViewFactory $shareViewFactory,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager
    ) {
        if (0 === $share->getSharedItems()->count()) {
            $entityManager->remove($share);
            $entityManager->flush();

            throw new NotFoundHttpException('Your access was revoked.');
        }

        return $serializer->normalize($shareViewFactory->create($share), 'array', ['groups' => 'share_read']);
    }

    /**
     * Delete share by id.
     *
     * @SWG\Tag(name="Share")
     * @SWG\Response(
     *     response=204,
     *     description="Success share deleted"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this share"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such share"
     * )
     *
     * @Route("/api/shares/{id}", name="api_share_delete", methods={"DELETE"})
     *
     * @param Share                  $share
     * @param EntityManagerInterface $entityManager
     *
     * @return JsonResponse
     */
    public function delete(Share $share, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(ShareVoter::DELETE_SHARE, $share);

        $entityManager->remove($share);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Edit share by id.
     *
     * @SWG\Tag(name="Share")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\EditShareType")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success share updated",
     *     @Model(type="\App\Model\View\Share\ShareView", groups={"share_edit"})
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns share errors",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @Model(type="\App\Form\Request\EditShareType")
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this share"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such share"
     * )
     *
     * @Route("/api/shares/{id}", name="api_share_edit", methods={"PATCH"})
     *
     * @param Request $request
     * @param Share $share
     * @param EntityManagerInterface $entityManager
     * @param ShareViewFactory $shareViewFactory
     * @param SerializerInterface $serializer
     *
     * @param ShareManager $shareManager
     * @return \Symfony\Component\Form\FormInterface
     */
    public function edit(
        Request $request,
        Share $share,
        EntityManagerInterface $entityManager,
        ShareViewFactory $shareViewFactory,
        SerializerInterface $serializer,
        ShareManager $shareManager
    ) {
        $this->denyAccessUnlessGranted(ShareVoter::EDIT_SHARE, $share);

        $oldLink = $share->getLink();
        $form = $this->createForm(UpdateShareType::class, $share);

        $form->submit($request->request->all());
        if ($form->isValid()) {
            $entityManager->persist($share);
            $entityManager->flush();

            $shareLink = $share->getLink();
            if ($shareLink) {
                $method = $oldLink ? ShareLinkCreatedSubscriber::METHOD_UPDATE : ShareLinkCreatedSubscriber::METHOD_CREATE;
                $shareManager->dispathLinkCreatedEvent($share, $method);
            }


            return $serializer->normalize($shareViewFactory->create($share), 'array', ['groups' => 'share_create']);
        }

        return $form;
    }

    /**
     * Batch edit shares.
     *
     * @SWG\Tag(name="Share")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\BatchEditShareType")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success shares updated",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="\App\Model\View\Share\ShareView", groups={"share_edit"})
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns share errors",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @Model(type="\App\Form\Request\BatchEditShareType")
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this share"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such share"
     * )
     *
     * @Route("/api/shares", name="api_batch_shares_edit", methods={"PATCH"})
     *
     * @param Request             $request
     * @param ShareManager        $shareManager
     * @param ShareViewFactory    $shareViewFactory
     * @param SerializerInterface $serializer
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function batchEdit(
        Request $request,
        ShareManager $shareManager,
        ShareViewFactory $shareViewFactory,
        SerializerInterface $serializer
    ) {
        $form = $this->createForm(BatchEditShareType::class);

        $form->submit($request->request->all());
        if ($form->isValid()) {
            $shares = [];
            foreach ($form->get('shares') as $item) {
                $share = $shareManager->editShare($item->get('id')->getData(), $item->getData());

                $shares[] = $serializer->normalize($shareViewFactory->create($share), 'array', ['groups' => 'share_create']);
            }

            return $shares;
        }

        return $form;
    }

    /**
     * Check share by id
     *
     * @SWG\Tag(name="Share")
     * @SWG\Response(
     *     response=200,
     *     description="The shared item exists"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such share"
     * )
     * @Route("/api/anonymous/share/{share}/check", methods={"GET"}, name="api_anonymous_share_check")
     * @param Share $share
     * @return JsonResponse
     */
    public function check(Share $share): JsonResponse
    {
        if (0 === $share->getSharedItems()->count()) {
            return new JsonResponse(['share' => $share->getId()], Response::HTTP_NOT_FOUND);
        }

        if (
            User::FLOW_STATUS_FINISHED === $share->getUser()->getFlowStatus() &&
            !$share->getUser()->hasRole(User::ROLE_ANONYMOUS_USER)
        ) {//The share link must be unavailable when user already finished flow
            return new JsonResponse(['share' => $share->getId()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['share' => $share->getId()], Response::HTTP_OK);
    }
}
