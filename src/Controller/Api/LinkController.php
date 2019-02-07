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
     *     @Model(type="App\Form\Request\LinkCreateRequestType")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success message created",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="token",
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
     * @SWG\Tag(name="Link")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success message created",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="jwt",
     *             example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1NDk1NDQwNTAsImV4cCI6MTU0OTYzMDQ1MCwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoiN2Q5ZTNiYjctMTFiNy00MTBiLWFhN2QtYjNiYWM4ZTA1N2NlIn0.cAAwihBVsaRXOzXnaFQ7u6BJhWvDzYGwLIxZEYp-8bdPMXk7v_DoooHjAvX5P_ubTXbzK_w8UZU1h0pphFA7dCAARsA0_B1MFz83HZY6EZW2Zeheg6z__89ppTwcp4SfK2Vdn9eylwtoUzu-ZT003FIdvmjdeSRY4Fdl9XpZHzO5HQE3DTydRk7zQMtRWwPZewhK9jUh9RW0AH-gYV9Uz6dTW_3EtB8pBKbFx6GmGtwbot8YftXJ7M93ZjPJIdj_Vm5cT3xRPZeb9ol2JKHCJMs1UhX5oy3pdRToyRSUnX_-GO_P3eXChGSOR3248OOXetlZzOah1iEB9owIrZFLlBGNIJP9YdeW-CW0zfgmK95hBfmlsJ6otoF2XNvw7xBfPhsmMZajtJoW1mTlCYis5hjYPv-wtpL6mrEnJkmL1Kp0sV31YXYTPj6rKP_ZYXJIn7SvN-Ebii_xy26UKC7_-PTzXlGtrbB3NXIUCHePL2nzQlR25qraKszghs1_fmosc95mx9nLOptMuNAI0rc1DjRED-FJgt0hmAIYzEcv0flfpYMGkHqA-zupT1JAiQXTbHCQA9jPuzTyJtjd02OUtugMi8qeZtbomFvGwnr6e757kHdInNa_3ABPaNB1WiDoBVHlKm1KYmdEREnOtIVuLeG7_yb9jc1Jh0-B7n91dpc"
     *         )
     *     )
     * )
     *
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
     * @SWG\Tag(name="Link")
     *
     * @SWG\Response(
     *     response=204,
     *     description="Success link deleted",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="You are not link owner",
     * )
     *
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
     * @SWG\Tag(name="Link")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="App\Form\Request\LinkUpdateRequestType")
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success link deleted",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="You are not link owner",
     * )
     *
     * @Route(
     *     "/api/link/{id}",
     *     name="update_link",
     *     methods={"PATCH"}
     * )
     *
     * @param Link                   $link
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     *
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
