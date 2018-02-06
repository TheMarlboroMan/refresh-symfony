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

	public function relationshipTestsAction($quantity) {
		$books=$this->get('doctrine')->getRepository('AppBundle:ContactBook')->findByContactsGreaterThan($quantity);
		return $this->render('contacts.html.twig', ['books' => $books, 'quantity' => $quantity]);
	}

	public function relationshipTestsProcedureAction($quantity) {

		$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
		$rsm->addEntityResult('AppBundle:ContactBook', 'b');
		$rsm->addFieldResult('b', 'id', 'id');
		$rsm->addFieldResult('b', 'name', 'name');

		$qs="CALL contact_book_by_quantity(?)";
		$books=$this->get('doctrine')->getEntityManager()
			->createNativeQuery($qs, $rsm)
			->setParameter(1, $quantity)
			->getResult();

		return $this->render('contacts.html.twig', ['books' => $books, 'quantity' => $quantity]);
	}

	public function relationshipTestsProcedureFullAction($quantity) {

		$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
		$rsm->addEntityResult('AppBundle:ContactBook', 'cb');
		$rsm->addFieldResult('cb', 'cb_id', 'id');
		$rsm->addFieldResult('cb', 'cb_name', 'name');
		$rsm->addJoinedEntityResult('AppBundle:Contact', 'c', 'cb', 'contacts');
		$rsm->addFieldResult('c', 'c_id', 'id');
		$rsm->addFieldResult('c', 'c_name', 'name');
		$rsm->addFieldResult('c', 'c_phone', 'phone');
		$rsm->addFieldResult('c', 'c_email', 'email');

		$qs="CALL contact_book_by_quantity(?)";
		$books=$this->get('doctrine')->getEntityManager()
			->createNativeQuery($qs, $rsm)
			->setParameter(1, $quantity)
			->getResult();

		return $this->render('contacts.html.twig', ['books' => $books, 'quantity' => $quantity]);
	}
}