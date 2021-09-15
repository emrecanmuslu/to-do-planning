<?php

namespace App\Controller;

use App\Services\MockyProviderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/default", name="default")
     * @param MockyProviderService $mockyProviderService
     * @return Response
     */
    public function index(MockyProviderService $mockyProviderService): Response
    {
        $planList = $mockyProviderService->getPlanningTodoList();
        return $this->render('default/index.html.twig', [
            'TodoList' => $planList['list'],
            'totalWeek' => $planList['totalWeek']
        ]);
    }
}
