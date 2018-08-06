<?php

namespace App\Controller\Admin;

use App\Entity\Room;
use App\Form\RoomFormType;
use App\Managers\RoomManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/_secure/room")
 */
class RoomController extends Controller
{
    /**
     * @Route("/", name="room_index")
     * @Method({"GET"})
     */
    public function index()
    {
        $rooms = $this->getDoctrine()->getRepository(Room::class)->findAll();

        return $this->render('admin/room/index.html.twig', [
            'rooms' => $rooms,
        ]);
    }

    /**
     * @Route("/add", name="room_add")
     * @Method({"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param RoomManager $roomManager
     *
     * @param Packages $packages
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addRoom(Request $request, RoomManager $roomManager, Packages $packages)
    {
        $room = new Room();

        //Creation du formulaire
        $form = $this->createForm(RoomFormType::class, $room, ['image_url' => $packages->getUrl($this->getParameter('assets_public_dir').'office/bureau-amenage.jpeg')])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roomManager->createRoom($room);

            $this->addFlash('success', 'Pièce correctement créée !');

            return $this->redirectToRoute('room_index', [
            ]);
        }

        return $this->render('admin/room/form.html.twig', [
            'titleform' => 'Creation d\'une nouvelle pièce',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="room_show")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Room $room
     *
     * @return Response
     */
    public function showRoom(Room $room): Response
    {
        return $this->render('admin/room/show.html.twig', [
            'room' => $room,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="room_edit")
     * @Method({"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Room $room
     * @param Request $request
     * @param RoomManager $roomManager
     *
     * @param Packages $packages
     * @return Response
     */
    public function editRoom(Room $room, Request $request, RoomManager $roomManager, Packages $packages): Response
    {
        //Creation du formulaire
        $form = $this->createForm(RoomFormType::class, $room, ['image_url' => $packages->getUrl($this->getParameter('room_assets_public_dir') . $room->getFeaturedImage())])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roomManager->editRoom($room);

            $this->addFlash('success', 'Modification éffectuée !');

            return $this->redirectToRoute('room_edit', [
                'id' => $room->getId(),
            ]);
        }

        return $this->render('admin/room/form.html.twig', [
            'titleform' => 'Edition d\'une pièce',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="room_delete")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Room        $room
     * @param RoomManager $roomManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteRoom(Room $room, RoomManager $roomManager): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $roomManager->deleteRoom($room);

        $this->addFlash(
            'success',
            'Your changes were saved!'
        );

        return $this->redirectToRoute('room_index');
    }
}
