<?php
namespace App\Form\Type;

use App\Entity\Article;
use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
            ->add('title', TextType::class, ['label'  => 'Titre','attr' => ["onchange" => 'change_val(this)']])
            ->add('articleDate', DateTimeType::class, ['label'  => 'Date','widget' => 'single_text','attr' => ["onchange" => 'change_val(this)']])
            ->add('archived_at', DateTimeType::class, ['label'  => 'Date fin','widget' => 'single_text', 'required' => false,'attr' => ["onchange" => 'change_val(this)']])
            ->add('category', EntityType::class, ['label'  => 'Catégorie','class' => Category::class, 'choice_label' => 'name','query_builder' => function (CategoryRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.status = 1');
            },
            'data' => $options['data']->getCategory(),'attr' => ["onchange" => 'change_val(this)']])
            ->add('content', CKEditorType::class, ['label'  => 'Contenu','attr' => ["onchange" => 'change_val(this)']])
            ->add('extract', CKEditorType::class, ['label'  => 'Extrait','attr' => ["onchange" => 'change_val(this)']])
            ->add('tags', TagType::class, ['label'  => 'Tags','attr' => ["onchange" => 'change_val(this)']])
            ->add('featured', CheckboxType::class, ['label'  => 'Mettre à la une','attr' => ["onchange" => 'change_val(this)']])
            ->add('save', SubmitType::class, ['label'  => 'Enregistrer','attr' => ["onclick" => 'submit_article()']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
