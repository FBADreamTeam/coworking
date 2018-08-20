<?php

namespace App\Controller;

use App\Form\CustomerLoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @param AuthenticationUtils $utils
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(AuthenticationUtils $utils): \Symfony\Component\HttpFoundation\Response
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

    /**
     * @Route("/info_user", name="info_user")
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoUser(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('api/info_user.html.twig', [
        ]);
    }

    /**
     * @Route("/api/user/info", name="user_info", defaults={
     *   "#_api_resource_class"=Customer::class,
     *   "_api_item_operation_name"="user_info",
     *   "_api_receive"=false
     * })
     */
    public function customerInfo(): JsonResponse
    {
        $userAddresses = [];
        foreach ($this->getUser()->getAddresses() as $id => $address) {
            $userAddresses[$id]['street'] = $address->getStreet();
            $userAddresses[$id]['cpl'] = $address->getAddressCpl();
            $userAddresses[$id]['postal_code'] = $address->getPostalCode();
            $userAddresses[$id]['city'] = $address->getCity();
            $userAddresses[$id]['country'] = $address->getCountry();
        }
        $responseUser = [
            'firstName' => $this->getUser()->getFirstName(),
            'lastName' => $this->getUser()->getLastName(),
            'email' => $this->getUser()->getEmail(),
            'addresses' => $userAddresses,
        ];

        return new JsonResponse($responseUser);
    }
}
