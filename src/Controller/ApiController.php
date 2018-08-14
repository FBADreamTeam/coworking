<?php

namespace App\Controller;

use App\Form\CustomerLoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class ApiController.
 */
class ApiController extends Controller
{
    /**
     * @Route("/login", name="login_jwt")
     * @Method("GET")
     *
     * @param Request             $request
     * @param AuthenticationUtils $utils
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(Request $request, AuthenticationUtils $utils): \Symfony\Component\HttpFoundation\Response
    {
        $error = $utils->getLastAuthenticationError();
        $lastUsername = $utils->getLastUsername();

        $form = $this->createForm(CustomerLoginType::class, [
            'username' => $lastUsername,
        ]);

        return $this->render('api/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }
}
