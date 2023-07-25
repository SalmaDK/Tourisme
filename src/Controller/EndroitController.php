<?php

namespace App\Controller;

use App\Entity\Endroit;
use App\Form\EndroitFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EndroitController extends AbstractController
{
    #[Route('/ajouter/endroit', name: 'add_endroit')]
    public function addendroit(Request $request, EntityManagerInterface $entityManager): Response
    {
        
            $endroit = new Endroit();
            $form = $this->createForm(EndroitFormType::class, $endroit);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
    
                $entityManager->persist($endroit);
                $entityManager->flush();
                
            }
        return $this->render('endroit/ajouterEndroit.html.twig', [
            'endroitForm' => $form,
        ]);
    }
}
