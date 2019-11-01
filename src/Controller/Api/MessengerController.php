<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Message\BufferedMessage;
use App\Form\Request\Message\BufferedMessageType;
use App\Mailer\MailRegistry;
use App\Model\Request\Message\BufferedMessageRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class MessengerController extends AbstractController
{
    /**
     * @Route(path="/api/notification/buffered", methods={"POST"})
     * @param Request $request
     * @return null
     */
    public function bufferedNotice(Request $request)
    {
        $bufferedMessageRequest = new BufferedMessageRequest();
        $form = $this->createForm(BufferedMessageType::class, $bufferedMessageRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $this->dispatchMessage($bufferedMessageRequest->createBufferedMessage());

        return null;
    }
}