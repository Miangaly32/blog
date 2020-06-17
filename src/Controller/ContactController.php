<?php
// src/Controller/ContactController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AboutRepository;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     * 
     */
    public function index(AboutRepository $aboutRepository)
    {
        return $this->render('contact/layout.html.twig', []);
    }
}