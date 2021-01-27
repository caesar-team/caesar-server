<?php

namespace App\Controller\Admin;

use App\Entity\Config;
use App\Entity\Directory\TeamDirectory;
use App\Entity\Directory\UserDirectory;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="easyadmin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->get(CrudUrlGenerator::class)->build();

        return $this->redirect(
            $routeBuilder->setController(UserCrudController::class)->generateUrl()
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Caesar App')
        ;
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->overrideTemplate('crud/field/id', 'admin/fields/_uuid.html.twig')
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Users Teams', 'far fa-address-book', UserTeam::class);
        yield MenuItem::linkToCrud('Teams', 'fas fa-users', Team::class);
        yield MenuItem::linkToCrud('Items', 'fas fa-key', Item::class);
        yield MenuItem::linkToCrud('Personal lists', 'fas fa-folder', UserDirectory::class)
            ->setController(PersonalListCrudController::class);
        yield MenuItem::linkToCrud('Team lists', 'fas fa-folder', TeamDirectory::class)
            ->setController(TeamListCrudController::class);
        yield MenuItem::linkToCrud('Config', 'fa fa-cog', Config::class)
            ->setController(ConfigCrudController::class);
    }
}
