<?php
// src/Controller/BlogController.php
namespace App\Controller;

use App\Entity\Article;
use App\Form\Type\ArticleType;
use App\Repository\AuthorRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

class BlogController extends AbstractController
{
    private Security $security;
    private ArticleRepository $articleRepository;

    public function __construct(Security $security, ArticleRepository $articleRepository)
    {
        $this->security = $security;
        $this->articleRepository = $articleRepository;
    }

    /**
     * @Route("/blog/{id}", name="blog_detail")
     * Page de detail d'une article
     * 
     */
    public function detail(int $id)
    {
        return $this->render('blog/layout.html.twig', ['article' => $this->articleRepository->find($id)]);
    }


    /* ADMIN */
    /**
     * @Route("/admin/article/list", name="list_article")
     * Liste des articles
     * 
     */
    public function list(ArticleRepository $articleRepository)
    {
        return $this->render('admin/article/list.html.twig', ['articles' => $this->articleRepository->findBy(['status'=>true])]);
    }

    /**
     * @Route("/admin/article/form/{id}", name="form_article")
     * Ajout et modification articles
     * 
     */
    public function form(Request $request, $id = 0,AuthorRepository $authorRepository, SluggerInterface $slugger)
    {
        $article = new Article();
        $titre = 'Modification';
        $id != 0 ? $article = $this->articleRepository->find($id) : $titre = 'Ajout';
        $article->setArticleDate(new \DateTime('now'));
        $article->setStatus(true);

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $user = $this->security->getUser();

            $article->setAuthor($authorRepository->findOneBy(["user"=> $user]));

            /** @var UploadedFile $brochureFile */
            $thumbnailFile = $form->get('thumbnailFile')->getData();

            if ($thumbnailFile) {
                $originalFilename = pathinfo($thumbnailFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid('', true).'.'.$thumbnailFile->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $thumbnailFile->move('uploads',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $article->setThumbnail($newFilename);
            }

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
     */
    public function delete(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $article = $this->articleRepository->find($request->request->get('id'));
            $article->setStatus(false);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
            return new JsonResponse(['res' => 1]);
        }

        return new JsonResponse(['res' => 0]);
    }

    /**
     * @Route("/admin/article/restore/{id}", name="restore_article")
     * Restaurer article
     */
    public function restore(int $id)
    {
        $article = $this->articleRepository->find($id);
        $article->setStatus(true);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();

        return $this->redirectToRoute('list_article');
    }
}
