<?php
namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     * 
     */
    public function index(ArticleRepository $articleRepository,AuthorRepository $authorRepository,CategoryRepository $categoryRepository)
    {
        return $this->render('admin/base.html.twig', [
            'nbArticles' => $articleRepository->countArticles(),
            'nbCategories' => $categoryRepository->countCategories(),
            'nbAuthors' => $authorRepository->countAuthors()
        ]);
    }

}