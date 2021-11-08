<?php
// src/Controller/BlogController.php
namespace App\Controller;

use App\Entity\Article;
use App\Form\Type\ArticleType;
use App\Form\Type\CropType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\Cropperjs\Factory\CropperInterface;
use Symfony\UX\Cropperjs\Model\Crop;

class BlogController extends AbstractController
{
    private Security $security;
    private ArticleRepository $articleRepository;
    private EntityManagerInterface $entityManager;

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
            'articles' => $this->articleRepository->findActives()
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
    public function form(Request $request,AuthorRepository $authorRepository, SluggerInterface $slugger, $id = 0)
    {
        $article = new Article();
        $article->setArticleDate(new \DateTime("now", new \DateTimeZone('Europe/Paris')));

        $titre = 'Modification';

        $id != 0 ? $article = $this->articleRepository->find($id) : $titre = 'Ajout';

        $session = new Session();
        if ($session->get("imageCropped")) {
            $article->setThumbnail($session->get("imageCropped"));
            $article->setImageDescription($session->get("imageDescription"));
            $article->setImageMetadata($session->get("imageMetadata"));
        }

        $form = $this->createForm(ArticleType::class, $article);

        $formImage = $this->createFormBuilder()
            ->add('thumbnail', FileType::class, ['label'  => 'Télécharger l\'image'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $user = $this->security->getUser();

            $article->setAuthor($authorRepository->findOneBy(["user"=> $user]));

            $thumbnailFile = $session->get("imageCropped");

            if ($thumbnailFile) {
                $article->setThumbnail($thumbnailFile);
            }

            $this->entityManager->persist($article);
            $this->entityManager->flush();

            $session->clear();

            return $this->redirectToRoute('list_article');
        }

        return $this->render('admin/article/add.html.twig', [
            'form' => $form->createView(),
            'titre' => $titre,
            'article' => $article,
            'formImage' => $formImage->createView()
        ]);
    }

    /**
     * @Route("/admin/article/uploadFile", name="uploadFile")
     * Upload l'image
     */
    public function uploadFile(Request $request, SluggerInterface $slugger,CropperInterface $cropper)
    {
        if ($request->isXmlHttpRequest()) {
            $thumbnailFile = $request->files->get('file');
            $articleId = $request->request->get('article_id');

            $originalFilename = pathinfo($thumbnailFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid('', true).'.'.$thumbnailFile->guessExtension();

            try {
                $thumbnailFile->move('temp',
                    $newFilename
                );
            } catch (FileException $e) {
                dump($e);
            }

            $crop = $cropper->createCrop($this->getParameter('kernel.project_dir').'/public/temp/'.$newFilename);

            $form = $this->createForm(
                CropType::class,
                [
                    "crop" => $crop],
                [
                    "action" => $this->generateUrl('cropImage'),
                    "newFilename" => $newFilename,
                    "articleId" => $articleId
                ]
            );

            return $this->render('cropper/index.html.twig', [
                'formCrop' => $form->createView()
            ]);
        }

        return new JsonResponse(['res' => 0]);
    }

    /**
     * @Route("/admin/article/cropImage", name="cropImage")
     * cropImage
     */
    public function cropImage(Request $request)
    {
        $filename = $this->getParameter('kernel.project_dir').'/public/temp/'.$request->get('crop')['newFilename'];
        $imageManager = new ImageManager();
        $crop = new Crop($imageManager,$filename);
        $crop->setOptions( $request->get('crop')['crop']['options']);
        $image = Image::make($crop->getCroppedImage());

        if ($image) {
            $image->save($this->getParameter('kernel.project_dir')."/public/uploads/".$request->get('crop')['newFilename']);

            $session = new Session();
            $session->set('imageCropped', $request->get('crop')['newFilename']);
            $session->set('imageMetadata', $request->get('crop')['image_metadata']);
            $session->set('imageDescription', $request->get('crop')['image_description']);

            if ($request->get('crop')['articleId']) {
                $article = $this->articleRepository->find($request->get('crop')['articleId']);
                if ($article) {
                    $article->setThumbnail($request->get('crop')['newFilename']);
                    return $this->redirectToRoute('form_article',['id' => $article->getId()]);
                }
            }
        }

        return $this->redirectToRoute('form_article');
    }

    /**
     * @Route("/admin/article/delete", name="delete_article")
     * Suppression article
     */
    public function delete(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $article = $this->articleRepository->find($request->request->get('id'));

            if ($article) {
                $article->setArchivedAt(new \DateTime("now", new \DateTimeZone('Europe/Paris')));
                $article->setStatus(false);
                $this->entityManager->persist($article);
                $this->entityManager->flush();

                return new JsonResponse(['res' => 1]);
            }
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

            if ($article) {
                $this->entityManager->remove($article);
                $this->entityManager->flush();
            }

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

        if ($article) {
            $article->setStatus(true);

            $category = $article->getCategory();
            if ($category) {
                $category->setStatus(true);
            }

            $author = $article->getAuthor();
            if ($author) {
                $author->setStatus(true);
            }

            $this->entityManager->persist($article);
            $this->entityManager->persist($category);
            $this->entityManager->persist($author);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('list_article');
    }

    /**
     * @Route("/admin/article/publish/{id}", name="publish")
     * Publier l'article
     */
    public function publish(int $id)
    {
        $article = $this->articleRepository->find($id);

        if ($article) {
            $article->setStatus(true);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('form_article',['id' => $id]);
    }
}
