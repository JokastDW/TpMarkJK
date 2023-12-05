<?php

namespace App\Controller;

use App\Entity\Modele;
use App\Form\ModeleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


#[Route('/modele')]
class ModeleController extends AbstractController
{
    #[Route('/', name: 'app_modele')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $Modele = new Modele();
        $form = $this->createForm(ModeleType::class, $Modele);

        //Demande d'analyse de la requete HTTP
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Le formulaire a été soumis et est valide
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'image' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory_modele'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Impossible d\'ajouter l\'imange');
                    // ... handle exception if something happens during file upload
                }
                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $Modele->setImage($newFilename);
            
            }

            $em->persist($Modele);
            $em->flush();
        }

        // Récupération des Modeles (SELECT *)
        $Modeles = $em->getRepository(Modele::class)->findAll();

        return $this->render('modele/index.html.twig', [
            'modeles' => $Modeles,
            'ajout' => $form->createView() //Envoie la version HTML du formulaire 
        ]);
    }

    #[Route('/{id}', name: 'modele')]
    public function modele(Modele $Modele = null, EntityManagerInterface $em, Request $request): Response
    {
        // Si le Modele est introuvable
        if ($Modele == null) {
            $this->addFlash('error', 'Modele introuvable');
            return $this->redirectToRoute('app_modele');
        }
        $form = $this->createForm(ModeleType::class, $Modele);

        //Demande d'analyse de la requete HTTP
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'image' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $imageFile->move(
                        $this->getParameter('_modele'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Impossible d\'ajouter l\'imange');
                    // ... handle exception if something happens during file upload
                }
                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
            
                $Modele->setImage($newFilename);
            }
                // Le formulaire a été soumis et est valide
                $em->persist($Modele);
                $em->flush();

                $this->addFlash('success', 'Modeles trouvés');
            }

            return $this->render('modele/show.html.twig', [
                'Modele' => $Modele,
                'update' => $form->createView(),
            ]);
        }
    

    #[Route('/delete/{id}', name: 'delete_modele')]
    public function delete(Modele $Modele = null, EntityManagerInterface $em)
    {
        // Le Modele a été supprimée après avoir cliqué sur supprimer 
        if ($Modele == null) {
            $this->addFlash('danger', 'Modele introuvable');
            return $this->redirectToRoute('app_modele');
        }

        $em->remove($Modele);
        $em->flush();
        $this->addFlash('success', 'Modele supprimé');
        return $this->redirectToRoute('app_modele');
    }
}
