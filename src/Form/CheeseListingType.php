<?php

namespace App\Form;

use App\Entity\CheeseListing;
use App\Entity\User;
use App\Repository\CheeseListingRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheeseListingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $criteria=$options['user'];
        $builder
            ->add('title')
            ->add('description')
            ->add('price')
            ->add('isPublished')
            ->add('slug')
            ->add('createdAt', null, [
                    'widget'=>'single_text'
                ]
            )
            ->add('updatedAt', null, [
                    'widget'=>'single_text'
                ]
            )
            ->add('owner',EntityType::class,[
                'class'=>User::class,
                'choice_label'=>'username',
                'query_builder'=>function(UserRepository $repository) use ($criteria){
                    return $repository->findOneByQueryBuilder($criteria);
                }

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CheeseListing::class,
            'user'=>null
        ]);
    }
}
