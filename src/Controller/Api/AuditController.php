<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Audit\ItemEventFactory;
use App\Entity\Audit\ItemEvent;
use App\Entity\Item;
use App\Factory\View\Audit\ListItemEventViewFactory;
use App\Factory\View\Audit\ItemEventViewFactory;
use App\Form\Request\AuditEventType;
use App\Model\Query\AuditEventsQuery;
use App\Model\View\Audit\ItemEventView;
use App\Repository\AuditItemEventRepository;
use App\Security\ItemVoter;
use App\Security\Voter\AuditItemEventVoter;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

final class AuditController extends Controller
{
    /**
     * Create event by item.
     *
     * @SWG\Tag(name="Audit")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=App\Form\Request\AuditEventType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success audit event created",
     *     @Model(type=App\Model\View\Audit\ItemEventView::class)
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns event creation error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="message",
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
     *     description="Access denied"
     * )
     *
     * @Route(
     *     path="/api/audit/item/{id}",
     *     name="api_create_audit_item_event",
     *     methods={"POST"}
     * )
     *
     * @param Item                   $item
     * @param Request                $request
     * @param EntityManagerInterface $manager
     * @param ItemEventFactory       $itemEventFactory
     * @param ItemEventViewFactory   $itemEventViewFactory
     *
     * @return ItemEventView|FormInterface
     */
    public function createItemEventAction(
        Item $item,
        Request $request,
        EntityManagerInterface $manager,
        ItemEventFactory $itemEventFactory,
        ItemEventViewFactory $itemEventViewFactory
    ) {
        $this->denyAccessUnlessGranted(AuditItemEventVoter::CREATE, $item);

        $event = $itemEventFactory->create($request, $item);
        $form = $this->createForm(AuditEventType::class, $event);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $manager->persist($event);
            $manager->flush();

            return $itemEventViewFactory->create($event);
        }

        return $form;
    }

    /**
     * Get event of item by event id.
     *
     * @SWG\Tag(name="Audit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success audit event created",
     *     @Model(type=App\Model\View\Audit\ItemEventView::class)
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Not found"
     * )
     *
     * @Route(
     *     path="/api/audit/item/event/{id}",
     *     name="api_get_audit_event",
     *     methods={"GET"}
     * )
     *
     * @param ItemEvent            $itemEvent
     * @param ItemEventViewFactory $itemEventViewFactory
     *
     * @return ItemEventView
     */
    public function eventAction(ItemEvent $itemEvent, ItemEventViewFactory $itemEventViewFactory)
    {
        $this->denyAccessUnlessGranted(AuditItemEventVoter::SHOW, $itemEvent);

        return $itemEventViewFactory->create($itemEvent);
    }

    /**
     * Get list of events for items.
     *
     * @SWG\Tag(name="Audit")
     *
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     type="integer"
     * )
     *
     * @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Count items per page",
     *     type="integer"
     * )
     *
     * @SWG\Parameter(
     *     name="tab",
     *     in="query",
     *     description="Filter by type credentials (shared|personal)",
     *     type="string",
     *     enum={App\Model\Query\AuditEventsQuery::TAB_SHARED, App\Model\Query\AuditEventsQuery::TAB_PERSONAL}
     * )
     *
     * @SWG\Parameter(
     *     name="date_from",
     *     in="query",
     *     description="Filter by date from (d-m-Y | d-m-Y H:i)",
     *     type="string",
     *     format="d-m-Y | d-m-Y H:i"
     * )
     * @SWG\Parameter(
     *     name="date_to",
     *     in="query",
     *     description="Filter by date to (d-m-Y | d-m-Y H:i)",
     *     type="string",
     *     format="d-m-Y | d-m-Y H:i"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Audit events list",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="array",
     *             property="data",
     *             @SWG\Items(
     *                 @Model(type=App\Model\View\Audit\ItemEventView::class)
     *             )
     *         ),
     *         @SWG\Property(
     *             type="integer",
     *             property="total",
     *             example="10",
     *         ),
     *         @SWG\Property(
     *             type="integer",
     *             property="totalPages",
     *             example="1",
     *         )
     *     )
     *
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns event creation error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="message",
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
     *     description="Access denied"
     * )
     *
     * @Route(
     *     path="/api/audit/events/item",
     *     name="api_list_audit_events_item",
     *     methods={"GET"}
     * )
     *
     * @param Request                  $request
     * @param AuditItemEventRepository $auditItemEventRepository
     * @param ListItemEventViewFactory $listItemEventViewFactory
     *
     * @return \App\Model\Response\PaginatedList
     */
    public function listEvent(
        Request $request,
        AuditItemEventRepository $auditItemEventRepository,
        ListItemEventViewFactory $listItemEventViewFactory
    ) {
        return $listItemEventViewFactory->create(
            $auditItemEventRepository->getEventsByQuery(
                AuditEventsQuery::fromRequest($this->getUser(), $request)
            )
        );
    }

    /**
     * Get list of events by item.
     *
     * @SWG\Tag(name="Audit")
     *
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     type="integer"
     * )
     *
     * @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Count items per page",
     *     type="integer"
     * )
     * @SWG\Parameter(
     *     name="date_from",
     *     in="query",
     *     description="Filter by date from (d-m-Y | d-m-Y H:i)",
     *     type="string",
     *     format="d-m-Y | d-m-Y H:i"
     * )
     * @SWG\Parameter(
     *     name="date_to",
     *     in="query",
     *     description="Filter by date to (d-m-Y | d-m-Y H:i)",
     *     type="string",
     *     format="d-m-Y | d-m-Y H:i"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Audit events list",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="array",
     *             property="data",
     *             @SWG\Items(
     *                 @Model(type=App\Model\View\Audit\ItemEventView::class)
     *             )
     *         ),
     *         @SWG\Property(
     *             type="integer",
     *             property="total",
     *             example="10",
     *         ),
     *         @SWG\Property(
     *             type="integer",
     *             property="totalPages",
     *             example="1",
     *         )
     *     )
     *
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns event creation error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="message",
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
     *     description="Access denied"
     * )
     *
     * @Route(
     *     path="/api/audit/events/item/{id}",
     *     name="api_list_audit_events_by_item",
     *     methods={"GET"}
     * )
     *
     * @param Item                     $item
     * @param Request                  $request
     * @param AuditItemEventRepository $auditItemEventRepository
     * @param ListItemEventViewFactory $listItemEventViewFactory
     *
     * @return \App\Model\Response\PaginatedList
     */
    public function listByItemEvent(
        Item $item,
        Request $request,
        AuditItemEventRepository $auditItemEventRepository,
        ListItemEventViewFactory $listItemEventViewFactory
    ) {
        $this->denyAccessUnlessGranted(ItemVoter::SHOW_ITEM, $item);
        $query = AuditEventsQuery::fromRequest($this->getUser(), $request);
        $query->setItem($item);

        return $listItemEventViewFactory->create(
            $auditItemEventRepository->getEventsByQuery($query)
        );
    }
}
