<?php
// src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog/{id}", name="blog_detail")
     * 
     */
    public function detail(int $id,ArticleRepository $articleRepository)
    {
        return $this->render('blog/layout.html.twig', ['article'=>$articleRepository->find($id)]);
    }
}