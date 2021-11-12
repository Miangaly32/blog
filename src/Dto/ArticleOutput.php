<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final class ArticleOutput
{
    /**
     * @Groups({"article:read","category:read"})
     */
    public $title;

    /**
     * @Groups({"article:read","category:read"})
     */
    public $author;

    /**
     * @Groups({"article:read","category:read"})
     */
    public $articleDate;

    /**
     * @Groups({"article:read"})
     */
    public $category;

    /**
     * @Groups({"article:read","category:read"})
     */
    public $content;

    /**
     * @Groups({"article:read","category:read"})
     */
    public $extract;

    /**
     * @Groups({"article:read","category:read"})
     */
    public $thumbnail;

    /**
     * @Groups("article:read")
     */
    public $tags;

    /**
     * @Groups("article:read")
     */
    public $featured;


    /**
     * @Groups("article:read")
     */
    public $image_description;

    /**
     * @Groups("article:read")
     */
    public $image_metadata;

}