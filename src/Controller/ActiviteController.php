<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Endroit;
use App\Form\EndroitFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ActiviteController extends AbstractController
{

    #[Route('/Activite', name:'activite_list')]
     
    public function index(EntityManagerInterface $entityManager,SessionInterface $session): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login');
        } 
        $roles = $session->get('roles');
        if(in_array('SUPER-ADMIN',$roles) || in_array('ADMIN',$roles)  ){
            $query = $entityManager->createQueryBuilder()
            ->select('e.id','a.id as ida','e.nom','e.adresse','e.description','e.image', 'e.linkMap')
            ->from(Endroit::class, 'e')
            ->join(Activite::class, 'a', 'WITH', 'a.idEndroit = e.id')
            ->getQuery();
            $results = $query->getResult();

        return $this->render('activite/afficherActivite.html.twig', [
            'activites' => $results,
        ]);
            
        }
        return $this->render('home/index.html.twig', [
            'controller_name' => 'AddCoursController',
        ]);

       

   
    }











    #[Route('/ajouter/activite', name: 'add_activite')]
    public function AjouterActivite(Request $request,SessionInterface $session,EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {   $session = $request->getSession();
        $session->set("typeEndroit","activite");

        $endroit = new Endroit();
        $activite = new Activite();

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
            $activite
            ->setIdendroit($endroit);
            $entityManager->persist($activite);
            $entityManager->flush();
            return $this->redirectToRoute('activite_list');

        }
        return $this->render('activite/ajouterActivite.html.twig', [
            'ativiteForm' => $form,
        ]);
    }

    #[Route('/detail/activite', name:'detail_activite')]
    public function detailMonum(EntityManagerInterface $entityManager ): Response
    {
        $query = $entityManager->createQueryBuilder()
        ->select('e.id','a.id as ida','e.nom','e.adresse','e.description','e.image', 'e.linkMap')
        ->from(Endroit::class, 'e')
        ->join(Activite::class, 'a', 'WITH', 'a.idEndroit = e.id')
        ->getQuery();
        $results = $query->getResult();

        return $this->render('activite/afficherDetailactiv.html.twig', [
            'activites' => $results,
        ]);
    }



    #[Route('/activite/delete/{id}/{id2}', name: 'activite_delete')]
    public function delete(EntityManagerInterface $entityManager, $id,$id2): Response //n9der ndir b7al edit f recuperation dial id
    {
        $entity = $entityManager->getRepository(Activite::class)->find($id2);
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


        return $this->redirectToRoute('activite_list');
    }



    #[Route('/modifier/activite/{id}/{id2}', name: 'edit_activite')]
    public function ModifierEndroit(EntityManagerInterface $entityManager,Endroit $endroit,Request $request,$id,$id2,SluggerInterface $slugger){

        $session = $request->getSession();
        $session->set("typeEndroit","activite");
            $entity = $entityManager->getRepository(Endroit::class)->find($id);
            $entity2 = $entityManager->getRepository(Activite::class)->find($id2);
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
                
                return $this->redirectToRoute('activite_list');
                
            }
            return $this->render('activite/ajouterActivite.html.twig', [
                'ativiteForm' => $form,
            ]);
        }











}
