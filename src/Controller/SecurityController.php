<?php

namespace App\Controller;

use App\Form\Type\ArticleType;
use App\Form\Type\UserPasswordType;
use App\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route ("/admin/profile",name="profile")
     */
    public function profile(Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $userRecup = clone $user;
        $form = $this->createForm(UserType::class, $user);
        $form_password = $this->createForm(UserPasswordType::class, $userRecup);

        $form->handleRequest($request);
        $form_password->handleRequest($request);

        if ($form->getClickedButton() === $form->get('save') || $form->getClickedButton() === $form->get('save1') ){
            if ($form->isSubmitted() && $form->isValid() ) {
                $user = $form->getData();
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('profile');
            }
        }
        if ($form_password->isSubmitted() && $form_password->isValid() ) {
            $userRecup = $form_password->getData();

            $user->setPassword($passwordHasher->hashPassword(
                $user,
                $userRecup->getNew()
            ));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('profile');
        }

        return $this->render('admin/security/profile.html.twig', [
            'form' => $form->createView(),
            'form_password' => $form_password->createView()
        ]);
    }
}
