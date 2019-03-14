<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Form\Request\SecureMessageType;
use App\Model\DTO\SecureMessage;
use App\Services\SecureMessageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageController extends AbstractController
{

    public function createMessage(Request $request, SecureMessageManager $messageManager)
    {
        $message = new SecureMessage();
        $form = $this->createForm(SecureMessageType::class, $message);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $messageManager->save($message);

        return [
            'id' => $message->getId(),
        ];
    }

    public function showMessage(string $id, SecureMessageManager $messageManager)
    {
        $message = $messageManager->get($id);

        if (empty($message)) {
            throw new NotFoundHttpException('No such message');
        }

        return $message;
    }
}
