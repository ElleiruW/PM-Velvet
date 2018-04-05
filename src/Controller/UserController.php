<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;


class UserController
{
    public function addUser(
        
        Environment $twig,
        FormFactoryInterface $factory,
        Request $request,
        ObjectManager $manager,
        SessionInterface $session){
        
            $user = new User;
            $builder = $factory->createBuilder(FormType::class, $user);
            $builder->add('username', TextType::class)
                ->add('firstname', TextType::class)
                ->add('lastname', TextType::class)
                ->add('email', TextType::class)
                ->add('password', RepeatedType::class,
                    array(
                        'type' => PasswordType::class,
                        'invalid_message' => 'The password fields must match.',
                        'options' => array('attr' => array('class' => 'password-field')),
                        'required' => true,
                        'first_options'  => array('label' => 'Password'),
                        'second_options' => array('label' => 'Repeat Password'),
                    ))
                ->add('submit', SubmitType::class,
                    [
                    'attr' => [
                        'class'=>'btn-block btn-success'
                        ]
                    ]
                    
                    );
              $form = $builder->getForm();
              
              $form->handleRequest($request);
              if ($form->isSubmitted()&& $form->isValid()){
                  
                  $manager->persist($user);
                  $manager->flush();
                  
                  $session->getFlashBag()->add('info', 'Your user was created');
                  
                  return new RedirectResponse('/');
              }
              
              return new Response(
                    $twig ->render(
                        'User/addUser.html.twig',
                        [
                            'formular'=> $form->createView()
                        ]
                      )
                  );
        
    }
    
}

