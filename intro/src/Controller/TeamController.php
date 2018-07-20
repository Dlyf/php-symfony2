<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Team;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TeamController extends Controller
{
  public function teamForm(Request $request)
  {
    if ($request->isMethod('post'))
    {
      // si la méthode HTTP de la requête est POST
      // traitement du formulaire
      // L'objet $request permet d'obtenir des informations
      // sur le requête
      // var_dump($request->request);
      // version objet de: ($_POST)
      //var_dump($_POST);
      // var_dump($request->request->get('name'));
      // récupération des valeurs
      $name           = $request->request->get('name');
      $coach          = $request->request->get('coach');
      $yearFoundation = $request->request->get('yearFoundation');

      // var_dump($substitute);
      // renvoie NULL si checkbox non coché, si cochée  : "On"

      // reformatage des données afin qu'elles correspondent
      // au type attendu par le constructeur de la classe Player

      // enregistrement en DB
      $team = new Team($name, $coach, $yearFoundation);
      $em = $this->getDoctrine()->getManager();

      $em->persist($team);
      $em->flush();

      // redirection vers la route teams
      return $this->redirectToRoute('teams');
    }

    // cas 2 : Get => affichage du formulaire (sans traitement) via l'url
    // GET
  return $this->render('team/form.html.twig');
}

  public function teamFormBis(Request $request)
  {
    $labels_fr = [
      'name'  => 'nom',
      'coach' => 'entraineur',
      'foundationYear' => 'Année de création'
    ];

    $teamExist = false; //flag permettant de savoir si l'équipe
    // qu'on souhaite enregistrer existe déjà en base de données

    // création d'un formulaire d'ajout d'une équipe
    // approche orientée controller (!= approche orientée template)
    $team = new Team('','', 2000);// création d'un objet vide
    $form = $this->createFormBuilder($team)
      ->add('name', TextType::class)
      ->add('coach', TextType::class)
      ->add('foundationYear', TextType::class)
      ->add('submit', SubmitType::class, array(
        'label' => 'Enregistrer'))
      ->getForm(); // produit un objet modellisant le formulaire

    // communication entre l'objet form et la requête http
    $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        // isSubmitted() permet de savoir si le formulaire a été posté

        $year = $request->request->get('foundationYear');
        // var_dump($year);

        $team = $form->getData(); // L'objet team
        // est alimentée avec les données du formulaire

        // vérifier s'il existe déjà une équipe ayant le
        // même nom en base de données
        $repo = $this->getDoctrine()->getRepository(Team::class);
        $found = $repo->findByName($team->getName());
        if (count($found) == 0) {
          // aucune portant déjà ce nom n'a été trouvée
          $em = $this->getDoctrine()->getManager();
          $em->persist($team);
          $em->flush();
          return $this->redirectToRoute('teams');
        } else {
            $teamExist = true;
        }



      }

      return $this->render('team/formbis.html.twig', array(
        'teamExist' => $teamExist,
        'labels_fr' => $labels_fr,
        'form' => $form->createView() //produit le balisage


      ));

  }

  public function teams()
  {
    // récupération des équipes
    $repo = $this->getDoctrine()->getRepository(Team::class);
    $teams = $repo->findAll();

    return $this->render('team/list.html.twig', array(
      'teams' => $teams
    ));
  }


}
