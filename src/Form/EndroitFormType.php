<?php

namespace App\Form;

use App\Entity\Endroit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EndroitFormType extends AbstractType
{
    private $requestStack;
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = $request->getSession();

        $typeEndroit = $session->get('typeEndroit');
        if($typeEndroit == "Hotel"){
            $builder
            ->add('nom')
            ->add('adresse')
            ->add('description',TextareaType::class)
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false, 
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Uploader une image valid (JPEG ou PNG)',
                    ])
                ],
            ])
            ->add('linkMap')
            ->add('linkRes', TextType::class,['mapped'=> false])
            ->add('nbretoile', TextType::class,['mapped'=> false])
            ->add('equipement', TextareaType::class,['mapped'=> false])
            ->add('Valider',SubmitType::class)   
        ;
        }
        elseif($typeEndroit == "restaurant" || $typeEndroit == "musee" ||  $typeEndroit == "activite" ||  $typeEndroit == "monument"){
            $builder
            ->add('nom')
            ->add('adresse')
            ->add('description',TextareaType::class)
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false, 
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Uploader une image valid (JPEG ou PNG)',
                    ])
                ],
            ])
            ->add('linkMap')
            ->add('Valider',SubmitType::class) ;
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Endroit::class,
        ]);
    }
}
