<?php

namespace App\Controller\Admin;

use App\Entity\Room;
use App\Managers\RoomManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/admin/room")
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
     */
    public function addRoom()
    {
    }

    /**
     * @Route("/show/{id}", name="room_show")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function showRoom()
    {
    }

    /**
     * @Route("/edit/{id}", name="room_edit")
     * @Method({"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Room $room
     * @param      $id   ID room
     */
    public function editRoom(Room $room, $id)
    {
    }

    /**
     * @Route("/delete/{id}", name="room_delete")
     * @Method({"DELETE"})
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
