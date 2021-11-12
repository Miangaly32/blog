<?php
// src/Controller/AuthorController.php
namespace App\Controller;

use App\Entity\Author;

use Doctrine\ORM\EntityManagerInterface;
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
    private EntityManagerInterface $entityManager;

    public function __construct(AuthorRepository $authorRepository,EntityManagerInterface $entityManager)
    {
        $this->authorRepository = $authorRepository;
        $this->entityManager = $entityManager;
    }

    /* ADMIN */
    /**
     * @Route("/admin/author/list", name="list_authors")
     * Liste des auteurs
     * 
     */
    public function list(Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $author = new Author();
        $author -> setStatus(true) ;
        $form = $this->createForm(AuthorType::class,$author);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $author = $form->getData();

            $user = $author->getUser();
            $user->setPassword($passwordHasher->hashPassword(
                $user,
                '1234'
            ));

            $this->entityManager->persist($author);
            $this->entityManager->flush();

            return $this->redirectToRoute('list_authors');
        }

        return $this->render('admin/author/list.html.twig', [
            'form' => $form->createView(),
            'authors' => $this->authorRepository ->findBy(['status'=>true])
        ]);
    }

    /**
     * @Route("/admin/author/delete", name="delete_author")
     * Suppression auteur
     * 
     */
    public function delete(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $author = $this->authorRepository->find($request->request->get('id'));

            if ($author) {
                $articles = $author->getArticles();

                foreach ($articles as $key => $article) {
                    $article->setAuthor(null);
                    $this->entityManager->persist($article);
                }

                $author->setStatus(false);
                $this->entityManager->persist($author);
                $this->entityManager->flush();

                return new JsonResponse(['res' => 1]);
            }
        }
        return new JsonResponse(['res' => 0]);
    }

    /**
     * @Route("/admin/author/archive/delete", name="delete_archive_author")
     * Suppression définitif auteur
     *
     */
    public function deleteArchive(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $author = $this->authorRepository->find($request->request->get('id'));
            $this->entityManager->remove($author);
            $this->entityManager->flush();

            return new JsonResponse(['res' => 1]);
        }

        return new JsonResponse(['res' => 0]);
    }

    /**
     * @Route("/admin/author/archive/delete/multiple", name="delete_multiple_archive_author")
     * Suppression multiple définitif auteur
     *
     */
    public function deleteMultipleArchive(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $authors = $this->authorRepository->findByIds($request->request->get('ids'));

            foreach ($authors as $author) {
                $this->entityManager->remove($author);
            }
            $this->entityManager->flush();

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
        if ($author) {
            $author->setStatus(true);
            $this->entityManager->persist($author);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('list_authors');
    }
}