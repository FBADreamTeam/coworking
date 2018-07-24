<?php

	namespace App\Controller;


	use Symfony\Bundle\FrameworkBundle\Controller\Controller;

	class DefaultController extends Controller
	{

		/**
		 * Just test route
		 */
		public function index()
		{
			return $this->render('index/index.html.twig', [
			]);
		}

	}