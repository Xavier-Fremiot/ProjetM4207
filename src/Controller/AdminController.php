<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Genre;
use App\Entity\Document;
use App\Entity\Acces;
use App\Entity\Autorisation;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
	#[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('admin/login.html.twig');
    }
	#[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
		//on détruit toute trace de la session
		$session = $request->getSession();
		$session->clear();
		//on redirige vers le login
        return $this->redirectToRoute('login');
    }
	#[Route('/connexion', name: 'connexion')]
    public function connexion(Request $request, ManagerRegistry $doctrine): Response
    {
		//Récupération variables de formulaire
		//ne pas oublier le use en haut de fichier
		$email = $request->request->get('email');
		$pwd = $request->request->get('pwd');
		dump($email,$pwd);
		$ok=0;
		//lien avec la base de données
		if($user= $doctrine->getRepository(User::class)->findOneByEmail($email)){
			if($user->getPwd() == $pwd){
				$ok=1;
				dump($ok);
				//on démarre la session
				//ne pas oublier le use en haut de fichier
				$session = new Session();
				// set session attributes
				$session->set('nameUser', $user->getNom());
				$session->set('roleUser', $user->getRole());
				$session->set('idUser', $user->getId());
				dump($session->get('nameUser'),$session->get('roleUser'));
				return $this->redirectToRoute('dashboard');
			}else{
				return $this->redirectToRoute('login');
			}
		}else{
			$ok=0;
			dump($ok);
			return $this->redirectToRoute('login');
		}    
    }
	//Partie correspondant à la gestion des Documents
	#[Route('/insertDocument', name: 'insert_document', methods: ['GET'])]
    public function insertDocument(ManagerRegistry $doctrine, Request $request): Response
    {
		//Securité 
		//1) on met Request dans les paramètres de la fonction
		//2) on récupère la fonction
		$session = $request->getSession();
		//3) on teste si le role est cohérent
		if($session->get('roleUser')<1 ||$session->get('roleUser') >3){
			//4) si problème on renvoie sur le login
			return $this->redirectToRoute('login');
		}else{			
			//5) sinon on renvoie la page demandée.
			return $this->render('admin/insertDocument.html.twig', [
				'genres' => $doctrine->getRepository(Genre::class)->findAll(),
				'user' => $doctrine->getRepository(User::class)->findAll(),
			]);
		}
    }
	#[Route('/uploadDocument', name: 'upload_document', methods: ['POST'])]
    public function uploadDocument(ManagerRegistry $doctrine, Request $request, EntityManagerInterface $em): Response
    {
		//Securité 
		//1) on met Request dans les paramètres de la fonction
		//2) on récupère la fonction
		$session = $request->getSession();
		//dd($request->request->get('choixBox'));
		//3) on teste si le role est cohérent
		if($session->get('roleUser')<1 ||$session->get('roleUser') >3){
			//4) si problème on renvoie sur le login
			return $this->redirectToRoute('login');
		}else{	
			// on upload le doc
			$doc = new Document();
			$doc->setNom($request->request->get('nom'));
			$doc->setChemin("toto");
			if($request->request->get('choixBox')=="on"){
				$doc->setActif(1);
			}else{
				$doc->setActif(0);
			}
			$doc->setCreatedAt(new \DatetimeImmutable);
			$doc->setType($doctrine->getRepository(Genre::class)->findOneById($request->request->get('genre')));
			$em->persist($doc);
			$em->flush();
			//maj de la table acces
			$acces = new Acces();
			$acces->setDocument($doc);
			$acces->setAutorisation($doctrine->getRepository(Autorisation::class)->findOneById(2));
			$acces->setUtilisateur($doctrine->getRepository(User::class)->findOneById($session->get('idUser')));
			$em->persist($acces);
			$em->flush();
			if($request->request->get('user')!="null"){
				$acces = new Acces();
				$acces->setDocument($doc);
				$acces->setAutorisation($doctrine->getRepository(Autorisation::class)->findOneById(2));
				$acces->setUtilisateur($doctrine->getRepository(User::class)->findOneById($request->request->get('user')));
				$em->persist($acces);
				$em->flush();
			}
			//5) sinon on renvoie la page demandée.
			return $this->redirectToRoute('dashboard');
		}
    }
	//FIN Partie correspondant à la gestion des Documents
	#[Route('/dashboard', name: 'dashboard')]
    public function dashboard(ManagerRegistry $doctrine, Request $request, EntityManagerInterface $em): Response
    {
		//Securité 
		//1) on met Request dans les paramètres de la fonction
		//2) on récupère la fonction
		$session = $request->getSession();
		//3) on teste si le role est cohérent
		if($session->get('roleUser')<1 ||$session->get('roleUser') >3){
			//4) si problème on renvoie sur le login
			return $this->redirectToRoute('login');
		}else{	
			// on récupère tous les accès de l'user connecté
			$listeDocuments = $em->getRepository(Acces::class)->findByUtilisateur($em->getRepository(User::class)->findOneById($session->get('idUser')));
			//5) sinon on renvoie la page demandée.
			dump($listeDocuments);
			return $this->render('admin/dashboard.html.twig', [
				'listeDocuments' => $listeDocuments
			]);
		}
    }
}












