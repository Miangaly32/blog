<?php
// src/Controller/CategoryController.php
namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
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
    private EntityManagerInterface $entityManager;

    public function __construct(CategoryRepository $categoryRepository, EntityManagerInterface $entityManager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
    }

    /* ADMIN */
    /**
     * @Route("/admin/category/list", name="list_categories")
     * Liste des articles
     * 
     */
    public function list(Request $request)
    {
        $category = new Category();
        $category -> setStatus(true) ;

        $form = $this->createForm(CategoryType::class,$category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            return $this->redirectToRoute('list_categories');
        }

        return $this->render('admin/category/list.html.twig', [
            'categories'=>$this->categoryRepository->findBy(['status' => true]),
            'form' => $form->createView()
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

            foreach ($articles as $key => $article) {
                $article -> setStatus(false);
                $this->entityManager->persist($article);
            }
    
            $category -> setStatus(false);
            $this->entityManager->flush();
            return new JsonResponse(['res' => 1]);
        }

        return new JsonResponse(['res' => 0]);
    }

    /**
     * @Route("/admin/category/archive/delete", name="delete_archive_category")
     * Suppression définitive categorie
     *
     */
    public function deleteArchive(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $category = $this->categoryRepository->find($request->request->get('id'));
            $articles = $category->getArticles();

            foreach ($articles as $key => $article) {
                $this->entityManager->remove($article);
            }

            $this->entityManager->remove($category);
            $this->entityManager->flush();
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
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $this->redirectToRoute('list_categories');
    }
}