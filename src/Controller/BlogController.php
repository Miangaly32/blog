<?php
// src/Controller/BlogController.php
namespace App\Controller;

use App\Entity\Article;
use App\Form\Type\ArticleType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
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
    private EntityManager $entityManager;

    public function __construct(Security $security, ArticleRepository $articleRepository, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->articleRepository = $articleRepository;
        $this->entityManager = $entityManager;
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
    public function list()
    {
        return $this->render('admin/article/list.html.twig', [
            'articles' => $this->articleRepository->findBy(['archived_at'=>null])
        ]);
    }

    /**
     * @Route("/admin/article/detail/{id}", name="detail_article")
     * Détail article
     *
     */
    public function details($id)
    {
        return $this->render('admin/article/detail.html.twig', ['article' => $this->articleRepository->find($id)]);
    }

    /**
     * @Route("/admin/article/form/{id}", name="form_article")
     * Ajout et modification articles
     *
     */
    public function form(Request $request, $id = 0,AuthorRepository $authorRepository, SluggerInterface $slugger)
    {
        $article = new Article();
        $article->setArticleDate(new \DateTime("now", new \DateTimeZone('Europe/Paris')));

        $titre = 'Modification';

        $id != 0 ? $article = $this->articleRepository->find($id) : $titre = 'Ajout';

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
                    dump($e);
                }

                $article->setThumbnail($newFilename);
            }

            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return $this->redirectToRoute('list_article');
        }

        return $this->render('admin/article/add.html.twig', [
            'form' => $form->createView(),
            'titre' => $titre,
            'article' => $article
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
            $article->setArchivedAt(new \DateTime("now", new \DateTimeZone('Europe/Paris')));
            $article->setStatus(false);
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return new JsonResponse(['res' => 1]);
        }

        return new JsonResponse(['res' => 0]);
    }

    /**
     * @Route("/admin/article/archive/delete", name="delete_archive_article")
     * Suppression définitif d'article
     */
    public function deleteArchive(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $article = $this->articleRepository->find($request->request->get('id'));
            $this->entityManager->remove($article);
            $this->entityManager->flush();

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
        $category = $article->getCategory();
        $category->setStatus(true);
        $author = $article->getAuthor();
        $author->setStatus(true);

        $this->entityManager->persist($article);
        $this->entityManager->persist($category);
        $this->entityManager->persist($author);
        $this->entityManager->flush();

        return $this->redirectToRoute('list_article');
    }

    /**
     * @Route("/admin/article/publish/{id}", name="publish")
     * Publier l'article
     */
    public function publish(int $id)
    {
        $article = $this->articleRepository->find($id);
        $article->setStatus(true);
        $this->entityManager->flush();

        return $this->redirectToRoute('form_article',['id' => $id]);
    }
}
