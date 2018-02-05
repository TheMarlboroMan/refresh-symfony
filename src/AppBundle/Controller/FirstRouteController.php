<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FirstRouteController extends Controller {

	public function showFirstRouteAction() {

		return new Response("<html><body>Hello!</body></html>");
	}
}
