<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 26/07/2018
 * Time: 15:41
 */

namespace App\Controller\Admin;


use App\Entity\Employee;
use App\Form\admin\EmployeeType;
use App\Form\CustomerLoginType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends Controller
{
    /**
     * @Route("/admin/index", name="admin_index")
     */
    public function index()
    {
        return $this->render('/admin/admin_index.html.twig');
    }

    /**
     * @Route("/admin/login", name="admin_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_index');
        }

        $form = $this->createForm(CustomerLoginType::class, [
            'email' => $authenticationUtils->getLastUsername()
        ]);

        // Récupération du message d'erreur
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('/admin/admin_login.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * @Route("/admin/add_employee", name="admin_add_employee")
     */
    public function addEmployee(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $employee = new Employee();

        $form = $this->createForm(EmployeeType::class, $employee);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();

            $emailEmployee = $employee->getEmail();

            // Gestion des doublons
            $emailEmployeeBdd = $em->getRepository(Employee::class)->findByEmail($emailEmployee);

            if (!empty($emailEmployeeBdd)) {
                $this->addFlash('notice', "L'employé existe déjà");
            } else {
                $employee->setPassword($encoder->encodePassword($employee, $employee->getPassword()));
                $em->persist($employee);
                $em->flush();

                $this->addFlash('success', "Le compte a bien été créé");

                return $this->redirectToRoute('admin_index');
            }
        }

        return $this->render('/admin/add_employee.html.twig', [
            'form' => $form->createView()
        ]);
    }

}