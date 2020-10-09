<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Team;
use App\Factory\View\Item\ItemViewFactory;
use App\Form\Type\Request\Item\KeypairFilterRequestType;
use App\Model\View\Item\ItemView;
use App\Repository\ItemRepository;
use App\Request\Item\KeypairFilterRequest;
use App\Security\Voter\TeamVoter;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/keypairs")
 * @SWG\Response(
 *     response=401,
 *     description="Unauthorized"
 * )
 */
final class KeypairController extends AbstractController
{
    /**
     * @SWG\Tag(name="Item / Keypair")
     * @SWG\Parameter(
     *     name="type",
     *     in="query",
     *     description="Type of keypair",
     *     type="string",
     *     enum={KeypairFilterRequest::TYPE_TEAM, KeypairFilterRequest::TYPE_PERSONAL}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Item collection",
     *     @SWG\Schema(type="array", @Model(type=ItemView::class))
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns filter error"
     * )
     *
     * @Route(
     *     path="",
     *     name="api_keypairs",
     *     methods={"GET"}
     * )
     *
     * @return ItemView[]
     */
    public function list(Request $request, ItemRepository $repository, ItemViewFactory $factory): array
    {
        $filter = new KeypairFilterRequest($this->getUser());

        $form = $this->createForm(KeypairFilterRequestType::class, $filter);
        $form->submit($request->query->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $items = $repository->getKeypairsByRequest($filter);

        return $factory->createCollection($items);
    }

    /**
     * @SWG\Tag(name="Item / Keypair")
     * @SWG\Response(
     *     response=200,
     *     description="Keypair team",
     *     @Model(type=ItemView::class)
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not member of this team"
     * )
     *
     * @Route(
     *     path="/personal/{team}",
     *     name="api_keypairs_personal_team",
     *     methods={"GET"}
     * )
     */
    public function team(Team $team, ItemRepository $repository, ItemViewFactory $factory): ItemView
    {
        $this->denyAccessUnlessGranted(TeamVoter::GET_KEYPAIR, $team);

        $keypair = $repository->getKeypairsUserTeam($team, $this->getUser());
        if (null == $keypair) {
            throw new NotFoundHttpException('Not found keypair');
        }

        return $factory->createSingle($keypair);
    }
}
