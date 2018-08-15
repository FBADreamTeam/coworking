<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Customer;
use App\Managers\BookingManager;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends Controller
{
    /**
     * @Route("/generate_invoice/{id_booking}/{id_user}", name="generate_invoice")
     * @ParamConverter("booking", options={"id"="id_booking"})
     * @ParamConverter("customer", options={"id"="id_user"})
     *
     * @IsGranted("ROLE_USER")
     *
     * @param Booking $booking
     * @param Customer $customer
     *
     * @return PdfResponse
     */
    public function generateInvoice(Booking $booking, Customer $customer): PdfResponse
    {
        // we verify that the customer in the parameters is the same as the owner of the booking
        // if not, an exception is thrown
        if (! BookingManager::checkBookingCustomerIsValid($booking, $this->getUser())) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The customer #%d is not the same as the one from the booking (#%d)',
                    $customer->getId(),
                    $booking->getCustomer()->getId()
                )
            );
        }

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
