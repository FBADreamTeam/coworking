<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 26/07/2018
 * Time: 15:41
 */

namespace App\Controller\Admin;

use App\Entity\Employee;
use App\Form\EmployeeType;
use App\Form\CustomerLoginType;
use App\Managers\EmployeeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/_secure")
 * Class AdminController
 * @package App\Controller\Admin
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="admin_index")
     */
    public function index(): Response
    {
        return $this->render('/admin/admin_index.html.twig');
    }

    /**
     * @Route("/login", name="admin_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return RedirectResponse|Response
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
     * @Route("/employee/list", name="admin_employee_list")
     */
    public function listEmployees()
    {
        $employees = $this->getDoctrine()->getRepository(Employee::class)->findAll();

        return $this->render('admin/employee/employee_list.html.twig', [
            'employees' => $employees,
        ]);
    }

    /**
     * @Route("/employee/new", name="admin_employee_new")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function addEmployee(Request $request, UserPasswordEncoderInterface $encoder, EmployeeManager $employeeManager)
    {
        $employee = new Employee();

        $form = $this->createForm(EmployeeType::class, $employee, ['role' => 'ROLE_ADMIN', 'context' => 'create']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            // Gestion de la modification du mot de passe
            $password = $form->get('password')->getData();
            $passwordConfirm = $form->get('password_confirm')->getData();

            if (!empty($password) || !empty($passwordConfirm)){
                if ($password === $passwordConfirm) {
                    $employee->setPassword($encoder->encodePassword($employee, $password));
                } else {
                    $this->addFlash('notice', "Les mots de passes ne sont pas identiques");

                    return $this->render('/admin/employee/add_employee.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            }

            $employeeManager->createEmployee($employee);

            $this->addFlash('success', "Le compte a bien été créé");

            return $this->redirectToRoute('admin_employee_list');

        }

        return $this->render('/admin/employee/add_employee.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/employee/edit/{id}", name="admin_employee_edit")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Employee $employee
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function editEmployee(Employee $employee, Request $request, UserPasswordEncoderInterface $encoder, EmployeeManager $employeeManager)
    {
        $form = $this->createForm(EmployeeType::class, $employee, ['role' => 'ROLE_ADMIN', 'context' => 'edit']);

        $emailEmployee = $employee->getEmail(); // Mail du user en Bdd

        $form->handleRequest($request); // A ce moment, le formulaire récupère les données du formulaire

        $emailEmployeeForm = $employee->getEmail(); // Mail du user saisi dans le formulaire

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion des doublons
            if ($emailEmployee !== $emailEmployeeForm) {
                if ($employeeManager->checkDuplicateEmail($emailEmployeeForm)) {
                    $this->addFlash('notice', "Le mail existe déjà");

                    return $this->render('/admin/employee/edit_employee.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            }

            // Gestion de la modification du mot de passe
            $password = $form->get('password')->getData();
            $passwordConfirm = $form->get('password_confirm')->getData();

            if (!empty($password) || !empty($passwordConfirm)){
                if ($password === $passwordConfirm) {
                    $employee->setPassword($encoder->encodePassword($employee, $password));
                } else {
                    $this->addFlash('notice', "Les mots de passes ne sont pas identiques");

                    return $this->render('/admin/employee/edit_employee.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            }

            $employeeManager->updateEmployee();

            $this->addFlash('success', "Le compte a bien été modifié");

            return $this->redirectToRoute('admin_employee_list');

        }

        return $this->render('/admin/employee/edit_employee.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/edit", name="profile_edit")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function editProfile(Request $request, UserPasswordEncoderInterface $encoder, EmployeeManager $employeeManager)
    {
        $employee = $this->getUser();

        $form = $this->createForm(EmployeeType::class, $employee, ['role' => $employee->getRole()->getLabel(), 'context' => 'edit']);

        $emailEmployee = $employee->getEmail(); // Mail du user en Bdd

        $form->handleRequest($request); // A ce moment, le formulaire récupère les données du formulaire

        $emailEmployeeForm = $employee->getEmail(); // Mail du user saisi dans le formulaire

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion des doublons
            if ($emailEmployee !== $emailEmployeeForm) {
                if ($employeeManager->checkDuplicateEmail($emailEmployeeForm)) {
                    $this->addFlash('notice', "Le mail existe déjà");

                    return $this->render('/admin/profile/edit_profile.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            }

            // Gestion de la modification du mot de passe
            $password = $form->get('password')->getData();
            $passwordConfirm = $form->get('password_confirm')->getData();

            if (!empty($password) || !empty($passwordConfirm)){
                if ($password === $passwordConfirm) {
                    $employee->setPassword($encoder->encodePassword($employee, $password));
                } else {
                    $this->addFlash('notice', "Les mots de passes ne sont pas identiques");

                    return $this->render('/admin/profile/edit_profile.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            }

            $employeeManager->updateEmployee();

            $this->addFlash('success', "Le compte a bien été modifié");

            return $this->redirectToRoute('admin_index');

        }

        return $this->render('/admin/profile/edit_profile.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/employee/delete/{id}", name="admin_employee_delete")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Employee $employee
     * @param EmployeeManager $employeeManager
     * @return RedirectResponse
     */
    public function deleteEmployee(Employee $employee, EmployeeManager $employeeManager)
    {
        if ($employeeManager->deleteEmployee($employee) ) {
            $this->addFlash('success', "L'employé(e) a bien été supprimé(e)");
            $this->redirectToRoute('admin_index');
        } else {
            $this->addFlash('notice', "L'employé(e) n'a pas été supprimé(e)");
            $this->redirectToRoute('admin_index');
        }

        return $this->redirectToRoute('admin_employee_list');
    }

    /**
     * @Route("/employee/show/{id}", name="admin_employee_show")
     *
     * @param Employee $employee
     * @return Response
     */
    public function showEmployee(Employee $employee): Response
    {
        if (empty($employee)) {
            throw new Exception("L'employé(e) n'existe pas");
        }

        return $this->render('admin/employee/show_employee.html.twig', [
            'employee' => $employee
        ]);
    }

    /**
     * @Route("/dt-trans", name="dt_trans")
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    public function getDataTablesTranslations(Request $request, TranslatorInterface $translator): JsonResponse
    {
        $locale = $request->getLocale();

        $translations = [
            'sProcessing'       => $translator->trans('sProcessing', [], 'datatables', $locale),
            'sSearch'           => $translator->trans('sSearch', [], 'datatables', $locale),
            'sLengthMenu'       => $translator->trans('sLengthMenu', [], 'datatables', $locale),
            'sInfo'             => $translator->trans('sInfo', [], 'datatables', $locale),
            'sInfoEmpty'        => $translator->trans('sInfoEmpty', [], 'datatables', $locale),
            'sInfoFiltered'     => $translator->trans('sInfoFiltered', [], 'datatables', $locale),
            'sInfoThousands'     => $translator->trans('sInfoThousands', [], 'datatables', $locale),
            'sInfoPostFix'      => '',
            'sLoadingRecords'   => $translator->trans('sLoadingRecords', [], 'datatables', $locale),
            'sZeroRecords'      => $translator->trans('sZeroRecord', [], 'datatables', $locale),
            'sEmptyTable'       => $translator->trans('sEmptyTable', [], 'datatables', $locale),
            'oPaginate'         => [
                    'sFirst'    => $translator->trans('sFirst', [], 'datatables', $locale),
                    'sPrevious' => $translator->trans('sPrevious', [], 'datatables', $locale),
                    'sNext'     => $translator->trans('sNext', [], 'datatables', $locale),
                    'sLast'     => $translator->trans('sLast', [], 'datatables', $locale),
            ],
            'oAria'             => [
                    'sSortAscending'    => $translator->trans('sSortAscending', [], 'datatables', $locale),
                    'sSortDescending'   => $translator->trans('sSortDescending', [], 'datatables', $locale),
            ],
            'select'            => [
                    'rows' => [
                        '_' => $translator->trans('s_', [], 'datatables', $locale),
                        '0' => $translator->trans('s0', [], 'datatables', $locale),
                        '1' => $translator->trans('s1', [], 'datatables', $locale),
                    ]
            ]
        ];

        return new JsonResponse($translations);
    }
}