<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface ; 
use Symfony\Component\Mime\Email ;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        		//Securité 
		//1) on met Request dans les paramètres de la fonction
		//2) on récupère la fonction
		$session = $request->getSession();
		//3) on teste si le role est cohérent
		if($session->get('roleUser')<1 ||$session->get('roleUser') >3){
			//4) si problème on renvoie sur le login
			return $this->redirectToRoute('login');}
          else{
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        
        ]);}
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        
		$session = $request->getSession();
		if($session->get('roleUser')<1 ||$session->get('roleUser') >3){
		return $this->redirectToRoute('login');}
        else{
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            
            $email = $form['email']->getData();
            $send = (new Email())
                ->from('welcomesymfony@gmail.com')
                ->to($email)
                ->subject('Blog Inscription')
                ->text('Bienvenue sur notre plateforme');
            $mailer->send($send);
    

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);}
    }

 /*   public function jetenvoieunmail(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response

    {
        $email = $request->request->get('email');
        $send = new email();
        $send->from('welcomesymfony@gmail.com');
        $send->to('welcomesymfony@gmail.com');
        $send->subject('Blog Inscription');
        $send->text('Bienvenue sur notre plateforme');
        $mailer->send($send);


    }

*/
    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
    }
}
