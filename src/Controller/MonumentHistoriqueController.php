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
     
    public function index(EntityManagerInterface $entityManager): Response
    {

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





    #[Route('/ajouter/monument/historique', name: 'add_monument')]
    public function AjouterHotel(Request $request,SessionInterface $session,EntityManagerInterface $entityManager,SluggerInterface $slugger ): Response
    {   $session = $request->getSession();
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

    #[Route('/monument/delete/{id}/{id2}', name: 'monum_delete')]
    public function delete(EntityManagerInterface $entityManager, $id,$id2): Response //n9der ndir b7al edit f recuperation dial id
    {
        $entity = $entityManager->getRepository(MonumentHistorique::class)->find($id2);
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


        return $this->redirectToRoute('monum_list');
    }



    #[Route('/modifier/monument/{id}/{id2}', name: 'edit_monum')]
    public function ModifierEndroit(EntityManagerInterface $entityManager,Endroit $endroit,Request $request,$id,$id2,SluggerInterface $slugger){

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


}
