<?php
// src/Controller/AuthorController.php
namespace App\Controller;

use App\Entity\Author;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AuthorRepository;
use App\Form\AuthorType;


class AuthorController extends AbstractController
{
    /* ADMIN */
    /**
     * @Route("/admin/author/list", name="list_authors")
     * Liste des auteurs
     * 
     */
    public function list(AuthorRepository $authorRepository)
    {
        return $this->render('admin/author/list.html.twig', ['authors'=>$authorRepository->findAll()]);
    }


      /**
     * @Route("/admin/author/form/{id}", name="form_author")
     * Ajout et modification categorie
     * 
     */
    public function form(Request $request,AuthorRepository $authorRepository,$id = 0)
    {
        
        $id != 0 ? $author = $authorRepository->find($id) : $author = new Author();$author -> setStatus(true) ;
        
        $form = $this->createForm(AuthorType::class,$author);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $author = $form->getData();
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($author);
            $entityManager->flush();

            return $this->redirectToRoute('list_authors');
        }

        return $this->render('admin/author/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    // /**
    //  * @Route("/admin/category/delete/{id}", name="delete_category")
    //  * Suppression categorie
    //  * 
    //  */
    // public function delete(CategoryRepository $categoryRepository,$id)
    // {
        
    //     $category = $categoryRepository->find($id);
    //     $articles = $category->getArticles();

    //     $entityManager = $this->getDoctrine()->getManager();

    //     foreach ($articles as $key => $article) {
    //         $article -> setStatus(false);
    //         $entityManager->persist($article); 
    //     }

    //     $category -> setStatus(false); 
    
    //     $entityManager->persist($category);
    //     $entityManager->flush();
    //     return $this->redirectToRoute('list_categories');
    // }
}