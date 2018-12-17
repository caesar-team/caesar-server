<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Share;
use App\Factory\View\Share\ShareViewFactory;
use App\Form\Request\ShareType;
use App\Share\ShareManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     *     @Model(type="\App\Form\Request\ShareType")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success share created",
     *     @Model(type="\App\Model\View\Share\ShareView", groups={"share_create"})
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
        $form = $this->createForm(ShareType::class, $share);

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
     *     description="Success share message created",
     *     @Model(type="\App\Model\View\Share\ShareView", groups={"share_read"})
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
}
