<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FirstRouteController extends Controller {

	public function showFirstRouteAction() {

		return $this->render('first-template.html.twig', ['something' => 'A new beginning']);
	}
}
