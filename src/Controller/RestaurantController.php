<?php

namespace App\Controller;

use App\Entity\Endroit;
use App\Entity\Restaurant;
use App\Form\EndroitFormType;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RestaurantController extends AbstractController
{

      
    #[Route('/restaurant', name:'restau_list')]
     
    public function index(EntityManagerInterface $entityManager): Response
    {

        $query = $entityManager->createQueryBuilder()
            ->select('e.id','r.id as idr','e.nom','e.adresse','e.description','e.image', 'e.linkMap')
            ->from(Endroit::class, 'e')
            ->join(Restaurant::class, 'r', 'WITH', 'r.idEndroit = e.id')
            ->getQuery();
            $results = $query->getResult();

        return $this->render('restaurant/index.html.twig', [
            'restaurants' => $results,
        ]);
    }


    #[Route('/detail/restaurant/{id}', name:'detail_restaurant')]
     
    public function detailHotel(EntityManagerInterface $entityManager ,$id): Response
    {

        

        $query = $entityManager->createQueryBuilder()
        ->select('e.id','r.id as idr','e.nom','e.adresse','e.description','e.image', 'e.linkMap')
        ->from(Endroit::class, 'e')
        ->join(Restaurant::class, 'r', 'WITH', 'r.idEndroit = e.id')
        ->where('r.id = :idRestau')
        ->setParameter('idRestau',$id)
        ->getQuery();
        $results = $query->getResult();


        return $this->render('restaurant/afficherDetailResto.html.twig', [
            'restaurants' => $results,
        ]);
    }


    #[Route('/ajouter/restaurant', name: 'add_restaurant')]
    public function AjouterRestaurant(Request $request,SessionInterface $session,EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {   $session = $request->getSession();
        $session->set("typeEndroit","restaurant");
        $endroit = new Endroit();
        $restaurant = new Restaurant();
       
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
            $restaurant->setIdendroit($endroit);

            $entityManager->persist($restaurant);
            $entityManager->flush();
            return $this->redirectToRoute('restau_list');

        }
        return $this->render('restaurant/ajouterRestaurant.html.twig', [
            'ResForm' => $form,
        ]);
    }




    #[Route('/restaurant/delete/{id}/{id2}', name: 'restaurant_delete')]
    public function delete(EntityManagerInterface $entityManager, $id,$id2): Response //n9der ndir b7al edit f recuperation dial id
    {
        $entity = $entityManager->getRepository(Restaurant::class)->find($id2);
        $entity2 = $entityManager->getRepository(Endroit::class)->find($id);


        // $filesystem = new Filesystem(); //hadi katrecuperer les fichier li 3ndi. andirha bach n9der nmsse7 tsswira dialo li 3ndi; hadi kadir l accer l'image
        // $imagePath = './uploads/' . $product->getImage();
        // //hna hoa dar if existe 7it dar choix bach tkon image awla la ; ana image drtha daroori tkon(vid 12_min 15)
        // $filesystem->remove($imagePath);

        $entityManager->remove($entity); //enregistrer product
        $entityManager->flush(); //executer
        $entityManager->remove($entity2); //enregistrer product
        $entityManager->flush(); 

        $this->addFlash(
            'success',
            'Suprission avec success'
        );


        return $this->redirectToRoute('restau_list');
    }



    #[Route('/modifier/restaurant/{id}/{id2}', name: 'edit_restaurant')]
    public function ModifierEndroit(EntityManagerInterface $entityManager,Endroit $endroit,Request $request,$id,$id2,SluggerInterface $slugger){

        $session = $request->getSession();
        $session->set("typeEndroit","restaurant");
            $entity = $entityManager->getRepository(Endroit::class)->find($id);
            $entity2 = $entityManager->getRepository(Restaurant::class)->find($id2);
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
                
                return $this->redirectToRoute('restau_list');
                
            }
            return $this->render('restaurant/ajouterRestaurant.html.twig', [
                'ResForm' => $form,
            ]);
    }














}
