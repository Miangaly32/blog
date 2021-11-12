<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\ArticleOutput;
use App\Entity\Article;
use DateTime;
use DateTimeZone;

class ArticleOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform($object, string $to, array $context = [])
    {
        $output = new ArticleOutput();
        $output->title = $object->getTitle();
        $output->category = $object->getCategory()->getName();
        $output->author = $object->getAuthor()->getUser()->getName();
        $output->content = $object->getContent();
        $output->extract = $object->getExtract();
        $output->tags = $object->getTags();
        $output->featured = $object->getFeatured();
        $output->thumbnail = $object->getThumbnail();
        $output->image_description = $object->getImageDescription();
        $output->image_metadata = $object->getImageMetadata();

        setlocale(LC_TIME, "fr_FR");
        $output->articleDate =strftime('%d %B %Y',$object->getArticleDate()->getTimestamp());

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return ArticleOutput::class === $to && $data instanceof Article;
    }
}