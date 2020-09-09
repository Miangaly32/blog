<?php
// src/Controller/BlogController.php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Author;
use App\Entity\Category;
use App\Form\ArticleType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;


class BlogController extends AbstractController
{
    /**
     * @Route("/blog/{id}", name="blog_detail")
     * Page de detail d'une article
     * 
     */
    public function detail(int $id,ArticleRepository $articleRepository)
    {
        return $this->render('blog/layout.html.twig', ['article'=>$articleRepository->find($id)]);
    }


    /* ADMIN */
    /**
     * @Route("/admin/article/list", name="list_article")
     * Liste des articles
     * 
     */
    public function list(ArticleRepository $articleRepository)
    {
        return $this->render('admin/article/list.html.twig', ['articles'=>$articleRepository->findAllActive()]);
    }

     /**
     * @Route("/admin/article/form/{id}", name="form_article")
     * Ajout articles
     * 
     */
    public function form(Request $request,ArticleRepository $articleRepository,$id = 0)
    {
        $article = new Article();
        $id != 0 ? $article = $articleRepository->find($id) : $article->setArticleDate(new \DateTime('now')) ;

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('list_article');
        }

        return $this->render('admin/article/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/article/delete/{id}", name="delete_article")
     * Liste des articles
     * 
     */
    public function delete(ArticleRepository $articleRepository,$id)
    {
        $article = $articleRepository->find($id);
        $article -> setStatus(false); 
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();
        return $this->redirectToRoute('list_article');
    }
}