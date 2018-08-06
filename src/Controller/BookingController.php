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
use App\Managers\RoomManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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

    /**
     * @Route("/filter", name="booking_filter")
     * @Method("GET")
     * @param Request $request
     * @param RoomManager $roomManager
     * @return string|RedirectResponse
     */
    public function filter(Request $request, RoomManager $roomManager)
    {
        if ($request->isXmlHttpRequest()) {
            // Filter rooms by availability
//            $rooms = $roomManager->filter($request->get('startDate'), $request->get('endDate'));
            $data = [];

            // get all types of rooms
            $types = $this->getDoctrine()->getRepository(RoomType::class)->findAll();

            /** @var RoomType $type */
            foreach ($types as $type) {
                $data[$type->getId()] = $roomManager->filterByType($type, $request->get('startDate'), $request->get('endDate'));
            }

            return new JsonResponse(['rooms' => $roomManager->serialize($data)]);


        } else {
            $this->addFlash('notice', 'Sorry, unauthorized action');
            return $this->redirectToRoute('booking_index');
        }
    }
}