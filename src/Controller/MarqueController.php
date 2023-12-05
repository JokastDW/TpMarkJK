<?php

namespace App\Controller;

use App\Entity\Marque;
use App\Form\MarqueType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Button;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;

#[Route('/marque')]
class MarqueController extends AbstractController
{
    #[Route('/', name: 'app_marque')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $marque = new Marque();
        $form = $this->createForm(MarqueType::class, $marque);

        //Demande d'analyse de la requete HTTP
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

              // Le formulaire a été soumis et est valide
              $imageFile = $form->get('logo')->getData();

              // this condition is needed because the 'image' field is not required
              // so the PDF file must be processed only when a file is uploaded
              if ($imageFile) {
                  $newFilename = uniqid() . '.' . $imageFile->guessExtension();
  
                  // Move the file to the directory where images are stored
                  try {
                      $imageFile->move(
                          $this->getParameter('upload_directory_marque'),
                          $newFilename
                      );
                  } catch (FileException $e) {
                      $this->addFlash('danger', 'Impossible d\'ajouter l\'image');
                      // ... handle exception if something happens during file upload
                  }
                  // updates the 'imageFilename' property to store the PDF file name
                  // instead of its contents
                  $marque->setLogo($newFilename);
              
              }
  

            // Le formulaire a été soumis et est valide
            $em->persist($marque);
            $em->flush();
        }

        // Récupération des catégories (SELECT *)
        $marques = $em->getRepository(Marque::class)->findAll();

        return $this->render('marque/index.html.twig', [
            'marques' => $marques,
            'ajout' => $form->createView() //Envoie la version HTML du formulaire 
        ]);
    }

    #[Route('/{id}', name: 'marque')]
    public function marque(Marque $marque = null, EntityManagerInterface $em, Request $request): Response
    {
        // Si la catégorie est introuvable
        if ($marque == null) {
            $this->addFlash('error', 'Catégorie introuvable');
            return $this->redirectToRoute('app_marque');
        }
        $form = $this->createForm(MarqueType::class, $marque);

        //Demande d'analyse de la requete HTTP
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Le formulaire a été soumis et est valide
            $em->persist($marque);
            $em->flush();

            $this->addFlash('success', 'Marques trouvées');
        }

        return $this->render('marque/show.html.twig', [
            'marque' => $marque,
            'update' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete_marque')]
    public function delete(Marque $marque = null, EntityManagerInterface $em)
    {
        // La catégorie a été supprimée après avoir cliqué sur supprimer 
        if ($marque == null) {
            $this->addFlash('danger', 'Marque introuvable');
            return $this->redirectToRoute('app_marque');
        }

        $em->remove($marque);
        $em->flush();
        $this->addFlash('success', 'Marque supprimée');
        return $this->redirectToRoute('app_marque');
    }
}
