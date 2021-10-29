<?php
// src/Controller/BlogController.php
namespace App\Controller;

use App\Entity\Article;
use App\Form\Type\ArticleType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\Cropperjs\Factory\CropperInterface;
use Symfony\UX\Cropperjs\Form\CropperType;
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
    public function form(Request $request,AuthorRepository $authorRepository, SluggerInterface $slugger, $id = 0)
    {
        $article = new Article();
        $article->setArticleDate(new \DateTime("now", new \DateTimeZone('Europe/Paris')));

        $titre = 'Modification';

        $id != 0 ? $article = $this->articleRepository->find($id) : $titre = 'Ajout';

        $form = $this->createForm(ArticleType::class, $article);

        $formImage = $this->createFormBuilder()
            ->add('thumbnail', FileType::class, ['label'  => 'Télécharger l\'image'])
            ->getForm();

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

        if ($formImage->isSubmitted() && $formImage->isValid()) {
            dd($formImage->getData());
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
            $crop->setCroppedMaxSize(2000, 1500);

            $form = $this->createFormBuilder(['crop' => $crop])
                ->setAction($this->generateUrl('cropImage'))
                ->add('crop', CropperType::class, [
                    'public_url' => '/temp/'.$newFilename
                ])
                ->add('newFilename',HiddenType::class, [
                    'data' => $newFilename,
                ])
                ->add('articleId',HiddenType::class, [
                    'data' => $articleId,
                ])
                ->add('image_description', TextType::class, ['label'  => 'Image description'])
                ->add('image_metadata', TextType::class, ['label'  => 'Image metadata'])
                ->add('save',SubmitType::class)
                ->getForm()
            ;

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
        $filename = $this->getParameter('kernel.project_dir').'/public/temp/'.$request->get('form')['newFilename'];
        $imageManager = new ImageManager();
        $crop = new Crop($imageManager,$filename);
        $crop->setOptions( $request->get('form')['crop']['options']);
        $image = Image::make($crop->getCroppedThumbnail(200, 150));

        $article = new Article();

        if ($image) {
            $image->save($this->getParameter('kernel.project_dir')."/public/uploads/".$request->get('form')['newFilename']);

            $article = $this->articleRepository->find( $request->get('form')['articleId']);

            $article->setThumbnail($request->get('form')['newFilename']);

            $this->entityManager->persist($article);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('form_article',['id' => $article->getId()]);
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
