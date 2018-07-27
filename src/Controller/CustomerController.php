<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerType;
use App\Form\CustomerLoginType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class CustomerController extends Controller
{

    /**
     * @Route("/profile/login", name="profile_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $form = $this->createForm(CustomerLoginType::class, [
            'email' => $authenticationUtils->getLastUsername()
        ]);

        // Récupération du message d'erreur
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('/profile/profile_login.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * @Route("/profile/new", name="profile_new")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addCustomer(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $customer = new Customer();

        $form = $this->createForm(CustomerType::class, $customer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();

            //$data = $form->getData();
            $emailCustom = $customer->getEmail();

            // Gestion des doublons
            $emailCustomBdd = $em->getRepository(Customer::class)->findByEmail($emailCustom);

            if (!empty($emailCustomBdd)) {
                $this->addFlash('notice', "L'email saisi est déjà enregistré");
            } else {
                $customer->setPassword($encoder->encodePassword($customer, $customer->getPassword()));
                $em->persist($customer);
                $em->flush();

                $this->addFlash('success', "Votre compte a bient été créé");
            }
        }

        return $this->render('/profile/profile_register.html.twig', [
            'form' => $form->createView()
        ]);
    }
}