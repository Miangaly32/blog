<?php
namespace App\Form\DataTransformer;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TagTransformer implements DataTransformerInterface
{
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function transform($value)
    {
        return implode(',',$value);
    }

    public function reverseTransform($value)
    {
        $labels = array_unique(array_filter(array_map('trim',explode(',',$value))));
        $tags = $this->tagRepository->findBy(['label'=>$labels]);
        $newLabels = array_diff($labels,$tags);
        foreach ($newLabels as $label) {
            $tag = new Tag();
            $tag->setLabel($label);
            $tags[] = $tag;
        }

        return $tags;
    }
}