<?php
// src/Controller/BlogController.php
namespace App\Controller;

use App\Entity\Article;
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
        return $this->render('admin/article/list.html.twig', ['articles'=>$articleRepository->findAll()]);
    }

     /**
     * @Route("/admin/article/add", name="add_article")
     * Ajout articles
     * 
     */
    public function add(Request $request)
    {
        $article = new Article();
        $article->setTitle('Nouvel article');
        $article->setArticleDate(new \DateTime('now'));

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$article` variable has also been updated
            $article = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($task);
            // $entityManager->flush();

            return $this->redirectToRoute('list_article');
        }

        return $this->render('admin/article/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}