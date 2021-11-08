<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Cropperjs\Form\CropperType;

class CropType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('crop', CropperType::class, [
                'public_url' => '/temp/'.$options['newFilename']
            ])
            ->add('newFilename',HiddenType::class, [
                'data' => $options['newFilename'],
            ])
            ->add('articleId',HiddenType::class, [
                'data' => $options['articleId'],
            ])
            ->add('image_description', TextType::class, ['label'  => 'Image description'])
            ->add('image_metadata', TextType::class, ['label'  => 'Image metadata'])
            ->add('save',SubmitType::class)
            ->getForm()
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'newFilename' => null,
            'articleId' => null,
        ));
    }
}