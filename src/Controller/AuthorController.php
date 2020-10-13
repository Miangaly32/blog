<?php
// src/Controller/AuthorController.php
namespace App\Controller;

use Exception;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


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
    public function form(Request $request, AuthorRepository $authorRepository, $id = 0, Security $security, UserPasswordEncoderInterface $passwordEncoder)
    {
        $titre = 'Modification';
        $author = new Author();
        $author->setStatus(true);

        $id != 0 ? $author = $authorRepository->findOneBy(["id" => $id, "status" => true]) : $titre = 'Ajout';
        
        $form = $this->createForm(AuthorType::class,$author);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           
            $author = $form->getData();
            $user = $author->getUser();
            $entityManager = $this->getDoctrine()->getManager();

            if (!$user->getPassword()) {
                $user->setRoles(array("ROLE_ADMIN"));
                $user->setPassword($passwordEncoder->encodePassword(
                    $user,
                    'mdp'
                ));
                $entityManager->persist($user);
                $entityManager->flush();
            }
           
            
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