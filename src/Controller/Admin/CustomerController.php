<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 02/08/2018
 * Time: 12:23
 */

namespace App\Controller\Admin;

use App\Entity\Address;
use App\Entity\Customer;
use App\Form\AddressType;
use App\Form\CustomerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Managers\CustomerManager;

/**
 * @Route("/_secure")
 * Class AdminController
 * @package App\Controller\Admin
 */
class CustomerController extends Controller
{

    /**
     * @Route("/customer/list", name="admin_customer_list")
     * @@Security("has_role('ROLE_ADMIN')")
     * @param CustomerManager $customerManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listCustomer(CustomerManager $customerManager)
    {
        $customers = $customerManager->listCustomer();

        return $this->render('/admin/customer/list_customer.html.twig', [
            'customers' => $customers
        ]);
    }

    /**
     * @Route("/customer/edit/{id}", name="admin_customer_edit")
     * @@Security("has_role('ROLE_ADMIN')")
     *
     * @param Customer $customer
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param CustomerManager $customerManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editCustomer(Customer $customer, Request $request, UserPasswordEncoderInterface $encoder, CustomerManager $customerManager)
    {
        $form = $this->createForm(CustomerType::class, $customer, ['context' => 'edit']);

        $customerEmployee = $customer->getEmail(); // Mail du user en Bdd

        $form->handleRequest($request); // A ce moment, le formulaire récupère les données du formulaire

        $emailCustomerForm = $customer->getEmail(); // Mail du user saisi dans le formulaire

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion des doublons

            if ($customerEmployee !== $emailCustomerForm) {
                $duplicateEmail = $customerManager->checkDuplicateEmail($emailCustomerForm);

                if ($duplicateEmail) {
                    $this->addFlash('notice', "Le mail existe déjà");

                    return $this->render('/admin/customer/edit_customer.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            }

            // Gestion de la modification du mot de passe
            $password = $form->get('password')->getData();
            $passwordConfirm = $form->get('password_confirm')->getData();

            if (!empty($password) || !empty($passwordConfirm)) {
                if ($password === $passwordConfirm) {
                    $customer->setPassword($encoder->encodePassword($customer, $password));
                } else {
                    $this->addFlash('notice', "Les mots de passes ne sont pas identiques");

                    return $this->render('/admin/customer/edit_customer.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            }

            $customerManager->updateCustomer();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('admin_customer_list');
        }

        return $this->render('/admin/customer/edit_customer.html.twig', [
            'form' => $form->createview()
        ]);
    }

    /**
     * @Route("/customer/{id_customer}/edit/address/{id_address}", name="admin_customer_edit_address")
     * @@Security("has_role('ROLE_ADMIN')")
     *
     * @ParamConverter("customer", options={"mapping": {"id_customer": "id"}})
     * @ParamConverter("address", options={"mapping": {"id_address": "id"}})
     * @param Address $address
     * @param Request $request
     * @param CustomerManager $customerManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAddressCustomer(Address $address, Request $request, CustomerManager $customerManager)
    {
        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerManager->updateCustomer();

            $this->addFlash('success', "L'addresse a bien été modifié");

            return $this->redirectToRoute('admin_customer_list');
        }

        return $this->render('/admin/customer/edit_address.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/customer/add/address/{id_customer}", name="admin_customer_add_address")
     * @@Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("customer", options={"mapping": {"id_customer": "id"}})
     *
     * @param Customer $customer
     * @param Request $request
     * @param CustomerManager $customerManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAddressCustomer(Customer $customer, Request $request, CustomerManager $customerManager)
    {
        $address = new Address();
        $address->setCustomer($customer);

        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerManager->addAddressCustomer($address);

            $this->addFlash('success', "L'addresse a bien été ajoutée");

            return $this->redirectToRoute('admin_customer_list');
        }

        return $this->render('/admin/customer/add_address.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/customer/delete/address/{id}", name="admin_customer_delete_address")
     * @@Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("address", options={"mapping": {"id": "id"}})
     *
     * @param Address $address
     * @param CustomerManager $customerManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAddressCustomer(Address $address, CustomerManager $customerManager)
    {
        $customerManager->deleteAddressCustomer($address);

        $this->addFlash('success', "L'address a bien été supprimée");

        return $this->redirectToRoute('admin_customer_list');
    }
}
