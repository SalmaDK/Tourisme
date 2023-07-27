<?php

namespace App\Controller;

use App\Entity\Endroit;
use App\Entity\Hotel;
use App\Entity\Nouveaute;
use App\Entity\Restaurant;
use App\Form\EndroitFormType;
use App\Form\HotelFormType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class HotelController extends AbstractController
{

    
    #[Route('/hotel', name:'hotel_list')]
     
    public function index(EntityManagerInterface $entityManager,SessionInterface $session): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
           
            $query = $entityManager->createQueryBuilder()
            ->select('e.id','h.id as idh','e.nom','e.adresse','e.description','e.image', 'e.linkMap','h.linkRes','h.nbretoile','h.equipement')
            ->from(Endroit::class, 'e')
            ->join(Hotel::class, 'h', 'WITH', 'h.idendroit = e.id')
            ->getQuery();
            $results = $query->getResult();

        return $this->render('hotel/afficheHotel.html.twig', [
            'hotels' => $results,
        ]);
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);

       
    }

    #[Route('/Tourisme', name:'hotel_list2')]
     
    public function afficherHotel(EntityManagerInterface $entityManager): Response
    {

        $query = $entityManager->createQueryBuilder()
            ->select('e.id','h.id as idh','e.nom','e.adresse','e.description','e.image', 'e.linkMap','h.linkRes','h.nbretoile','h.equipement')
            ->from(Endroit::class, 'e')
            ->join(Hotel::class, 'h', 'WITH', 'h.idendroit = e.id')
            ->getQuery();
            $results = $query->getResult();

        $query2 = $entityManager->createQueryBuilder()
            ->select('e.id','r.id as idr','e.nom','e.adresse','e.description','e.image', 'e.linkMap')
            ->from(Endroit::class, 'e')
            ->join(Restaurant::class, 'r', 'WITH', 'r.idEndroit = e.id')
            ->getQuery();
        $results2 = $query2->getResult();

        $query3 = $entityManager->createQueryBuilder()
        ->select('n.id','n.description','n.image', 'n.datePub','n.titreU','n.titreD')
        ->from(Nouveaute::class, 'n')
        ->getQuery();
        $results3 = $query3->getResult();



        return $this->render('base3.html.twig', [
            'hotels' => $results,
            'restaurants' => $results2,
            'nouveautes' => $results3,

        ]);
    }

    #[Route('/detail/hotel/{id}', name:'detail_hotel')]
     
    public function detailHotel(EntityManagerInterface $entityManager ,$id,SessionInterface $session): Response
    {
       

        $query = $entityManager->createQueryBuilder()
        ->select('e.id','h.id as idh','e.nom','e.adresse','e.description','e.image', 'e.linkMap','h.linkRes','h.nbretoile','h.equipement')
        ->from(Endroit::class, 'e')
        ->join(Hotel::class, 'h', 'WITH', 'h.idendroit = e.id')
        ->where('h.id = :idHotel')
        ->setParameter('idHotel',$id)
        ->getQuery();
        $results = $query->getResult();

        return $this->render('hotel/afficherDetailHotel.html.twig', [
            'hotels' => $results,
        ]);
    }



    #[Route('/ajouter/hotel', name: 'add_hotel')]
    public function AjouterHotel(Request $request,SessionInterface $session,EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {  
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
            $session = $request->getSession();
        $session->set("typeEndroit","Hotel");
        $endroit = new Endroit();
        $hotel = new Hotel();
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

            $hotel->setLinkRes($form->get('linkRes')->getData());
            $hotel->setNbretoile($form->get('nbretoile')->getData());
            $hotel->setEquipement($form->get('equipement')->getData());

            $endroit = $entityManager->getRepository(Endroit::class)->find($endroit->getId());
            $hotel->setIdendroit($endroit);

            $entityManager->persist($hotel);
            $entityManager->flush();
            return $this->redirectToRoute('hotel_list');

        }
        return $this->render('hotel/ajouterHotel.html.twig', [
            'hotelForm' => $form,
        ]);
            
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
       
    }



    #[Route('/hotel/delete/{id}/{id2}', name: 'hotel_delete')]
    public function delete(EntityManagerInterface $entityManager, $id,$id2,SessionInterface $session): Response //n9der ndir b7al edit f recuperation dial id
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
            $entity = $entityManager->getRepository(Hotel::class)->find($id2);
            $entity2 = $entityManager->getRepository(Endroit::class)->find($id);
    
            $entityManager->remove($entity);
            $entityManager->flush(); 
            $entityManager->remove($entity2); 
            $entityManager->flush(); 
    
            $this->addFlash(
                'success',
                'Suprission avec success'
            );
    
    
            return $this->redirectToRoute('hotel_list');
            
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
       
    }
    
    #[Route('/modifier/hotel/{id}/{id2}', name: 'edit_hotel')]
    public function ModifierEndroit(SessionInterface $session,EntityManagerInterface $entityManager,Endroit $endroit,Request $request,$id,$id2,SluggerInterface $slugger){
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
           
            $session = $request->getSession();
        $session->set("typeEndroit","Hotel");
            $entity = $entityManager->getRepository(Endroit::class)->find($id);
            $entity2 = $entityManager->getRepository(Hotel::class)->find($id2);
            $form = $this->createForm(EndroitFormType::class, $endroit);
            $form->get('linkRes')->setData($entity2->getLinkRes());
            $form->get('nbretoile')->setData($entity2->getNbretoile());
            $form->get('equipement')->setData($entity2->getEquipement());
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
                $entity2->setLinkRes($form->get('linkRes')->getData());
                $entity2->setNbretoile($form->get('nbretoile')->getData());
                $entity2->setEquipement($form->get('equipement')->getData());
                // $endroit = $entityManager->getRepository(Endroit::class)->find($form->get('equipement')->getData());
                // $entity2->setIdendroit($endroit);
                

                $entityManager->flush();
                
                return $this->redirectToRoute('hotel_list');
                
            }
            return $this->render('hotel/ajouterHotel.html.twig', [
                'hotelForm' => $form,
            ]);
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
        
    }










    
}
