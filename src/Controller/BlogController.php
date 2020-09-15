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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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
     * Ajout et modification articles
     * 
     */
    public function form(Request $request,ArticleRepository $articleRepository,$id = 0)
    {
        $article = new Article();
        $titre = 'Modification';
        $id != 0 ? $article = $articleRepository->find($id) : $titre='Ajout'; $article->setArticleDate(new \DateTime('now')) ; $article->setStatus(true);

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
            'titre' => $titre
        ]);
    }

    /**
     * @Route("/admin/article/delete", name="delete_article")
     * Suppression article
     * @Method({"POST"})
     */
    public function delete(ArticleRepository $articleRepository,Request $request)
    {
        $data = json_decode($request->getContent());
        return new JsonResponse(array('data' =>$data));
      /*  $article = $articleRepository->find($id);
        $article -> setStatus(false); 
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();
        return 'okok';*/
    }
}