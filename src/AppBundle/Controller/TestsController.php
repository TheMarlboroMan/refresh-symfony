<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Person;
use AppBundle\Repository\PersonRepository;

class TestsController extends Controller {

	public function usePersonRepositoryAction($paramname) {

		$people=$this->get('doctrine')->getRepository('AppBundle:Person')->findAllNameLike($paramname);

		$contents="No people found by ".$paramname;
		if(count($people)) {
			$contents=substr(array_reduce($people, function($carry, Person $item) {$carry.=$item->getName().' '.$item->getSurname().', '; return $carry;}, "People found by ".$paramname.": "), 0, -2);
		}

		return $this->render('first-template.html.twig', ['something' => $contents]);
	}
}
