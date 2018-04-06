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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\UserRepository;

class UserController
{
    public function addUser(
        
        Environment $twig,
        FormFactoryInterface $factory,
        Request $request,
        ObjectManager $manager,
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $urlGenerator,
        SessionInterface $session){
        
            $user = new User;
            $builder = $factory->createBuilder(FormType::class, $user);
            $builder->add('username', TextType::class, ['label'=>'FORM.USER.USERNAME'] )
                ->add('firstname', TextType::class, ['label'=>'FORM.USER.FIRSTNAME'])
                ->add('lastname', TextType::class, ['label'=>'FORM.USER.LASTNAME'])
                ->add('email', EmailType::class, ['label'=>'FORM.USER.EMAIL'])
                ->add('password', RepeatedType::class,
                    array(
                        'type' => PasswordType::class,
                        'invalid_message' => 'The password fields must match.',
                        'options' => array('attr' => array('class' => 'password-field')),
                        'required' => true,
                        'first_options'  => array('label' => 'FORM.USER.PASSWORD.FIRST'),
                        'second_options' => array('label' => 'FORM.USER.PASSWORD.SECOND')
                    ))
                ->add('submit', SubmitType::class,
                    [
                    'attr' => [
                        'class'=>'btn-block btn-success'
                        ],
                     'label' => 'FORM.USER.SUBMIT'
                    ]
                    
                    );
              $form = $builder->getForm();
              
              $form->handleRequest($request);
              if ($form->isSubmitted()&& $form->isValid()){
                  
                  $manager->persist($user);
                  $manager->flush();
                  
                  $message = new \Swift_Message();
                  $message->setFrom('wf3@localhost.com')
                       ->setTo($user->getEmail())
                       ->setSubject('Validate yoour account')
                       ->setBody(
                           $twig->render(
                               'mail/account_creation.txt.twig',
                               ['user' => $user]
                               )
                           )->addPart(
                               $twig->render(
                                    'mail/account_creation.html.twig',
                                   ['user' => $user]
                               )
                                ,'text/plain'    
                           );
                  $mailer->send($message);     
                  
                  $session->getFlashBag()->add('info', 'Your account has been created');
                  
                  return new RedirectResponse($urlGenerator->generate('homepage'));
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
    public function activateUser(
        $token,
        ObjectManager $manager,
        UrlGeneratorInterface $urlGenerator,
        SessionInterface $session){
        
            
        $userRepository=$manager->getRepository(User::class);
        
  
           
            $user=$userRepository->findOneByEmailToken($token);
            if(!$user){
                throw new NotFoundHttpException("not found in directory");
            }
        $user->setActive(true);
        $user->setEmailToken(null);

          
          $session->getFlashBag()->add('info', 'Your account has been activated');
          $manager->flush();
          
          return new RedirectResponse($urlGenerator->generate('homepage'));
    }
    public function usernameAvailable(Request $request, UserRepository $repository){
        $username = $request->request->get('username');
        $unavailable = false;
        if(!empty($username)){
        $unavailable = $repository->usernameExist($username);
        }
        return new JsonResponse(
            [
            'available'=>!$unavailable
            ]
          );
    }
}

