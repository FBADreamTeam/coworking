<?php
/**
 * Created by PhpStorm.
 * User: alexa
 * Date: 31/07/2018
 * Time: 11:35.
 */

namespace App\Controller\Admin;

use App\Entity\RoomOption;
use App\Form\RoomOptionFormType;
use App\Managers\RoomOptionManager;
use App\Repository\RoomOptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/_secure/room_option")
 */
class RoomOptionController extends Controller
{
    /**
     * @Route("/", name="room_option_index", methods={"GET"})
     *
     * @param RoomOptionRepository $roomOptionRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(RoomOptionRepository $roomOptionRepository)
    {
        $roomOptions = $roomOptionRepository->findAllWithRoomTypes();
        dump($roomOptions);

        return $this->render('admin/room_option/index.html.twig', [
            'roomOptions' => $roomOptions,
        ]);
    }

    /**
     * @Route("/add", name="room_option_add", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request           $request
     * @param RoomOptionManager $roomOptionManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addRoomOption(Request $request, RoomOptionManager $roomOptionManager)
    {
        $roomOption = new RoomOption();
        //Creation du formulaire
        $form = $this->createForm(RoomOptionFormType::class, $roomOption)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roomOptionManager->createRoomOption($roomOption);

            $this->addFlash('success', 'Option correctement créée !');

            return $this->redirectToRoute('room_option_index', [
            ]);
        }

        return $this->render('admin/room_option/form.html.twig', [
            'titleform' => 'Creation d\'une nouvelle pièce',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="room_option_show", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param RoomOption           $id
     * @param RoomOptionRepository $roomOptionRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showRoomOption(RoomOption $id, RoomOptionRepository $roomOptionRepository): \Symfony\Component\HttpFoundation\Response
    {
        $roomOption = $roomOptionRepository->findOneRoomOptionWithRoomTypes($id);

        return $this->render('admin/room_option/show.html.twig', [
            'roomOption' => $roomOption[0],
        ]);
    }

    /**
     * @Route("/edit/{id}", name="room_option_edit", methods={"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param RoomOption        $roomOption
     * @param Request           $request
     * @param RoomOptionManager $roomOptionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editRoomOption(RoomOption $roomOption, Request $request, RoomOptionManager $roomOptionManager): \Symfony\Component\HttpFoundation\Response
    {
        //Creation du formulaire
        $form = $this->createForm(RoomOptionFormType::class, $roomOption)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roomOptionManager->editRoomOption();

            $this->addFlash('success', 'Modification éffectuée !');

            return $this->redirectToRoute('room_option_edit', [
                'id' => $roomOption->getId(),
            ]);
        }

        return $this->render('admin/room_option/form.html.twig', [
            'titleform' => 'Edition d\'une option',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="room_option_delete", methods={"DELETE"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param RoomOption        $roomOption
     * @param RoomOptionManager $roomOptionManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteRoomOption(RoomOption $roomOption, RoomOptionManager $roomOptionManager): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $roomOptionManager->deleteRoomOption($roomOption);

        $this->addFlash(
            'success',
            'Your changes were saved!'
        );

        return $this->redirectToRoute('room_option_index');
    }
}
