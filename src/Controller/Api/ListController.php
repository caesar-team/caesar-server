<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Directory;
use App\Form\Request\CreateListType;
use App\Form\Request\EditListType;
use App\Model\View\Error\SingleError;
use App\Security\ListVoter;
use App\Services\PostDisplacer;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends Controller
{
    /**
     * @SWG\Tag(name="list")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\CreateListType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success post created",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="id",
     *             example="f553f7c5-591a-4aed-9148-2958b7d88ee5",
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns list creation error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="label",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="List with such label aleady exist"
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
     *     description="You are not owner of this list"
     * )
     *
     * @Route(
     *     path="/api/list",
     *     name="api_create_list",
     *     methods={"POST"}
     * )
     *
     * @param Request                $request
     * @param EntityManagerInterface $manager
     *
     * @return FormInterface|JsonResponse
     */
    public function createListAction(Request $request, EntityManagerInterface $manager)
    {
        $list = new Directory();
        $form = $this->createForm(CreateListType::class, $list);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
        $this->denyAccessUnlessGranted(ListVoter::EDIT, $list->getParentList());

        $manager->persist($list);
        $manager->flush();

        return JsonResponse::create(['id' => $list->getId()->toString()]);
    }

    /**
     * @SWG\Tag(name="list", description="Edit list")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\EditListType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success list edited",
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns list creation error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="label",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="List with such label already exist"
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
     *     description="You are not owner of this list"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such list"
     * )
     *
     *
     * @Route(
     *     path="/api/list/{id}",
     *     name="api_edit_list",
     *     methods={"PATCH"}
     * )
     *
     * @param Directory              $list
     * @param Request                $request
     * @param EntityManagerInterface $manager
     *
     * @return SingleError|FormInterface|JsonResponse
     */
    public function editListAction(Directory $list, Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted(ListVoter::EDIT, $list);
        if (null === $list->getParentList()) { //root list
            throw new BadRequestHttpException('You can`t edit root list');
        }

        $form = $this->createForm(EditListType::class, $list);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $manager->persist($list);
        $manager->flush();

        return null;
    }

    /**
     * @SWG\Tag(name="list")
     *
     * @SWG\Response(
     *     response=204,
     *     description="Success list deleted"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns list deletion error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="array",
     *             property="errors",
     *             @SWG\Items(
     *                 type="string",
     *                 example="You can`t delete root list"
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
     *     description="You are not owner of this list"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such list"
     * )
     *
     * @Route(
     *     path="/api/list/{id}",
     *     name="api_delete_list",
     *     methods={"DELETE"}
     * )
     *
     * @param Directory              $list
     * @param PostDisplacer          $postDisplacer
     * @param EntityManagerInterface $manager
     *
     * @return null
     */
    public function deleteListAction(Directory $list, PostDisplacer $postDisplacer, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted(ListVoter::DELETE_LIST, $list);

        if (null === $list->getParentList()) { //root list
            throw new BadRequestHttpException('You can`t delete root list');
        }

        $postDisplacer->moveChildPostsToTrash($list, $this->getUser());

        $manager->remove($list);
        $manager->flush();

        return null;
    }
}
