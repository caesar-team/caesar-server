<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Audit\PostEventFactory;
use App\Entity\Audit\PostEvent;
use App\Entity\Post;
use App\Factory\View\Audit\ListPostEventViewFactory;
use App\Factory\View\Audit\PostEventViewFactory;
use App\Form\Request\AuditEventType;
use App\Model\Query\AuditEventsQuery;
use App\Model\View\Audit\PostEventView;
use App\Repository\AuditPostEventRepository;
use App\Security\PostVoter;
use App\Security\Voter\AuditPostEventVoter;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

class AuditController extends Controller
{
    /**
     * Create event by post.
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
     *     @Model(type=App\Model\View\Audit\PostEventView::class)
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
     *     path="/api/audit/post/{id}",
     *     name="api_create_audit_post_event",
     *     methods={"POST"}
     * )
     *
     * @param Post                   $post
     * @param Request                $request
     * @param EntityManagerInterface $manager
     * @param PostEventFactory       $postEventFactory
     * @param PostEventViewFactory   $postEventViewFactory
     *
     * @return PostEventView|FormInterface
     */
    public function createPostEventAction(
        Post $post,
        Request $request,
        EntityManagerInterface $manager,
        PostEventFactory $postEventFactory,
        PostEventViewFactory $postEventViewFactory
    ) {
        $this->denyAccessUnlessGranted(AuditPostEventVoter::CREATE, $post);

        $event = $postEventFactory->create($request, $post);
        $form = $this->createForm(AuditEventType::class, $event);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $manager->persist($event);
            $manager->flush();

            return $postEventViewFactory->create($event);
        }

        return $form;
    }

    /**
     * Get event of post by event id.
     *
     * @SWG\Tag(name="Audit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Success audit event created",
     *     @Model(type=App\Model\View\Audit\PostEventView::class)
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
     *     path="/api/audit/post/event/{id}",
     *     name="api_get_audit_event",
     *     methods={"GET"}
     * )
     *
     * @param PostEvent            $postEvent
     * @param PostEventViewFactory $postEventViewFactory
     *
     * @return PostEventView
     */
    public function eventAction(PostEvent $postEvent, PostEventViewFactory $postEventViewFactory)
    {
        $this->denyAccessUnlessGranted(AuditPostEventVoter::SHOW, $postEvent);

        return $postEventViewFactory->create($postEvent);
    }

    /**
     * Get list of events for posts.
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
     * @SWG\Response(
     *     response=200,
     *     description="Audit events list",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="array",
     *             property="data",
     *              @SWG\Items(
     *                  @Model(type=App\Model\View\Audit\PostEventView::class)
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
     *     path="/api/audit/events/post",
     *     name="api_list_audit_events_post",
     *     methods={"GET"}
     * )
     *
     * @param Request                  $request
     * @param AuditPostEventRepository $auditPostEventRepository
     * @param ListPostEventViewFactory $listPostEventViewFactory
     *
     * @return \App\Model\Response\PaginatedList
     */
    public function listEvent(
        Request $request,
        AuditPostEventRepository $auditPostEventRepository,
        ListPostEventViewFactory $listPostEventViewFactory
    ) {
        return $listPostEventViewFactory->create(
            $auditPostEventRepository->getEventsByQuery(
                AuditEventsQuery::fromRequest($this->getUser(), $request)
            )
        );
    }

    /**
     * Get list of events by post.
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
     * @SWG\Response(
     *     response=200,
     *     description="Audit events list",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="array",
     *             property="data",
     *              @SWG\Items(
     *                  @Model(type=App\Model\View\Audit\PostEventView::class)
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
     *     path="/api/audit/events/post/{id}",
     *     name="api_list_audit_events_by_post",
     *     methods={"GET"}
     * )
     *
     * @param Post                     $post
     * @param Request                  $request
     * @param AuditPostEventRepository $auditPostEventRepository
     * @param ListPostEventViewFactory $listPostEventViewFactory
     *
     * @return \App\Model\Response\PaginatedList
     */
    public function listByPostEvent(
        Post $post,
        Request $request,
        AuditPostEventRepository $auditPostEventRepository,
        ListPostEventViewFactory $listPostEventViewFactory
    ) {
        $this->denyAccessUnlessGranted(PostVoter::SHOW_POST, $post);
        $query = AuditEventsQuery::fromRequest($this->getUser(), $request);
        $query->setPost($post);

        return $listPostEventViewFactory->create(
            $auditPostEventRepository->getEventsByQuery($query)
        );
    }
}
