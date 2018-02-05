<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FirstRouteController extends Controller {

	public function showFirstRouteAction() {

		$conn=$this->get('database_connection');
		list ($something)=$conn->fetchArray("SELECT NOW() AS now FROM DUAL");
		return $this->render('first-template.html.twig', ['something' => $something]);
	}
}
