<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{

    /**
     * Function login
     *
     * @param Request $request
     * @param AuthenticationUtils $authUtils
     * @return void
     */
    public function login(Request $request, AuthenticationUtils $authUtils)
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('security/login.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }
}