<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\About;
use App\Entity\Author;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {

        $user = new User();
        $user->setEmail("test@gmail.com");
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'mdp'
        ));
        $manager->persist($user);

        $author = new Author();
        $author->setName("Auteur ");
        $author->setStatus(true);
        $manager->persist($author);
        $manager->flush();

        $category = new Category();
        $category->setName("Catégorie ");
        $category->setStatus(true);
        $manager->persist($category);
        $manager->flush();

        for ($i = 1; $i < 15; $i++) {
            $article = new Article;
            $article->setTitle("Titre " . $i);
            $article->setContent("<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Nihil adipisci harum pariatur molestias dolores laudantium commodi repellat quam! Doloremque quasi dolorum tempore dicta placeat libero sequi cupiditate quisquam non provident!</p>");
            $article->setArticleDate(new DateTime());
            $article->setAuthor($author);
            $article->setCategory($category);
            $article->setStatus(true);
            $manager->persist($article);
        }


        $about = new About();
        $about->setDescription("<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi optio autem architecto temporibus dignissimos maxime recusandae esse eos, totam perferendis similique officia error eaque ipsum saepe sed, nihil hic aut?</p>");
        $manager->persist($about);

        $manager->flush();
    }
}
