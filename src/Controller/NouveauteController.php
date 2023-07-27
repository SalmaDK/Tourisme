<?php

namespace App\Controller;

use App\Entity\Nouveaute;
use App\Form\NouveauteFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


class NouveauteController extends AbstractController
{



    #[Route('/Nouveaute', name:'nouveaute_list')]
     
    public function index(SessionInterface $session,EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
           
            $query = $entityManager->createQueryBuilder()
            ->select('n.id','n.description','n.image', 'n.datePub','n.titreU','n.titreD')
            ->from(Nouveaute::class, 'n')
            ->getQuery();
            $results = $query->getResult();

        return $this->render('nouveaute/afficheNouveaute.html.twig', [
            'nouveautes' => $results,
        ]);
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
        
    }

    #[Route('/ajouter/nouveaute', name: 'add_nouveaute')]
    public function addNouveaute(SessionInterface $session,Request $request, EntityManagerInterface $entityManager,SluggerInterface $slugger ): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
            $nouveaute = new Nouveaute();
        $form = $this->createForm(NouveauteFormType::class, $nouveaute);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
                    if ($image) {
                        $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
                        try {
                            $image->move(
                                $this->getParameter('image_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {

                        }
                        $nouveaute->setImage($newFilename);
                    }
            $entityManager->persist($nouveaute);
            $entityManager->flush();
            return $this->redirectToRoute('nouveaute_list');

        }


        return $this->render('nouveaute/ajouterNouveaute.html.twig', [
            'nouveauteForm' => $form,
        ]);
            
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
        
    }




 #[Route('/nouveaute/delete/{id2}', name: 'nouveaute_delete')]
    public function delete(SessionInterface $session,EntityManagerInterface $entityManager,$id2): Response //n9der ndir b7al edit f recuperation dial id
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
           
            $entity = $entityManager->getRepository(Nouveaute::class)->find($id2);

        $entityManager->remove($entity); //enregistrer product
        $entityManager->flush(); //executer

        $this->addFlash(
            'success',
            'Suprission avec success'
        );


        return $this->redirectToRoute('nouveaute_list');
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
        
    }

    #[Route('/modifier/nouveaute/{id}', name: 'edit_nouveaute')]
    public function ModifierCours(SessionInterface $session,EntityManagerInterface $entityManager,Nouveaute $nouveaute,Request $request,$id,SluggerInterface $slugger){
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
            $entity = $entityManager->getRepository(Nouveaute::class)->find($id);
            $form = $this->createForm(NouveauteFormType::class, $nouveaute);
            
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                
                $nouveaute = $form->getData();
                
                $entity->setDescription($form->get('description')->getData());  
                $entity->setDatePub($form->get('datePub')->getData());
                $image = $form->get('image')->getData();
                if ($image != null) {
                    $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
                    try {
                        $image->move(
                            $this->getParameter('image_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {

                    }
                    $entity->setImage($newFilename);
                }
                $entityManager->flush();
                
                return $this->redirectToRoute('nouveaute_list');
                
            }
            return $this->render('nouveaute/ajouterNouveaute.html.twig', [
                'nouveauteForm' => $form,
            ]);
            
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
            
    }
    
    }
    









