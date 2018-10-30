<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="root")
     */
    public function index()
    {
        return $this->redirectToRoute('app.swagger_ui');
    }

    /**
     * @Route("/status", name="status")
     */
    public function statusAction()
    {
        return [
            'database' => $this->checkDB(),
        ];
    }

    private function checkDB(): bool
    {
        try {
            $this->getDoctrine()->getRepository(User::class)->findOneBy([]);

            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
