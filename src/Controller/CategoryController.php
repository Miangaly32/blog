<?php
// src/Controller/CategoryController.php
namespace App\Controller;

use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;
use App\Form\Type\CategoryType;
use Symfony\Component\HttpFoundation\JsonResponse;

class CategoryController extends AbstractController
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /* ADMIN */
    /**
     * @Route("/admin/category/list", name="list_categories")
     * Liste des articles
     * 
     */
    public function list()
    {
        return $this->render('admin/category/list.html.twig', ['categories'=>$this->categoryRepository->findAllActive()]);
    }


      /**
     * @Route("/admin/category/form/{id}", name="form_category")
     * Ajout et modification categorie
     * 
     */
    public function form(Request $request,$id = 0)
    {
        $category = new Category();
        $category -> setStatus(true) ;
        $titre = 'Modification';

        $id != 0 ? $category = $this->categoryRepository->find($id) :  $titre = 'Ajout';

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
    public function delete(Request $request)
    {    
        if ($request->isXmlHttpRequest()) {
            $category = $this->categoryRepository->find($request->request->get('id'));
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

    /**
     * @Route("/admin/category/restore/{id}", name="restore_category")
     * Restaurer categorie
     */
    public function restore(int $id)
    {
        $category = $this->categoryRepository->find($id);
        $category->setStatus(true);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($category);
        $entityManager->flush();

        return $this->redirectToRoute('list_categories');
    }
}