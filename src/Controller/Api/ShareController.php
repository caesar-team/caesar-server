<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Share;
use App\Factory\View\Share\ShareViewFactory;
use App\Form\Request\CreateShareType;
use App\Form\Request\EditShareType;
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
            $share = $shareManager->updateShare($share, $form->get('email')->getData());

            return $serializer->normalize($shareViewFactory->create($share), 'array', ['groups' => 'share_create']);
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
        if (0 === $share->getSharedPosts()->count()) {
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
     * @Route("/api/share/{id}", name="api_share_delete", methods={"DELETE"})
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
     * @Route("/api/share/{id}", name="api_share_edit", methods={"PATCH"})
     *
     * @param Request                $request
     * @param Share                  $share
     * @param EntityManagerInterface $entityManager
     * @param ShareViewFactory       $shareViewFactory
     * @param SerializerInterface    $serializer
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function edit(
        Request $request,
        Share $share,
        EntityManagerInterface $entityManager,
        ShareViewFactory $shareViewFactory,
        SerializerInterface $serializer
    ) {
        $this->denyAccessUnlessGranted(ShareVoter::EDIT_SHARE, $share);

        $form = $this->createForm(EditShareType::class, $share);

        $form->submit($request->request->all());
        if ($form->isValid()) {
            $entityManager->persist($share);
            $entityManager->flush();

            return $serializer->normalize($shareViewFactory->create($share), 'array', ['groups' => 'share_create']);
        }

        return $form;
    }
}
