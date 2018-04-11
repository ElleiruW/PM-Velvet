<?php
namespace App\Controller;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repository\ProductRepository;
use App\Form\CommentType;
use App\Entity\Comment;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\CommentFile;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;




class ProductController
{   
    
          
    public function addProduct(Environment $twig, FormFactoryInterface $factory, Request $request, ObjectManager $manager, SessionInterface $session){
        
        $product = new Product;
        $builder = $factory->createBuilder(FormType::class, $product);
        $builder->add('name', TextType::class, ['label'=>'FORM.PRODUCT.NAME'])
        ->add('description', TextareaType::class, ['label'=>'FORM.PRODUCT.DESCRIPTION'])
        ->add('version', TextType::class, ['label'=>'FORM.PRODUCT.VERSION'])
        ->add('submit', SubmitType::class,
            [
                'attr' => [
                    'class'=>'btn-block btn-success'
                ],
                'label'=>'FORM.PRODUCT.SUBMIT'
            ]
            
            );
        
        $form = $builder->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            
           $manager->persist($product);
           $manager->flush();
           
           $session->getFlashBag()->add('info', 'Your product was created');
           
           return new RedirectResponse('/');
        }

        return new Response(
            $twig ->render(
                'Product/addProduct.html.twig',
                [                                                                                                         
                    'formular' => $form->createView()
                ]
            )   
        );
    }

    public function listProduct(Environment $twig, ObjectManager $manager){
        
        $productRepository=$manager->getRepository(Product::class);
        
        $product=$productRepository->findAll();
        
        return new Response(
            $twig ->render(
                'Product/listProduct.html.twig',
                [
                    'product'=>$product
                ]
               )
            );
    }

    public function detailProduct(Environment $twig, ObjectManager $manager, FormFactoryInterface $formFactory, ProductRepository $productRepository, Request $request,  TokenStorageInterface $tokenStorage, UrlGeneratorInterface $urlGenerator){
        $id = $request->get('id');
        
        $product=$productRepository->find($id);
        
        
        
        $comment = new Comment();
        $form = $formFactory->create(
            CommentType::class,
            $comment,['stateless' => true]
            );
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            //do something
            $tmpCommentFile = [];
            
            foreach ($comment->getFiles() as $fileArray) {
                foreach ($fileArray as $file) {
                    $name = sprintf(
                        '%s.%s',
                        Uuid::uuid1(),
                        $file->getClientOriginalExtension()
                        );
                    
                    $commentFile = new CommentFile();
                    $commentFile->setComment($comment)
                    ->setMimeType($file->getMimeType())
                    ->setName($file->getClientOriginalName())
                    ->setFileUrl('/upload/'.$name);
                    
                    $tmpCommentFile[] = $commentFile;
                    
                    $file->move(
                        __DIR__.'/../../public/upload',
                        $name
                        );
                    $manager->persist($commentFile);
                }
        }
        $token = $tokenStorage->getToken();
        if (!$token){
            throw new \Exception();
        }
        $user = $token->getUser();
        if (!$user){
            throw new \Exception();
        }
        
        $comment->setFiles($tmpCommentFile)
        ->setAuthor($user)
        ->setProduct($product);
        
        $manager->persist($comment);
        $manager->flush();
        return new RedirectResponse($urlGenerator->generate('product_details',
            [
                'id'=>$product->getId()
            ]
            )
            );
        
        }
        
        
        
                
        return new Response(
            $twig ->render(
                    'Product/detailProduct.html.twig',
                    [
                        'product'=>$product,
                        'form' => $form->createView()
                    ]
                    )
                );
     }


}



