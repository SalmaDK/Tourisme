<?php

namespace App\Controller;

use App\Entity\Endroit;
use App\Entity\MonumentHistorique;
use App\Form\EndroitFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class MonumentHistoriqueController extends AbstractController
{
    #[Route('/Monument', name:'monum_list')]
     
    public function index(EntityManagerInterface $entityManager,SessionInterface $session): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
           
            $query = $entityManager->createQueryBuilder()
            ->select('e.id','m.id as idm','e.nom','e.adresse','e.description','e.image', 'e.linkMap')
            ->from(Endroit::class, 'e')
            ->join(MonumentHistorique::class, 'm', 'WITH', 'm.idEndroit = e.id')
            ->getQuery();
            $results = $query->getResult();

        return $this->render('monument_historique/afficheMonument.html.twig', [
            'monumments' => $results,
        ]);
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
       
    }





    #[Route('/ajouter/monument/historique', name: 'add_monument')]
    public function AjouterHotel(Request $request,SessionInterface $session,EntityManagerInterface $entityManager,SluggerInterface $slugger ): Response
    {   
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
            $session = $request->getSession();
        $session->set("typeEndroit","monument");

        $endroit = new Endroit();
        $monum = new MonumentHistorique();

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
            $monum
            ->setIdendroit($endroit);
            $entityManager->persist($monum);
            $entityManager->flush();
            return $this->redirectToRoute('monum_list');

        }

        return $this->render('monument_historique/ajouterMonumentH.html.twig', [
            'monumentForm' => $form,
        ]);
            
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
       
    }

    #[Route('/monument/delete/{id}/{id2}', name: 'monum_delete')]
    public function delete(SessionInterface $session,EntityManagerInterface $entityManager, $id,$id2): Response //n9der ndir b7al edit f recuperation dial id
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
           
            $entity = $entityManager->getRepository(MonumentHistorique::class)->find($id2);
            $entity2 = $entityManager->getRepository(Endroit::class)->find($id);
    
    
            $entityManager->remove($entity);
            $entityManager->flush(); 
            $entityManager->remove($entity2); 
            $entityManager->flush(); 
    
            $this->addFlash(
                'success',
                'Suprission avec success'
            );
    
    
            return $this->redirectToRoute('monum_list');
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
       
    }

    #[Route('/detail/monum', name:'detail_monum')]
    public function detailMonum(EntityManagerInterface $entityManager ): Response
    {
        $query = $entityManager->createQueryBuilder()
        ->select('e.id','m.id as idm','e.nom','e.adresse','e.description','e.image', 'e.linkMap')
        ->from(Endroit::class, 'e')
        ->join(MonumentHistorique::class, 'm', 'WITH', 'm.idEndroit = e.id')
        ->getQuery();
        $results = $query->getResult();

        return $this->render('monument_historique/afficherDetailMonum.html.twig', [
            'monuments' => $results,
        ]);
    }



    #[Route('/modifier/monument/{id}/{id2}', name: 'edit_monum')]
    public function ModifierEndroit(SessionInterface $session,EntityManagerInterface $entityManager,Endroit $endroit,Request $request,$id,$id2,SluggerInterface $slugger){
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
           
            $session = $request->getSession();
            $session->set("typeEndroit","monument");
                $entity = $entityManager->getRepository(Endroit::class)->find($id);
                $entity2 = $entityManager->getRepository(MonumentHistorique::class)->find($id2);
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
                    
                    return $this->redirectToRoute('monum_list');
                    
                }
                return $this->render('monument_historique/ajouterMonumentH.html.twig', [
                    'monumentForm' => $form,
                ]);
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
       
        }


}
