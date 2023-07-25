<?php

namespace App\Controller;

use App\Entity\Endroit;
use App\Entity\Hotel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'variable' => 'Salma',
        ]);
    }
    #[Route('/hotels', name:'hotel_list2')]
     
    public function afficherHotels(EntityManagerInterface $entityManager): Response
    {

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
}
