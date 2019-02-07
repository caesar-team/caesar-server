<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Item;
use App\Entity\Link;
use App\Form\Request\LinkCreateRequestType;
use App\Form\Request\LinkUpdateRequestType;
use App\Model\Request\LinkCreateRequest;
use App\Security\ItemVoter;
use App\Services\LinkManager;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends AbstractController
{
    /**
     * @SWG\Tag(name="Link")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\SecureMessageType")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success message created",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="id",
     *             example="fc2b052450a6c890ffa510c2aa735c0178c71a03"
     *         )
     *     )
     * )
     *
     * @Route(
     *     "/api/link",
     *     name="create_link",
     *     methods={"POST"}
     * )
     *
     * @param Request     $request
     * @param LinkManager $linkManager
     *
     * @return array|FormInterface
     */
    public function createMessage(Request $request, LinkManager $linkManager)
    {
        $linkRequest = new LinkCreateRequest();
        $form = $this->createForm(LinkCreateRequestType::class, $linkRequest);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $linkRequest->getItem());
        $link = $linkManager->create($linkRequest);

        return [
            'token' => $link->getId()->toString(),
        ];
    }

    /**
     * @Route(
     *     "/api/link/{id}",
     *     name="authorize_by_link",
     *     methods={"GET"}
     * )
     *
     * @param Link                     $link
     * @param JWTTokenManagerInterface $jwtManager
     *
     * @return array
     */
    public function getAuthByLink(Link $link, JWTTokenManagerInterface $jwtManager)
    {
        $user = $link->getGuestUser();
        $user->setGoogleAuthenticatorSecret(null); //TO omit 2fa

        return [
            'jwt' => $jwtManager->create($user),
        ];
    }

    /**
     * @Route(
     *     "/api/link/{id}",
     *     name="delete_link",
     *     methods={"DELETE"}
     * )
     *
     * @param Link                   $link
     * @param EntityManagerInterface $entityManager
     *
     * @return null
     */
    public function deleteLink(Link $link, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $link->getParentItem());

        $entityManager->remove($link->getGuestUser());
        $entityManager->remove($link);
        $entityManager->flush();

        return null;
    }

    /**
     * @Route(
     *     "/api/link/{id}",
     *     name="update_link",
     *     methods={"PATCH"}
     * )
     *
     * @param Link                   $link
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return null
     */
    public function updateLink(Link $link, Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $link->getParentItem());
        $item = $entityManager->getRepository(Item::class)->findByLink($link);

        $form = $this->createForm(LinkUpdateRequestType::class, $item);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $entityManager->persist($item);
        $entityManager->flush();

        return null;
    }
}
