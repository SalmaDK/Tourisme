<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admins', name: 'app_admin')]
    public function AfficherAdmins(EntityManagerInterface $entityManager,SessionInterface $session): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles)   ){
            $query = $entityManager->createQueryBuilder()
            ->select('u.id','u.email','u.nom','u.prenom','u.telephone','u.dateNais', 'u.image')
            ->from(User::class, 'u')
            ->getQuery();
            $results = $query->getResult();
            return $this->render('admin/afficherAdmin.html.twig', [
                'admins' => $results,
            ]);
                
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
        
    }

    #[Route('/ajouter/admin', name: 'add_admin')]
    public function AjouterAdmin(EntityManagerInterface $entityManager,SessionInterface $session): Response
    {
        
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles)  ){
            return $this->redirectToRoute('app_register');
            
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);
       
    }

    #[Route('/admin/delete/{id}', name: 'admin_delete')]
    public function delete(SessionInterface $session,EntityManagerInterface $entityManager,$id): Response //n9der ndir b7al edit f recuperation dial id
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles)   ){
           
            $entity = $entityManager->getRepository(User::class)->find($id);
            $entityManager->remove($entity); 
            $entityManager->flush(); 
    
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
}
