<?php
// src/Controller/AuthorController.php
namespace App\Controller;

use App\Entity\Author;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AuthorRepository;
use App\Form\AuthorType;
use Symfony\Component\HttpFoundation\JsonResponse;


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
        return $this->render('admin/author/list.html.twig', ['authors'=>$authorRepository->findAllActive()]);
    }


      /**
     * @Route("/admin/author/form/{id}", name="form_author")
     * Ajout et modification categorie
     * 
     */
    public function form(Request $request,AuthorRepository $authorRepository,$id = 0)
    {
        $titre = 'Modification';
        $id != 0 ? $author = $authorRepository->findAllActive($id) : $titre = 'Ajout'; $author = new Author();$author -> setStatus(true) ;
        
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
            'titre' => $titre
        ]);
    }
    
    /**
     * @Route("/admin/author/delete/", name="delete_author")
     * Suppression auteur
     * 
     */
    public function delete(AuthorRepository $authorRepository,Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $article = $authorRepository->find($request->request->get('id'));
            $article->setStatus(false);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
            return new JsonResponse(['res' => 1]);
        }

        return new JsonResponse(['res' => 0]);
    }
}