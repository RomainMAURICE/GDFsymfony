<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;

use App\Form\IdentificationType;
use App\Entity\Employe;
use App\Entity\formation;
use App\Form\FormationType;

use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\Inscription;


class EmployeController extends AbstractController
{
    /**
     * @Route("/employe", name="employe")
     */
    public function index(): Response
    {
        return $this->render('employe/index.html.twig', [
            'controller_name' => 'EmployeController',
        ]);
    }

     /**
     * @Route("/identification", name="identification")
     */
    public function identification(Request $request, $emp= null){

        $form = $this->createForm(IdentificationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $login = $form->get('login')->getViewData();
            $mdp = $form->get('mdp')->getViewData();
            $mdp = md5($mdp);
            $user = $this->getDoctrine()->getRepository(Employe::class)->findBy(
                [
                    'login' => $login,
                    'mdp' => $mdp
                ]
            );

            if($user == null){
                return $this->redirectToRoute('identification');
            }
            else{
                $session = new Session();
                $session->set('id',$user[0]->getId());
                if($user[0]->getStatut()==0){
                    return $this->redirectToRoute('app_for_supp');
                }
                elseif($user[0]->getStatut()==1){
                    return $this->redirectToRoute('app_for');
                }            
            }
        }
        return $this->render('employe/editer.html.twig', array('form'=>$form->createView()));

    }
//---------------------------crée une nouvelle formation---------------------------------//

    /**
     * @Route("/ajoutFormation", name="ajoutFormation")
     */
    public function ajoutFormationAction(Request $request, $formation= null){

        if (!$this->get('session')->get('id')){
            return $this->redirectToRoute('identification');
        }

        if($formation == null){
            $formation = new Formation();
        }
        $form = $this->createForm(FormationType::class, $formation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($formation);
            $em->flush();
            return $this->redirectToRoute('app_for_supp');
        }
        return $this->render('employe/formation.html.twig', array('form'=>$form->createView()));

    }







//----------------------------------affiche les employés P.U--------------------------------------------------------------------------------//

    /**
     * @Route("/afficheLesEmploye", name="app_emp")
     */
    public function afficheLesEmploye()
    {
        $employe = $this->getDoctrine()->getRepository(Employe::class)->findAll();
        if (!$employe ){
            $message = "Pas d'employe";
        }
        else{
            $message = null;
        }

        return $this->render('employe/listeemploye.html.twig',array('ensEmploye'=>$employe, 'message'=>$message));
    }

//---------------------------------------admin affiche les formations a supprimer--------------------------------------------------------//

    /**
    * @Route("/afficheLesFormationasupp", name="app_for_supp")
    */
   public function afficheLesFormationsupp()
   {
       $formation = $this->getDoctrine()->getRepository(Formation::class)->findAll();
       if (!$formation ){
           $message = "Pas de formation";
       }
       else{
           $message = null;
       }

       return $this->render('employe/listeformationasupp.html.twig',array('ensFormation'=>$formation, 'message'=>$message));
   }

   /**
    * @Route("/suppFormation/{id}", name="app_sup")
    */
   public function suppFormation($id)
   {
       $formation = $this->getDoctrine()->getManager()->getRepository(Formation::class)->find($id);
       $manager = $this->getDoctrine()->getManager();
       $manager->remove($formation);
       $manager->flush();
       return $this->redirectToRoute('app_for_supp');
   }


//---------------------------------s'inscrire a une formation-----------------------------------------------------------------------//


   
/**
     * @Route("/afficheLesFormation", name="app_for")
     */
    public function afficheLesFormation()
    {
        $formation = $this->getDoctrine()->getRepository(formation::class)->findAll();
        if (!$formation ){
            $message = "Pas de formation";
        }
        else{
            $message = null;
        }
        return $this->render('employe/listeformation.html.twig',array('ensFormation'=>$formation, 'message'=>$message));
    }

    // /**
    //  * @Route("/ajout", name="app_ajout")
    //  */
    // public function ajoutEmploye()
    // {
    //     $employe = new Employe();
    //     $employe->setLogin("tata");
    //     $employe->setMdp("tata");
    //     $employe->setNom("tata");
    //     $employe->setPrenom("tata");
    //     $employe->setStatut(1);
    //     $manager = $this->getDoctrine()->getManager();
    //     $manager->persist($employe);
    //     $manager->flush();
    //     return $this->render('Employe/index.html.twig', [
    //         'controller_name' => 'FilmController',
    //     ]);

    // }

    /**
     * @Route("/ajoutInscription/{id}", name="app_inscription")
     */
    public function ajoutInscription($id){
        $formation = $this->getDoctrine()->getManager()->getRepository(Formation::class)->find($id);
        $employeId = $this->get('session')->get('id');
        $employe = $this->getDoctrine()->getRepository(Employe::class)->find($employeId);

        $EXISTE = $this->getDoctrine()->getRepository(Inscription::class)->findBy(
            [
                'employe' => $employe,
                'formation' => $formation
            ]
        );
        if (!$EXISTE){
            $inscription = new Inscription();
            $inscription->setFormation($formation);
            $inscription->setEmploye($employe);
            $inscription->setStatut("E");
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($inscription);
            $manager->flush();
            return $this->render('employe/nonInscrit.html.twig');
        }
        else{
            return $this->render('employe/estInscrit.html.twig');
        }
    }

    /*-----------------------------valider inscription----------------------------------------*/

    /**
     * @Route("/afficheLesInscript", name="app_inscrit")
     */
    public function afficheLesInscript()
    {
        $inscription = $this->getDoctrine()->getRepository(inscription::class)->findAll();
        if (!$inscription ){
            $message = "Pas d'employe";
        }
        else{
            $message = null;
        }

        return $this->render('employe/listeInscription.html.twig',array('ensInscript'=>$inscription, 'message'=>$message));
    }


    /**
     * @Route("/modifieInscriptionv2/{id}", name="app_mod_inscription2")
     */
    public function modifieInscriptionv2($id){
        $inscription = $this->getDoctrine()->getManager()->getRepository(Inscription::class)->find($id);
        $inscription->setStatut("R");
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($inscription);
        $manager->flush();
        return $this->redirectToRoute('app_inscrit');
    }

    /**
     * @Route("/modifieInscription/{id}", name="app_mod_inscription")
     */
    public function modifieInscription($id){
        $inscription = $this->getDoctrine()->getManager()->getRepository(Inscription::class)->find($id);
        $inscription->setStatut("A");
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($inscription);
        $manager->flush();
        return $this->redirectToRoute('app_inscrit');
    }


/*-------------- DEVOIR --------------------------DEVOIR---------------------------------------------*/


    /**
     * @Route("/afficheLesFormationParis", name="app_for_paris")
     */
    public function afficheLesFormationParis()
    {
        $formation = $this->getDoctrine()->getRepository(formation::class)->findBy(
            [
                'departement' => "Paris"
            ]
        );
        if (!$formation ){
            $message = "Pas de formation";
        }
        else{
            $message = null;
        }
        return $this->render('employe/listeformation.html.twig',array('ensFormation'=>$formation, 'message'=>$message));
    }


/*------------Affiche tous les employés, et tous les formations d'un employé------------------------------------*/

    /**
     * @Route("/afficheFormationEmploye", name="aff_formation")
     */
    public function afficheLesFormationsEmploye()
    {
        $employe = $this->getDoctrine()->getRepository(Employe::class)->findAll();
        if (!$employe){
            $message = "Pas d'employe";
        }
        else{
            $message = null;
        }

        return $this->render('employe/afficheFormation.html.twig',array('ensEmploye'=>$employe, 'message'=>$message));
    }

    /**
     * @Route("/afficheFormationEmploye2/{id}", name="aff_formation2")
     */
    public function afficheLesFormationsEmploye2($id)
    {
        $inscription = $this->getDoctrine()->getRepository(Inscription::class)->findBy(
            [
                'employe' => $id
            ]
        );

        if (!$inscription){
            $message = "Inscrit dans aucune formation";
        }
        else{
            $message = null;
        }

        return $this->render('employe/afficheFormation2.html.twig',array('ensInscription'=>$inscription, 'message'=>$message));
    }
}