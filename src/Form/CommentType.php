<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Comment;


class CommentType extends AbstractType
{
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO Auto-generated method stub
        $builder->add('comment',
            TextareaType::class,
            [
                'required'=>false
            ]  
           )->add(
                'files',
                CollectionType::class,
                [
                    'entry_type' =>CommentFileType::class,
                    'allow_add' => true
                ]
                
                );
            if ($options['stateless']){
                $builder->add('submit', SubmitType::class);
            }
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\AbstractType::configureOptions()
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // TODO Auto-generated method stub
        $resolver->setDefault('data_type', Comment::class);
        $resolver->setDefault('stateless', false);
        
        
    }

}

