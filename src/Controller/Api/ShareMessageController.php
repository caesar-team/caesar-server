<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Form\Request\ShareMessageType;
use App\Form\Request\ShareSendMessageType;
use App\Mailer\MailRegistry;
use App\Model\DTO\ShareMessage;
use App\Model\Request\ShareSendMessageRequest;
use App\Share\ShareMessageManager;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Model\Message;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

final class ShareMessageController extends Controller
{
    private $shareMessageManager;

    public function __construct(ShareMessageManager $shareMessageManager)
    {
        $this->shareMessageManager = $shareMessageManager;
    }

    /**
     * @SWG\Tag(name="Share")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Get share message by id",
     *     @SWG\Schema(
     *         @Model(type="\App\Model\DTO\ShareMessage")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Not Found share message"
     * )
     *
     * @Route("/api/share/messages/{id}", name="messages_get", methods={"GET"})
     *
     * @param string $id
     *
     * @return JsonResponse|Response
     */
    public function show($id)
    {
        $message = $this->shareMessageManager->get($id);
        if ($message) {
            return new Response($this->shareMessageManager->serialize($message));
        }

        return new JsonResponse(['errors' => ['id' => 'Message not found']], Response::HTTP_NOT_FOUND);
    }

    /**
     * @SWG\Tag(name="Share")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\ShareMessageType")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success share message created",
     *     @SWG\Schema(
     *         @Model(type="\App\Model\DTO\ShareMessage")
     *     )
     * )
     *
     * @Route("/api/share/messages", name="messages_create", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function create(Request $request)
    {
        $message = new ShareMessage();
        $form = $this->createForm(ShareMessageType::class, $message);

        $messageArray = json_decode($request->getContent(), true);
        $form->submit($messageArray);
        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();
            $message = $this->shareMessageManager->create($message);

            return new Response($this->shareMessageManager->serialize($message));
        }

        return $form;
    }

    /**
     * Send share message to email.
     *
     * @SWG\Tag(name="Share")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\ShareSendMessageType")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success share message send"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns send mail errors",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @Model(type="\App\Form\Request\ShareSendMessageType")
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route("/api/share/messages/{id}/send", name="api_share_send_message", methods={"POST"})
     *
     * @param string          $id
     * @param Request         $request
     * @param SenderInterface $sender
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function sendMessage(string $id, Request $request, SenderInterface $sender)
    {
        $message = $this->shareMessageManager->has($id);
        if (!$message) {
            throw new NotFoundHttpException();
        }

        $sendRequest = new ShareSendMessageRequest();

        $form = $this->createForm(ShareSendMessageType::class, $sendRequest);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $sender->send(MailRegistry::SHARE_SEND_MESSAGE, [$sendRequest->getEmail()], [
                'message' => $sendRequest->getMessage(),
            ]);

            return null;
        }

        return $form;
    }
}
