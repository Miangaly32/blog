<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     * 
     */
    public function index()
    {
        return $this->render('admin/login.html.twig', []);
    }

    /**
     * @Route("/register", name="register")
     * 
     */
    public function register()
    {
        return $this->render('admin/register.html.twig', []);
    }
}