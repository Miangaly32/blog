<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final class ArticleOutput
{
    /**
     * @Groups({"article:read"})
     */
    public $title;

    /**
     * @Groups({"article:read"})
     */
    public $author;

    /**
     * @Groups("article:read")
     */
    public $articleDate;

    /**
     * @Groups({"article:read"})
     */
    public $category;

    /**
     * @Groups({"article:read"})
     */
    public $content;

    /**
     * @Groups({"article:read"})
     */
    public $extract;

    /**
     * @Groups("article:read")
     */
    public $thumbnail;

    /**
     * @Groups("article:read")
     */
    public $tags;

    /**
     */
    public $image_description;

    /**
     */
    public $image_metadata;

}