<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Form\Request\EditItemRequestType;
use App\Model\Request\EditItemRequest;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\TeamItemVoter;
use App\Services\ChildItemActualizer;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route(path="/api/items")
 */
final class UpdateController extends AbstractController
{
    /**
     * Edit item.
     *
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\EditItemRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success item edited",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="lastUpdated",
     *             example="Oct 19, 2018 12:08 pm",
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns item edit error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="secret",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="This value is empty"
     *                 )
     *             )
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of item"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such item"
     * )
     *
     * @Route(
     *     path="/{id}",
     *     name="api_edit_item",
     *     methods={"PATCH"}
     * )
     *
     * @return array|FormInterface
     */
    public function edit(
        Item $item,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ChildItemActualizer $itemHandler
    ) {
        $this->denyAccessUnlessGranted([ItemVoter::EDIT, TeamItemVoter::EDIT], $item);

        /** @var EditItemRequest $itemRequest */
        $itemRequest = $serializer->deserialize($request->getContent(), EditItemRequest::class, 'json');
        $item->setSecret($itemRequest->getItem()->getSecret());

        $form = $this->createForm(EditItemRequestType::class, $itemRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $entityManager->persist($item);
        if ($itemRequest->getOriginalItem()->getSecret() && $item->getOriginalItem()) {
            $itemHandler->updateItem($item->getOriginalItem(), $itemRequest->getOriginalItem()->getSecret(), $this->getUser());
        }
        $entityManager->flush();

        return [
            'lastUpdated' => $item->getLastUpdated(),
        ];
    }
}
