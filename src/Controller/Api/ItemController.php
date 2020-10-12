<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Form\Type\Request\Item\ItemsCollectionRequestType;
use App\Request\Item\ItemsCollectionRequest;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\TeamItemVoter;
use Doctrine\ORM\EntityManagerInterface;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class ItemController extends AbstractController
{
    /**
     * Batch delete items (if items into trash list).
     *
     * @SWG\Tag(name="Item")
     * @SWG\Parameter(
     *     name="items",
     *     in="body",
     *     @Model(type=ItemsCollectionRequestType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Items deleted",
     * )
     * @Route(
     *     path="/api/items/batch",
     *     name="api_batch_delete_items",
     *     methods={"DELETE"}
     * )
     */
    public function batchDelete(Request $request, EntityManagerInterface $manager): void
    {
        //@todo @frontend candidate to refactoring, stay only request (body)
        $query = $request->query->all();
        if (empty($query)) {
            $query = $request->request->all();
        }

        $batchRequest = new ItemsCollectionRequest();

        $form = $this->createForm(ItemsCollectionRequestType::class, $batchRequest);
        $form->submit($query);
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        foreach ($batchRequest->getItems() as $item) {
            $this->denyAccessUnlessGranted([ItemVoter::DELETE, TeamItemVoter::DELETE], $item);
            if ($item->isNotDeletable()) {
                $message = $this->translator->trans('app.exception.delete_trash_only');
                throw new BadRequestHttpException($message);
            }

            $manager->remove($item);
        }

        $manager->flush();
    }

    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Response(
     *     response=204,
     *     description="Success item deleted"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns item deletion error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="array",
     *             property="errors",
     *             @SWG\Items(
     *                 type="string",
     *                 example="You can fully delete item only from trash"
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
     *     description="You are not owner of this item"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such item"
     * )
     *
     * @Route(
     *     path="/api/items/{id}",
     *     name="api_delete_item",
     *     methods={"DELETE"}
     * )
     *
     * @return null
     */
    public function deleteItem(Item $item, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted([ItemVoter::DELETE, TeamItemVoter::DELETE], $item);
        if ($item->isNotDeletable()) {
            $message = $this->translator->trans('app.exception.delete_trash_only');
            throw new BadRequestHttpException($message);
        }

        $manager->remove($item);
        $manager->flush();

        return null;
    }
}
