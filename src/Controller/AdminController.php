<?php
namespace App\Controller;

use App\Repository\AuthorRepository;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     * 
     */
    public function index(ArticleRepository $articleRepository, AuthorRepository $authorRepository, CategoryRepository $categoryRepository)
    {
        // nombre d'articles - catégories - auteurs

        $nbArticle = $articleRepository->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();


        $nbCats = $authorRepository->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();


        $nbAuthors = $categoryRepository->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('admin/base.html.twig', ["nbArticle" => $nbArticle, "nbCats" => $nbCats, "nbAuthors" => $nbAuthors]);
    }
}