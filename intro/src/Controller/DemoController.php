<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Player;
use App\Entity\Team;

class DemoController extends Controller
{
  public $players = [
    array(
      'id' => 1,
      'name' => 'Pogba',
      'num' => 6,
      'substitute' => false,
      'photo' => 'https://cdn-media.rtl.fr/cache/cF_cpQchwkEenyULSozZYw/880v587-0/online/image/2016/0613/7783642440_paul-pogba-le-10-juin-2016-sur-la-pelouse-du-stade-de-france.jpg'
    ),
    array(
      'id' => 2,
      'name' => 'Lloris',
      'num' => 1,
      'substitute' => true,
      'photo' => 'https://cdn.static01.nicematin.com/media/npo/mobile_1440w/2017/06/sweden-vs-france-21139351.jpg'
    ),
    array(
      'id' => 3,
      'name' => 'Mbappé',
      'num' => 10,
      'substitute' => false,
      'photo' => 'https://o.aolcdn.com/images/dims3/GLOB/crop/3993x2000+7+473/resize/630x315!/format/jpg/quality/85/http%3A%2F%2Fo.aolcdn.com%2Fhss%2Fstorage%2Fmidas%2Fd29c75dee4addd58a632c716ad3dd867%2F206139045%2Fparis-saintgermains-french-forward-kylian-mbappe-celebrates-his-goal-picture-id889341858'
    )
  ];

  public function index()
  {
    $colors = ['bleu', 'blanc', 'rouge'];

    // La méthode render() provient de la classe Controller
    return $this->render('demo.html.twig', array(
      'title' => 'Demo TWIG',
      'message' => 'Simple message en provenance du controller',
      'available' => true,
      'colors' => $colors,
      'players' => $this->players
    ));
  }

  public function player($id)
  {
    // $id correspond au paramètre d'url {id} défini
    // dans le fichier le routage

    // à partir de l'identifiant, on va récupérer la
    // totalité des données concernant le joueur identifié

    $player = null;

    //cette boucle sert à circuler le tableau des joueurs
    foreach($this->players as $p) {
      //joueur trouvé dans la source de donnée
      if ($p['id'] == $id) {
        $player = $p;
      }
    }

    return $this->render('player.html.twig', array(
      'player' => $player
    ));
  }

  public function players()
  {
    $p1 = new Player('Abdel M', 10, false, '');
    // $p1->setName("Abdel M");
    // $p1->setNum(10);
    // $p1->setSubstitute(false);
    $p2 = new Player('Cristiano Ronaldo', 7, false, '');
    $p3 = new Player('Adil Rami', 17, true, '');

    // $this->addPlayers();

    // récupérer les joueurs en base de données
    // pour Les requêtes en Lecture, on utilise getRepository()
    // Le Repository gère toutes les reqêtes en lecture
    $repo = $this->getDoctrine()->getRepository(Player::class);
    $players = $repo->findAll(); // SELECT * FROM Player


    return $this->render('players.html.twig', array(
      'title'   => 'Liste de joueurs',
      'players' => [$p1, $p2, $p3],
      'players2'=> $players
    ));
  }

  private function addPlayers()
  {
    $p1 = new Player('Pogba', 6, false, 'https://cdn-media.rtl.fr/cache/cF_cpQchwkEenyULSozZYw/880v587-0/online/image/2016/0613/7783642440_paul-pogba-le-10-juin-2016-sur-la-pelouse-du-stade-de-france.jpg');
    $p2 = new Player('Lloris', 1, true, 'https://cdn.static01.nicematin.com/media/npo/mobile_1440w/2017/06/sweden-vs-france-21139351.jpg');

    //getManager() fournit un objet gérant les requêtes en écriture

    $em = $this->getDoctrine()->getManager();
    $em->persist($p1); // requête pendante (en attente d'execution)
    $em->persist($p2); // requête pendante (en attente d'execution)
    $em->flush(); //  exécutions des requêtes pendantes

  }

  public function playerForm(Request $request)
  {
    // L'objet $request permet d'obtenir des informations
    // sur le requête

    $teamRepo = $this->getDoctrine()->getRepository(Team::class);
    // 2 case de figure


    // récupérer les valeurs postées


    // Cas 1 : POST => traitement de l'envoi du formulaire via le formulaire
    if ($request->isMethod('post')) {
      // si la méthode HTTP de la requête est POST
      // traitement du formulaire

      // var_dump($request->request);
      // version objet de: ($_POST)
      //var_dump($_POST);
      // var_dump($request->request->get('name'));
      $name       = $request->request->get('name');
      $num        = $request->request->get('num');
      $substitute = $request->request->get('substitute');
      $photo      = $request->request->get('photo');
      $team_id    = intval($request->request->get('team'));

      // var_dump($substitute);
      // renvoie NULL si checkbox non coché, si cochée  : "On"

      // reformatage des données afin qu'elles correspondent
      // au type attendu par le constructeur de la classe Player

      if ($substitute != NULL) {
        $substitute = true;
      } else {
        $substitute = false;
      }
      // enregistrement en DB
      $player = new Player($name, $num, $substitute, $photo);

      //$player->setTeam($team_id); // INTERDIT
      // setTeams attend un argument de type Team (un objet) pas un entier
      // solution: obtenir un objet à partir de l'identifiant (entier)
      //findById renvoie un tableau d'objet, on l'extrait l'objet
      // situé à l'indice 0 
      $team = $teamRepo->findById($team_id)[0];
      $player->setTeam($team);
      // var_dump($team_id);
      // echo '<br><br>';
      // var_dump($team);

      $em = $this->getDoctrine()->getManager();
      $em->persist($player);
      $em->flush();

      // redirection vers la route players
      return $this->redirectToRoute('players');
    }

    // cas 2 : Get => affichage du formulaire (sans traitement) via l'url

    // récupération des équipes
    $teams = $teamRepo->findAll();
    return $this->render('playerForm.html.twig', array(
      // 'teams' => $teams = $teamRepo->findAll(); ou
      'teams' => $teams
    ));
  }

}
