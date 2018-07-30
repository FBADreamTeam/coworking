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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        return $this->render('admin/employee_list.html.twig', [
            'employees' => $employees,
        ]);
    }

    /**
     * @Route("/employee/new", name="admin_employee_new")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
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