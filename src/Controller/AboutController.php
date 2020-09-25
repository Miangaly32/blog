<?php
// src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AboutRepository;
use App\Entity\About;
use App\Form\AboutType;

class AboutController extends AbstractController
{
    /**
     * @Route("/about", name="about")
     * 
     */
    public function index(AboutRepository $aboutRepository)
    {
        return $this->render('about/layout.html.twig', ['about' =>$aboutRepository->get()]);
    }

    /* ADMIN */
    /**
     * @Route("/admin/pages/about", name="apercu_about")
     * 
     */
    public function about(AboutRepository $aboutRepository)
    {
        return $this->render('admin/pages/about/apercu.html.twig', ['about' =>$aboutRepository->get()]);
    }

    /**
     * @Route("/admin/pages/about/form/{id}", name="form_about")
     * 
     */
    public function form(Request $request,AboutRepository $aboutRepository,$id=0)
    {

        $about = $aboutRepository->find($id) ;

        $form = $this->createForm(AboutType::class, $about);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $about = $form->getData();
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($about);
            $entityManager->flush();

            return $this->redirectToRoute('apercu_about');
        }

        return $this->render('admin/pages/about/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}