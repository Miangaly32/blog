<?php
// src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AboutRepository;

class AboutController extends AbstractController
{
    /**
     * @Route("/about", name="about")
     * 
     */
    public function index(AboutRepository $aboutRepository)
    {
        return $this->render('about/layout.html.twig', ['about' =>$aboutRepository->findActive()]);
    }
}