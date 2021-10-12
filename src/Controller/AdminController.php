<?php
namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private ArticleRepository $articleRepository;
    private AuthorRepository $authorRepository;
    private CategoryRepository $categoryRepository;

    public function __construct(ArticleRepository $articleRepository,AuthorRepository $authorRepository,CategoryRepository $categoryRepository)
    {
        $this->articleRepository = $articleRepository;
        $this->authorRepository = $authorRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/admin", name="admin")
     * 
     */
    public function index()
    {
        return $this->render('admin/base.html.twig', [
            'nbArticles' => $this->articleRepository->countArticles(),
            'nbCategories' => $this->categoryRepository->countCategories(),
            'nbAuthors' => $this->authorRepository->countAuthors()
        ]);
    }

    /**
     * @Route("/admin/archive", name="list_archive")
     * Liste des articles archivés
     *
     */
    public function listArchive(ArticleRepository $articleRepository,CategoryRepository $categoryRepository,AuthorRepository $authorRepository)
    {
        return $this->render('admin/archive.html.twig', [
            'articles' => $this->articleRepository->findBy(['status'=>false]),
            'categories' => $this->categoryRepository->findBy(['status'=>false]),
            'authors' => $this->authorRepository->findBy(['status'=>false])
        ]);
    }
}