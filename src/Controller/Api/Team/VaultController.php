<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Controller\AbstractController;
use App\Factory\Entity\VaultFactory;
use App\Factory\View\Team\VaultViewFactory;
use App\Form\Request\Team\CreateVaultType;
use App\Limiter\Inspector\TeamCountInspector;
use App\Limiter\LimiterInterface;
use App\Limiter\Model\LimitCheck;
use App\Model\Request\Team\CreateVaultRequest;
use App\Model\View\Team\VaultView;
use App\Repository\TeamRepository;
use App\Security\Voter\TeamVoter;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/vault")
 * @SWG\Response(
 *     response=401,
 *     description="Unauthorized"
 * )
 */
class VaultController extends AbstractController
{
    /**
     * Create a team with keypair.
     *
     * @SWG\Tag(name="Vault")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=CreateVaultType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Create a vault (team and keypair)",
     *     @Model(type=VaultView::class)
     * )
     *
     * @Route(
     *     name="api_vault_create",
     *     methods={"POST"}
     * )
     */
    public function create(
        Request $request,
        TeamRepository $repository,
        VaultViewFactory $viewFactory,
        VaultFactory $factory,
        LimiterInterface $limiter
    ): VaultView {
        $this->denyAccessUnlessGranted(TeamVoter::CREATE, $this->getUser());

        $limiter->check([
            new LimitCheck(TeamCountInspector::class, 1),
        ]);

        $createRequest = new CreateVaultRequest($this->getUser());
        $form = $this->createForm(CreateVaultType::class, $createRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $vault = $factory->createFromRequest($createRequest);
        $repository->saveVault($vault);

        return $viewFactory->createSingle($vault);
    }
}
