<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Post;
use App\Factory\View\CreatedPostViewFactory;
use App\Factory\View\ListTreeViewFactory;
use App\Factory\View\PostListViewFactory;
use App\Factory\View\PostViewFactory;
use App\Form\Query\PostListQueryType;
use App\Form\Request\CreatePostType;
use App\Form\Request\SharePostRequestType;
use App\Model\Query\PostListQuery;
use App\Form\Request\EditPostType;
use App\Form\Request\MovePostType;
use App\Model\Request\SharePostRequest;
use App\Model\View\CredentialsList\CreatedPostView;
use App\Model\View\CredentialsList\ListView;
use App\Model\View\CredentialsList\PostView;
use App\Security\ListVoter;
use App\Security\PostVoter;
use App\Services\SharesHandler;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class PostController extends AbstractController
{
    /**
     * @SWG\Tag(name="Post")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Full list tree with posts",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="\App\Model\View\CredentialsList\ListView")
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/list",
     *     name="api_list_tree",
     *     methods={"GET"}
     * )
     *
     * @param ListTreeViewFactory $viewFactory
     *
     * @return ListView[]
     */
    public function fullListAction(ListTreeViewFactory $viewFactory)
    {
        return $viewFactory->create($this->getUser());
    }

    /**
     * @SWG\Tag(name="Post")
     *
     * @SWG\Parameter(
     *     name="listId",
     *     in="query",
     *     description="Id of parent list",
     *     type="string"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Post collection",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="\App\Model\View\CredentialsList\PostView")
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
     *     path="/api/post",
     *     name="api_user_posts",
     *     methods={"GET"}
     * )
     *
     * @param Request             $request
     * @param PostListViewFactory $viewFactory
     *
     * @return PostView[]|FormInterface
     */
    public function postListAction(Request $request, PostListViewFactory $viewFactory)
    {
        $postListQuery = new PostListQuery();

        $form = $this->createForm(PostListQueryType::class, $postListQuery);
        $form->submit($request->query->all());

        if (!$form->isValid()) {
            return $form;
        }
        $this->denyAccessUnlessGranted(ListVoter::SHOW_POSTS, $postListQuery->list);

        $postCollection = $this->getDoctrine()->getRepository(Post::class)->getByQuery($postListQuery);

        return $viewFactory->create($postCollection);
    }

    /**
     * @SWG\Tag(name="Post")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Post data",
     *     @Model(type="\App\Model\View\CredentialsList\PostView")
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this post"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such post"
     * )
     *
     * @Route(
     *     path="/api/post/{id}",
     *     name="api_show_post",
     *     methods={"GET"}
     * )
     *
     * @param Post            $post
     * @param PostViewFactory $factory
     *
     * @return PostView
     */
    public function postShowAction(Post $post, PostViewFactory $factory)
    {
        $this->denyAccessUnlessGranted(PostVoter::SHOW_POST, $post);

        return $factory->create($post);
    }

    /**
     * @SWG\Tag(name="Post")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\CreatePostType::class)
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
     *         ),
     *         @SWG\Property(
     *             type="string",
     *             property="lastUpdated",
     *             example="Oct 19, 2018 12:08 pm",
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
     *     path="/api/post",
     *     name="api_create_post",
     *     methods={"POST"}
     * )
     *
     * @param Request                $request
     * @param EntityManagerInterface $manager
     * @param CreatedPostViewFactory $viewFactory
     *
     * @return CreatedPostView|FormInterface
     */
    public function createPostAction(Request $request, EntityManagerInterface $manager, CreatedPostViewFactory $viewFactory)
    {
        $post = new Post();
        $form = $this->createForm(CreatePostType::class, $post);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
        $this->denyAccessUnlessGranted(PostVoter::CREATE_POST, $post);

        $manager->persist($post);
        $manager->flush();

        return $viewFactory->create($post);
    }

    /**
     * @SWG\Tag(name="Post")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\MovePostType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success post moved"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns post move error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="listId",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="This value is not valid."
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
     *     description="You are not owner of list or post"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such post"
     * )
     *
     * @Route(
     *     path="/api/post/{id}/move",
     *     name="api_move_post",
     *     methods={"PATCH"}
     * )
     *
     * @param Post                   $post
     * @param Request                $request
     * @param EntityManagerInterface $manager
     *
     * @return FormInterface|JsonResponse
     */
    public function movePostAction(Post $post, Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted(PostVoter::EDIT_POST, $post);

        $form = $this->createForm(MovePostType::class, $post);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $this->denyAccessUnlessGranted(ListVoter::EDIT, $post->getParentList());

        $manager->persist($post);
        $manager->flush();

        return null;
    }

    /**
     * @SWG\Tag(name="Post")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\EditPostType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success post edited",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="lastUpdated",
     *             example="Oct 19, 2018 12:08 pm",
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns post edit error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="secret",
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
     *     description="You are not owner of post"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such post"
     * )
     *
     * @Route(
     *     path="/api/post/{id}",
     *     name="api_edit_post",
     *     methods={"PATCH"}
     * )
     *
     * @param Post          $post
     * @param Request       $request
     * @param SharesHandler $sharesHandler
     *
     * @return array|FormInterface
     */
    public function editPostAction(Post $post, Request $request, SharesHandler $sharesHandler)
    {
        $this->denyAccessUnlessGranted(PostVoter::EDIT_POST, $post);
        if (null !== $post->getOriginalPost()) {
            throw new BadRequestHttpException('Read only post. You are not owner');
        }

        $form = $this->createForm(EditPostType::class, $post);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $sharesHandler->savePostWithShares($post);

        return [
            'lastUpdated' => $post->getLastUpdated(),
        ];
    }

    /**
     * @SWG\Tag(name="Post")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\SharePostRequestType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success post shared"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns post share error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="userIds",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="This value is not valid"
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
     *     description="You are not owner of post"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such post"
     * )
     *
     * @Route(
     *     path="/api/post/{id}/share",
     *     name="api_share_post",
     *     methods={"PATCH"}
     * )
     *
     * @param Post          $post
     * @param Request       $request
     * @param SharesHandler $sharesHandler
     *
     * @return FormInterface|JsonResponse
     */
    public function sharePostAction(Post $post, Request $request, SharesHandler $sharesHandler)
    {
        $this->denyAccessUnlessGranted(PostVoter::EDIT_POST, $post);
        $sharePostRequest = new SharePostRequest($post);

        $form = $this->createForm(SharePostRequestType::class, $sharePostRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $sharesHandler->sharePost($sharePostRequest);

        return null;
    }

    /**
     * @SWG\Tag(name="Post")
     *
     * @SWG\Response(
     *     response=204,
     *     description="Success post deleted"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns post deletion error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="array",
     *             property="errors",
     *             @SWG\Items(
     *                 type="string",
     *                 example="You can fully delete post only from trash"
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
     *     description="You are not owner of this post"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such post"
     * )
     *
     * @Route(
     *     path="/api/post/{id}",
     *     name="api_delete_post",
     *     methods={"DELETE"}
     * )
     *
     * @param Post                   $post
     * @param EntityManagerInterface $manager
     */
    public function deletePostAction(Post $post, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted(PostVoter::DELETE_POST, $post);
        if (NodeEnumType::TYPE_TRASH !== $post->getParentList()->getType()) {
            throw new BadRequestHttpException('You can fully delete post only from trash');
        }

        $manager->remove($post);
        $manager->flush();

        return null;
    }

    /**
     * Get list of favourite posts.
     *
     * @SWG\Tag(name="Post")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of favourite posts"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this post"
     * )
     *
     * @Route(
     *     path="/api/posts/favorite",
     *     name="api_favorites_post",
     *     methods={"GET"}
     * )
     *
     * @param PostListViewFactory $viewFactory
     *
     * @return PostView[]|FormInterface
     */
    public function favorite(PostListViewFactory $viewFactory)
    {
        $postCollection = $this->getDoctrine()->getRepository(Post::class)->getFavoritesPosts($this->getUser());

        return $viewFactory->create($postCollection);
    }

    /**
     * Toggle favorite post.
     *
     * @SWG\Tag(name="Post")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Set favorite is on or off"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this post"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such post"
     * )
     *
     * @Route(
     *     path="/api/post/{id}/favorite",
     *     name="api_favorite_post_toggle",
     *     methods={"POST"}
     * )
     *
     * @param Post                   $post
     * @param EntityManagerInterface $entityManager
     * @param PostViewFactory        $factory
     *
     * @return PostView
     */
    public function favoriteToggle(Post $post, EntityManagerInterface $entityManager, PostViewFactory $factory)
    {
        $this->denyAccessUnlessGranted(PostVoter::SHOW_POST, $post);

        $post->setFavorite(!$post->isFavorite());
        $entityManager->persist($post);
        $entityManager->flush();

        return $factory->create($post);
    }
}
