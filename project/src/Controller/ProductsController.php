<?php

namespace App\Controller;

use App\Entity\Products;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{
    public function __construct(private readonly ProductsRepository $productsRepository)
    {
    }
    #[Route('/product', name: 'product_list', methods: ['GET'])]
    public function default(): Response
    {
        $products = $this->productsRepository->findAll();
        return $this->render('productList.html.twig', ['products' => $products]);
    }

    #[Route('/product/add', name: 'product_add', methods: ['GET', 'POST'])]
    public function newProduct(Request $request, ManagerRegistry $registry): Response
    {
        $product = new Products();
        $form = $this->createFormBuilder($product)
            ->add('name', TextType::class)
            ->add('price', TextType::class)
            ->add('season', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Products'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $entityManager = $registry->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('product_list');
        }

        return $this->render('addProduct.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/product/delete/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Products::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('product_list');
    }

    #[Route('/product/update/{id}', name: 'product_update', methods: ['GET','POST'], requirements: ['id' => '\d+'])]
    public function update(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Products::class)->find($id);
        $form = $this->createFormBuilder($product)
            ->add('name', TextType::class)
            ->add('price', TextType::class)
            ->add('season', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Products'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('product_list', ['id' => $product->getId()]);
        }
        return $this->render('update.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/product/show/{id}', name: 'product_show', methods: ['GET','POST'], requirements: ['id' => '\d+'])]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $product = $doctrine->getRepository(Products::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
         return $this->render('showProduct.html.twig', ['product' => $product]);
    }
}
