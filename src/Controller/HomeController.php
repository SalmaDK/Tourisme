<?php

namespace App\Controller;

use App\Entity\Endroit;
use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(Security $security,SessionInterface $session): Response
    {
        $user = $security->getUser();
        
        if ($user) {
            $roles = $user->getRoles();
            $session->set('roles', $roles);
        }
        
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('home/index.html.twig', [
                'controller_name' => 'HomeController',

            ]);
        } else {
           return $this->redirectToRoute('app_login');
        }
    }
    #[Route('/hotels', name:'hotel_list2')]
     
    public function afficherHotels(EntityManagerInterface $entityManager,SessionInterface $session): Response
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

        return $this->render('hotel/afficherHotelClient.html.twig', [
            'hotels' => $results,
        ]);
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);

    
    }
}
