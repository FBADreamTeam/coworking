<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Events\UserCreatedEvent;
use App\Form\CustomerType;
use App\Form\CustomerLoginType;
use App\Managers\CustomerManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class CustomerController extends Controller
{

    /**
     * @Route("/profile/login", name="profile_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return RedirectResponse|Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
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
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param EventDispatcherInterface $dispatcher
     * @param CustomerManager $customerManager
     * @return Response
     */
    public function addCustomer(
        Request $request,
        UserPasswordEncoderInterface $encoder,
                                EventDispatcherInterface $dispatcher,
        CustomerManager $customerManager
    ): Response {
        $customer = new Customer();

        $form = $this->createForm(CustomerType::class, $customer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $emailCustom = $customer->getEmail();

            // Gestion des doublons
            $emailCustomBdd = $customerManager->checkDuplicateEmail($emailCustom);

            if ($emailCustomBdd) {
                $this->addFlash('notice', "L'email saisi est déjà enregistré");

                return $this->render('/profile/profile_register.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            // Gestion de la modification du mot de passe
            $password = $form->get('password')->getData();
            $passwordConfirm = $form->get('password_confirm')->getData();

            if (!empty($password) || !empty($passwordConfirm)) {
                if ($password === $passwordConfirm) {
                    $customer->setPassword($encoder->encodePassword($customer, $password));
                } else {
                    $this->addFlash('notice', "Les mots de passes ne sont pas identiques");

                    return $this->render('/profile/profile_register.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            }

            $customer->setPassword($encoder->encodePassword($customer, $customer->getPassword()));

            $customerManager->createCustomer($customer);

            $this->addFlash('success', "Votre compte a bien été créé");

            $event = new UserCreatedEvent($customer);
            $dispatcher->dispatch(UserCreatedEvent::NAME, $event);

            return $this->redirectToRoute('profile_new');
        }

        return $this->render('/profile/profile_register.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
