<?php

namespace App\Controller;

use App\Entity\Endroit;
use App\Entity\Musee;
use App\Form\EndroitFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class MuseeController extends AbstractController
{


    #[Route('/Musee', name:'musee_list')]
     
    public function index(SessionInterface $session,EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
           
            $query = $entityManager->createQueryBuilder()
            ->select('e.id','m.id as idm','e.nom','e.adresse','e.description','e.image', 'e.linkMap')
            ->from(Endroit::class, 'e')
            ->join(Musee::class, 'm', 'WITH', 'm.idEndroit = e.id')
            ->getQuery();
            $results = $query->getResult();

        return $this->render('musee/afficherMusee.html.twig', [
            'musees' => $results,
        ]);
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
        
    }




    #[Route('/ajouter/musee', name: 'add_musee')]
    public function AjouterMusee(Request $request,SessionInterface $session,EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {   
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
            $session = $request->getSession();
            $session->set("typeEndroit","musee");
    
            $endroit = new Endroit();
            $musee = new Musee();
    
            $form = $this->createForm(EndroitFormType::class, $endroit);
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
                    $endroit->setImage($newFilename);
                }
                $entityManager->persist($endroit);
                $entityManager->flush();
    
                $endroit = $entityManager->getRepository(Endroit::class)->find($endroit->getId());
                $musee->setIdendroit($endroit);
                
                $entityManager->persist($musee);
                $entityManager->flush();
                return $this->redirectToRoute('musee_list');
    
            }
            return $this->render('musee/ajouterMusee.html.twig', [
                'museeForm' => $form,
            ]);
            
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
       
    }

    #[Route('/detail/musee', name:'detail_musee')]
    public function detailMusee(EntityManagerInterface $entityManager ): Response
    {
        $query = $entityManager->createQueryBuilder()
        ->select('e.id','m.id as idm','e.nom','e.adresse','e.description','e.image', 'e.linkMap')
        ->from(Endroit::class, 'e')
        ->join(Musee::class, 'm', 'WITH', 'm.idEndroit = e.id')
        ->getQuery();
        $results = $query->getResult();

        return $this->render('musee/afficherDetailMusee.html.twig', [
            'musees' => $results,
        ]);
    }

    #[Route('/musee/delete/{id}/{id2}', name: 'musee_delete')]
    public function delete(SessionInterface $session,EntityManagerInterface $entityManager, $id,$id2): Response //n9der ndir b7al edit f recuperation dial id
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
           
            $entity = $entityManager->getRepository(Musee::class)->find($id2);
        $entity2 = $entityManager->getRepository(Endroit::class)->find($id);

        $entityManager->remove($entity);  
        $entityManager->flush(); 
        $entityManager->remove($entity2);  
        $entityManager->flush(); 

        $this->addFlash(
            'success',
            'Suprission avec success'
        );


        return $this->redirectToRoute('musee_list');
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
        
    }



    #[Route('/modifier/musee/{id}/{id2}', name: 'edit_musee')]
    public function ModifierEndroit(SessionInterface $session,EntityManagerInterface $entityManager,Endroit $endroit,Request $request,$id,$id2,SluggerInterface $slugger){
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
           
            $session = $request->getSession();
        $session->set("typeEndroit","musee");
            $entity = $entityManager->getRepository(Endroit::class)->find($id);
            $entity2 = $entityManager->getRepository(Musee::class)->find($id2);
            $form = $this->createForm(EndroitFormType::class, $endroit);
            
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                
                $endroit = $form->getData();
                
                $entity->setNom($form->get('nom')->getData());
                $entity->setAdresse($form->get('adresse')->getData());
                $entity->setDescription($form->get('description')->getData());
                $entity->setLinkMap($form->get('linkMap')->getData());

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
                
                return $this->redirectToRoute('musee_list');
                
            }
            return $this->render('musee/ajouterMusee.html.twig', [
                'museeForm' => $form,
            ]);
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
        
    }










}
