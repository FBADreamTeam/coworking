<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Customer;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends Controller
{
    /**
     * @Route("/invoice", name="index_invoice")
     */
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('invoice/invoice.html.twig', [
        ]);
    }

    /**
     * @Route("/generate_invoice/{id_booking}/{id_user}", name="generate_invoice")
     *
     * @param int $id_booking
     * @param int $id_user
     *
     * @return PdfResponse
     */
    public function generateInvoice(int $id_booking, int $id_user): PdfResponse
    {
        /** @var Booking $booking */
        $booking = $this->getDoctrine()->getRepository(Booking::class)->find($id_booking);

        /** @var Customer $customer */
        $customer = $this->getDoctrine()->getRepository(Customer::class)->find($id_user);
        $html = $this->renderView('invoice/invoice.html.twig', [
            'booking' => $booking,
            'bookingOptions' => $booking->getBookingOptions(),
            'customer' => $customer,
            'customerAdress' => $booking->getOrder()->getAddress(),
            'order' => $booking->getOrder(),
        ]);

        /** @var Pdf $pdf */
        $pdf = $this->get('knp_snappy.pdf');

        return new PdfResponse(
           $pdf->getOutputFromHtml($html),
            'invoice '.$booking->getId().'.pdf'
        );
    }
}
