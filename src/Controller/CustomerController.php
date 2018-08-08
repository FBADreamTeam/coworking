<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Events\UserCreatedEvent;
use App\Form\CustomerType;
use App\Form\CustomerLoginType;
use App\Form\PasswordProfileType;
use App\Managers\CustomerManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

    /**
     * @Route("/profile/password/forget", name="profile_password_forget")
     *
     * @param Request $request
     * @param CustomerManager $customerManager
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function forgetPassword(Request $request, CustomerManager $customerManager, \Swift_Mailer $mailer)
    {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(PasswordProfileType::class, null, ['context' => "create"]);

        $form->handleRequest($request);

        $email = $form->get('email')->getData();

        if ($form->isSubmitted() && $form->isValid()){
            // On check que l'email est dans la bdd
            if ($customerManager->checkDuplicateEmail($email)) {

                // on check si un token est présent
                if ( (!$customerManager->checkTokenExist($email)) ) {

                    // Génération du token
                    $chaine = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    $token = md5(str_shuffle($chaine));

                    $customer = $em->getRepository(Customer::class)
                        ->findOneBy(['email' => $email]);

                    // Insertion du token
                    $customerManager->insertToken($customer, $token);
                    $linkResetPassword = $this->generateUrl('profile_password_update',
                        ['id' => $customer->getId(), 'token' => $customer->getToken()],
                        UrlGeneratorInterface::ABSOLUTE_URL);

                    // Envoi du mail avec le token
                    $customerManager->sendMessageGetPassword($email, $mailer, $linkResetPassword);

                    $this->addFlash("success", "Votre demande a bien été prise en compte.
                            Vous allez recevoir un email contenant la procédure à suivre pour modifier votre mote de passe. 
                            Elle est valable durant 2 heures. ");

                }else{
                    $this->addFlash('notice', "Une demande de réinitialistion de votre mot de passe vous a déjà été envoyé");
                }
            } else {
                $this->addFlash('notice', "L'email saisis n'est rattaché à aucun compte");
            }
        }

        return $this->render('/profile/profile_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/password/update/{id}/{token}", name="profile_password_update")
     *
     * @param Customer $customer
     * @param $token
     * @param CustomerManager $customerManager
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function updatePassword(Customer $customer, $token, CustomerManager $customerManager, Request $request, UserPasswordEncoderInterface $encoder)
    {
        dump($dateExpired = new \DateTime('now +2 hours'));

        // On vérifi que le token en GET soit identique en bdd
        if ($customerManager->checkTokenValid($customer->getId(), $token)) {

            // on vérifi que le token est encore valide en vérifiant la date d'expiration
            $dateToday = new \DateTime();
            $dateExpiredToken = $customer->getExpiredToken();

            // on check si une demande n'a pas déjà été envoyée
            if ($dateToday < $dateExpiredToken ){

                // On affiche le formulaire de changement de password et on le traite
                $form = $this->createForm(PasswordProfileType::class, null, ['context' => 'edit']);
                $form->handleRequest($request);

                // Gestion de la modification du mot de passe
                $password = $form->get('password')->getData();
                $passwordConfirm = $form->get('password_confirm')->getData();

                // Si password identique, maj du customer
                if (!empty($password) || !empty($passwordConfirm)) {
                    if ($password === $passwordConfirm) {
                        $customer->setPassword($encoder->encodePassword($customer, $password));

                        $customerManager->resetToken($customer);

                        $this->addFlash("success", "Votre mot de passe a bien été modifié");
                    } else {
                        $this->addFlash('notice', "Les mots de passes ne sont pas identiques");

                        return $this->render('/profile/profile_register.html.twig', [
                            'form' => $form->createView()
                        ]);
                    }
                }

                return $this->render('/profile/profile_password_reset.html.twig', [
                    'form' => $form->createView()
                ]);
            } else {

                // Mise à null du token pour pouvoir le réinitialiser
                //$customerManager->resetToken($customer);

                $this->addFlash('notice', "Par mesure de sécurité, le validité du lien pour réinitialiser 
                votre mot de passe a expriré. Veuillez relancer la procédure de réinitialisation.");
            }
        } else {
            $this->addFlash('notice', "Une erreur a été détectée dans le processus de modification du mot de passe");
            return $this->render('/profile/profile_password_reset.html.twig');
        }

        return $this->render('/profile/profile_password_reset.html.twig');
    }
}
