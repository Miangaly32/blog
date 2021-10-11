<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"article:read"}}
 * )
 * @ApiFilter(BooleanFilter::class,properties={"status"})
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"article:read","category:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"article:read","category:read"})
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups("article:read")
     */
    private $content;

    /**
     * @ORM\Column(type="date")
     * @Groups("article:read")
     */
    private $articleDate;

    /**
     * @ORM\ManyToOne(targetEntity=Author::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("article:read")
     */
    private $category;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("article:read")
     */
    private $status;

    /**
     * @ORM\Column(type="text")
     * @Groups("article:read")
     */
    private $extract;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }


    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('title', new NotBlank([
            'message' => 'Ce champ est requis',
        ]));
        $metadata->addPropertyConstraint('content', new NotBlank([
            'message' => 'Ce champ est requis',
        ]));
        $metadata->addPropertyConstraint('articleDate', new NotBlank([
            'message' => 'Ce champ est requis',
        ]));
        $metadata->addPropertyConstraint(
            'articleDate',
            new Type(\DateTime::class)
        );
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getExtract(): ?string
    {
        return $this->extract;
    }

    public function setExtract(string $extract): self
    {
        $this->extract = $extract;

        return $this;
    }
}
