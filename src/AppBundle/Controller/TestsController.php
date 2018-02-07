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
		$rsm->addEntityResult('AppBundle\Entity\ContactBook', 'b')
			->addFieldResult('b', 'id', 'id')
			->addFieldResult('b', 'name', 'name');

		$qs="CALL contact_book_by_quantity(?)";
		$books=$this->get('doctrine')->getManager()
			->createNativeQuery($qs, $rsm)
			->setParameter(1, $quantity)
			->getResult();

		return $this->render('contacts.html.twig', ['books' => $books, 'quantity' => $quantity]);
	}

	public function relationshipTestsProcedureFullAction($quantity) {

		$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
		$rsm->addEntityResult('AppBundle:ContactBook', 'cb')
			->addFieldResult('cb', 'cb_id', 'id')
			->addFieldResult('cb', 'cb_name', 'name')
			->addJoinedEntityResult('AppBundle:Contact', 'c', 'cb', 'contacts')
			->addFieldResult('c', 'c_id', 'id')
			->addFieldResult('c', 'c_name', 'name')
			->addFieldResult('c', 'c_phone', 'phone')
			->addFieldResult('c', 'c_email', 'email');

		$qs="CALL contact_book_by_quantity_full(?);";
		$books=$this->get('doctrine')->getManager()
			->createNativeQuery($qs, $rsm)
			->setParameter(1, $quantity)
			->getResult();

		return $this->render('contacts.html.twig', ['books' => $books, 'quantity' => $quantity]);
	}

	public function procedureScalarAction() {

		$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
		$rsm->addScalarResult('id', 'book_id')
			->addScalarResult('total', 'contacts_total');

		$qs="CALL get_contact_count();";
		$result=$this->get('doctrine')->getManager()
			->createNativeQuery($qs, $rsm)
			->getResult();

		$reduce=function($carry, array $item) {
			$carry.="Book with id ".$item['book_id'].' has '.$item['contacts_total'].' contacts, ';
			return $carry;
		};

		$contents=substr(array_reduce($result, $reduce, "Book report: "), 0, -2);
		return $this->render('first-template.html.twig', ['something' => $contents]);
	}

	public function databaseFunctionAction() {

		$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
		$rsm->addScalarResult('total_count', 'this_does_not_really_matter');

		$qs="SELECT get_total_contact_count() AS total_count FROM DUAL";
		$result=$this->get('doctrine')->getManager()
			->createNativeQuery($qs, $rsm)
			->getSingleScalarResult();

		return $this->render('first-template.html.twig', ['something' => 'There are '.$result.' contact(s) in the whole database']);
	}

	public function procedureMaybeNullUglyAction($id) {

			$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
		$rsm->addScalarResult('name', 'contact_name');

		$qs="CALL get_contact_info_by_id(?);";
		$result=$this->get('doctrine')->getManager()
			->createNativeQuery($qs, $rsm)
			->setParameter(1, $id)
			->getResult();

		$result_name=count($result) ? $result[0]['contact_name']: 'nobody';
		return $this->render('first-template.html.twig', ['something' => 'With the id '.$id.' you can find '.$result_name]);
	}

	public function procedureMaybeNullLessUglyAction($id) {

		$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
		$rsm->addScalarResult('name', 'contact_name');
		//No need to map all values if we aren't going to use them.

		$qs="CALL get_contact_info_by_id(?);";
		$result=$this->get('doctrine')->getManager()
			->createNativeQuery($qs, $rsm)
			->setParameter(1, $id)
			->getOneOrNullResult();

		$result_name=$result ? $result['contact_name']: 'nobody';
		return $this->render('first-template.html.twig', ['something' => 'With the id '.$id.' you can find '.$result_name]);
	}

	public function procedureMaybeNullOkAction($id) {

		$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
		$rsm->addScalarResult('name', 'contact_name');
		//No need to map all values if we aren't going to use them.

		$qs="CALL get_contact_info_by_id(?);";
		$result=$this->get('doctrine')->getManager()
			->createNativeQuery($qs, $rsm)
			->setParameter(1, $id)
			->getOneOrNullResult();

		return $this->render('search-contact.html.twig', ['result' => $result, 'id' => $id]);
	}

	public function functionDatabaseExceptionAction($value) {

		$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
		$rsm->addScalarResult('result', 'result');

		$qs="SELECT odd_even(?) AS result FROM DUAL";

		$value_type=null;

		try {
			$result=$this->get('doctrine')->getManager()
				->createNativeQuery($qs, $rsm)
				->setParameter(1, $value)
				->getSingleScalarResult();
			$value_type='odd';
		}
		catch(\Doctrine\DBAL\Exception\DriverException $e) {
			$value_type='even';
		}

		return $this->render('first-template.html.twig', ['something' => $value.' is '.$value_type]);
	}

	public function procedureDatabaseExceptionAction($name, $phone, $email) {

		$rsm=new \Doctrine\ORM\Query\ResultSetMapping;
		$rsm->addScalarResult('result', 'result');

		$result=null;

		try {
			$qs="CALL create_contact(?,?,?);";
			$result=$this->get('doctrine')->getManager()
				->createNativeQuery($qs, $rsm)
				->setParameter(1, $name)
				->setParameter(2, $phone)
				->setParameter(3, $email)
				->getSingleScalarResult();
		}
		catch(\Doctrine\DBAL\Exception\DriverException $e) {
			//We won't do a thing here...
		}

		return $this->render('create-user.html.twig', ['id' => $result]);
	}
}
