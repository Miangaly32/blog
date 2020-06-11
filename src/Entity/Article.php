<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="date")
     */
    private $articleDate;

    /**
     * @ORM\ManyToOne(targetEntity=Author::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getArticleDate(): ?\DateTimeInterface
    {
        return $this->articleDate;
    }

    public function setArticleDate(\DateTimeInterface $articleDate): self
    {
        $this->articleDate = $articleDate;

        return $this;
    }

    public function getAuthor(): ?author
    {
        return $this->author;
    }

    public function setAuthor(?author $author): self
    {
        $this->author = $author;

        return $this;
    }
}
