<?php
// src/Controller/MainController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage(ArticleRepository $articleRepository)
    {
        return $this->render('base.html.twig', ['articles' => $articleRepository->findAllActive()]);
    }
}