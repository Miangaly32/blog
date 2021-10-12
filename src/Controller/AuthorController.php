<?php
// src/Controller/AuthorController.php
namespace App\Controller;

use App\Entity\Author;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AuthorRepository;
use App\Form\Type\AuthorType;
use Symfony\Component\HttpFoundation\JsonResponse;


class AuthorController extends AbstractController
{
    private AuthorRepository $authorRepository;

    public function __construct(AuthorRepository $authorRepository)
    {
        $this->authorRepository = $authorRepository;
    }

    /* ADMIN */
    /**
     * @Route("/admin/author/list", name="list_authors")
     * Liste des auteurs
     * 
     */
    public function list()
    {
        return $this->render('admin/author/list.html.twig', ['authors'=>$this->authorRepository ->findBy(['status'=>true])]);
    }


      /**
     * @Route("/admin/author/form/{id}", name="form_author")
     * Ajout et modification categorie
     * 
     */
    public function form(Request $request,$id = 0, UserPasswordHasherInterface $passwordHasher)
    {
        $titre = 'Modification';
        $author = new Author();
        $author -> setStatus(true) ;
        $id != 0 ? $author = $this->authorRepository->find($id) : $titre = 'Ajout';
        
        $form = $this->createForm(AuthorType::class,$author);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $author = $form->getData();

            $user = $author->getUser();
            $user->setPassword($passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            ));

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
    public function delete(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $article = $this->authorRepository->find($request->request->get('id'));
            $article->setStatus(false);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
            return new JsonResponse(['res' => 1]);
        }

        return new JsonResponse(['res' => 0]);
    }

    /**
     * @Route("/admin/author/restore/{id}", name="restore_author")
     * Restaurer auteur
     */
    public function restore(int $id)
    {
        $author = $this->authorRepository->find($id);
        $author->setStatus(true);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        return $this->redirectToRoute('list_authors');
    }
}