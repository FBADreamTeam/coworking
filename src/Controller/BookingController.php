<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 31/07/2018
 * Time: 14:50
 */

namespace App\Controller;


use App\Entity\Room;
use App\Entity\RoomType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BookingController
 * @package App\Controller
 * @Route("/booking")
 */
class BookingController extends Controller
{
    /**
     * @Route("/", name="booking_index")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $roomTypes = $em->getRepository(RoomType::class)->findAll();

        $data = [];

        foreach ($roomTypes as $roomType) {
            $data[$roomType->getId()]['roomType'] = $roomType;
            $data[$roomType->getId()]['rooms'] = $em->getRepository(Room::class)->findBy(['roomType' => $roomType]);
        }

        return $this->render('booking/booking_index.html.twig', [
            'data'      => $data,
        ]);
    }
}