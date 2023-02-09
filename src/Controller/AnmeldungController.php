<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Repository\ApiTokenRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AnmeldungController extends AbstractController
{


    #[Route('/', name:'app_home')]
    public function home(): Response
    {

        return $this->redirectToRoute('app_cheeses');
    }



    #[Route('/create_token', name:'app_create_token')]
    public function createToken(ApiTokenRepository $apiTokenRepository, UserRepository $userRepository): Response
    {
        if($this->getUser()){
            $user= $userRepository->findOneBy(['email'=>$this->getUser()->getUserIdentifier()]);
            $token = new ApiToken($user) ;
            $apiTokenRepository->save($token,true);

            return $this->json($token,200,[],['groups'=>['token:read']]);
        }


        return $this->json(["token"=>null]);
    }


    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('target_path');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
