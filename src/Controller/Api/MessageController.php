<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Form\Request\SendMessageType;
use App\Form\Request\TemporaryMessageType;
use App\Mailer\MailRegistry;
use App\Model\DTO\TemporaryMessage;
use App\Model\Request\SendMessageRequest;
use App\Services\TemporaryMessageManager;
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
     *     @Model(type="\App\Form\Request\TemporaryMessageType")
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
     * @param Request                 $request
     * @param TemporaryMessageManager $messageManager
     *
     * @return array|FormInterface
     */
    public function createMessage(Request $request, TemporaryMessageManager $messageManager)
    {
        $message = new TemporaryMessage();
        $form = $this->createForm(TemporaryMessageType::class, $message);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $messageManager->save($message);

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
     *         @Model(type="\App\Model\DTO\TemporaryMessage")
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
     * @param string                  $id
     * @param TemporaryMessageManager $messageManager
     *
     * @return TemporaryMessage
     */
    public function showMessage(string $id, TemporaryMessageManager $messageManager)
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
     *     @Model(type="\App\Form\Request\SendMessageType")
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
        $sendRequest = new SendMessageRequest();

        $form = $this->createForm(SendMessageType::class, $sendRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $sender->send(MailRegistry::INVITE_SEND_MESSAGE, [$sendRequest->getUser()->getEmail()], [
            'url' => $sendRequest->getUrl(),
            'token' => $sendRequest->getToken(),
        ]);

        return null;
    }
}
