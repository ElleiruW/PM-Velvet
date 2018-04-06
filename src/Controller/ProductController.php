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
}

