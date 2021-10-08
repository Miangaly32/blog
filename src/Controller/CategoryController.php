<?php
// src/Controller/CategoryController.php
namespace App\Controller;

use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;
use App\Form\CategoryType;
use Symfony\Component\HttpFoundation\JsonResponse;

class CategoryController extends AbstractController
{
    /* ADMIN */
    /**
     * @Route("/admin/category/list", name="list_categories")
     * Liste des articles
     * 
     */
    public function list(CategoryRepository $categoryRepository)
    {
        return $this->render('admin/category/list.html.twig', ['categories'=>$categoryRepository->findAllActive()]);
    }


      /**
     * @Route("/admin/category/form/{id}", name="form_category")
     * Ajout et modification categorie
     * 
     */
    public function form(Request $request,CategoryRepository $categoryRepository,$id = 0)
    {
        $category = new Category();
        $category -> setStatus(true) ;
        $titre = 'Modification';

        $id != 0 ? $category = $categoryRepository->find($id) :  $titre = 'Ajout';

        $form = $this->createForm(CategoryType::class,$category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('list_categories');
        }

        return $this->render('admin/category/form.html.twig', [
            'form' => $form->createView(),
            'titre' => $titre
        ]);
    }
    
    /**
     * @Route("/admin/category/delete", name="delete_category")
     * Suppression categorie
     * 
     */
    public function delete(CategoryRepository $categoryRepository,Request $request)
    {    
        if ($request->isXmlHttpRequest()) {
            $category = $categoryRepository->find($request->request->get('id'));
            $articles = $category->getArticles();
    
            $entityManager = $this->getDoctrine()->getManager();
    
            foreach ($articles as $key => $article) {
                $article -> setStatus(false);
                $entityManager->persist($article); 
            }
    
            $category -> setStatus(false); 
            $entityManager->flush();
            return new JsonResponse(['res' => 1]);
        }

        return new JsonResponse(['res' => 0]);
    }
}