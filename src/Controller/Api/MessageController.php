<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Form\Request\ShareMessageType;
use App\Form\Request\ShareSendMessageType;
use App\Mailer\MailRegistry;
use App\Model\DTO\ShareMessage;
use App\Model\Request\ShareSendMessageRequest;
use App\Share\ShareMessageManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    /**
     * @SWG\Tag(name="Invitation")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\ShareMessageType")
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
     *     "/api/message",
     *     name="message_create",
     *     methods={"POST"}
     * )
     *
     * @param Request             $request
     * @param ShareMessageManager $shareMessageManager
     *
     * @return array|FormInterface
     */
    public function createMessage(Request $request, ShareMessageManager $shareMessageManager)
    {
        $message = new ShareMessage();
        $form = $this->createForm(ShareMessageType::class, $message);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $shareMessageManager->save($message);

        return [
            'id' => $message->getId(),
        ];
    }

    /**
     * @SWG\Tag(name="Invitation")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Get message by id",
     *     @SWG\Schema(
     *         @Model(type="\App\Model\DTO\ShareMessage")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Not found message"
     * )
     *
     * @Route(
     *     "/api/message/{id}",
     *     name="get_message_by_id",
     *     methods={"GET"}
     * )
     *
     * @param string              $id
     * @param ShareMessageManager $messageManager
     *
     * @return ShareMessage
     */
    public function showMessage(string $id, ShareMessageManager $messageManager)
    {
        $message = $messageManager->get($id);

        if (empty($message)) {
            throw new NotFoundHttpException('No such message');
        }

        return $message;
    }

    /**
     * @SWG\Tag(name="Invitation")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\ShareSendMessageType")
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Success message sent"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns send mail errors",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     "/api/invitation",
     *     name="api_send_invitation",
     *     methods={"POST"}
     * )
     *
     * @param Request         $request
     * @param SenderInterface $sender
     *
     * @return FormInterface
     */
    public function sendInvitation(Request $request, SenderInterface $sender)
    {
        $sendRequest = new ShareSendMessageRequest();

        $form = $this->createForm(ShareSendMessageType::class, $sendRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $sender->send(MailRegistry::SHARE_SEND_MESSAGE, [$sendRequest->getUser()->getEmail()], [
            'message' => $sendRequest->getMessage(),
        ]);

        return null;
    }
}
