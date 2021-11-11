<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use App\Dto\ArticleOutput;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ApiResource(
 *     collectionOperations={
 *          "get"
 *      },
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"article:read"}},
 *     output= ArticleOutput::class
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
     * @ORM\Column(type="datetime")
     * @Groups("article:read")
     */
    private $articleDate;

    /**
     * @ORM\ManyToOne(targetEntity=Author::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true)
     * @Groups("article:read")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"article:read"})
     */
    private $category;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("article:read")
     */
    private $status = 0;

    /**
     * @ORM\Column(type="text")
     * @Groups("article:read")
     */
    private $extract;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups("article:read")
     */
    private $thumbnail;

    /**
     * @var File
     */
    private $thumbnailFile;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="articles",cascade={"persist"})
     * @Groups("article:read")
     */
    private $tags;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $archived_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("article:read")
     */
    private $image_description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("article:read")
     */
    private $image_metadata;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

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

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @return File
     */
    public function getThumbnailFile(): ?File
    {
        return $this->thumbnailFile;
    }

    /**
     * @param File $thumbnailFile
     */
    public function setThumbnailFile(File $thumbnailFile): void
    {
        $this->thumbnailFile = $thumbnailFile;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAt(): self
    {
        $this->created_at = new \DateTime();

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAt(): self
    {
        $this->updated_at = new \DateTime();

        return $this;
    }

    public function getArchivedAt(): ?\DateTimeInterface
    {
        return $this->archived_at;
    }

    public function setArchivedAt(?\DateTimeInterface $archived_at): self
    {
        $this->archived_at = $archived_at;

        return $this;
    }

    public function getImageDescription(): ?string
    {
        return $this->image_description;
    }

    public function setImageDescription(?string $image_description): self
    {
        $this->image_description = $image_description;

        return $this;
    }

    public function getImageMetadata(): ?string
    {
        return $this->image_metadata;
    }

    public function setImageMetadata(?string $image_metadata): self
    {
        $this->image_metadata = $image_metadata;

        return $this;
    }
}
