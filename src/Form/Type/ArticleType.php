<?php
namespace App\Form\Type;

use App\Entity\Article;
use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use App\Entity\Author;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add('title', TextType::class, ['label'  => 'Titre'])
            ->add('thumbnailFile', FileType::class, ['label'  => 'Image de la vignette'])
            ->add('articleDate', DateType::class, ['label'  => 'Date', 'format' => 'dd-MM-yyyy'])
            ->add('category', EntityType::class, ['class' => Category::class, 'choice_label' => 'name','query_builder' => function (CategoryRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.status = 1');
            },
            'data' => $options['data']->getCategory()])
            ->add('content', CKEditorType::class, ['label'  => 'Contenu'])
            ->add('extract', CKEditorType::class, ['label'  => 'Extrait'])
            ->add('tags', TagType::class, ['label'  => 'Tags'])
            ->add('save', SubmitType::class, ['label'  => 'Enregister'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
