<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Form\FormErrorSerializer;
use App\Form\Request\ShareMessageType;
use App\Model\DTO\ShareMessage;
use App\Share\ShareMessageManager;
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
     * @param Request             $request
     * @param FormErrorSerializer $formErrorSerializer
     *
     * @return JsonResponse|Response
     */
    public function create(Request $request, FormErrorSerializer $formErrorSerializer)
    {
        $message = new ShareMessage();
        $form = $this->createForm(ShareMessageType::class, $message);

        if ($request->isMethod('POST')) {
            $messageArray = json_decode($request->getContent(), true);
            $form->submit($messageArray);

            if ($form->isSubmitted() && $form->isValid()) {
                $message = $form->getData();
                $message = $this->shareMessageManager->create($message);

                return new Response($this->shareMessageManager->serialize($message));
            }
        }

        $errors = $formErrorSerializer->getFormErrorsAsArray($form);

        return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }
}
