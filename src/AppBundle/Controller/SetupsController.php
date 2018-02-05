<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\BorrowedItem;

class SetupsController extends Controller {

	public function populateBorrowedItemsAction() {

		$em=$this->get('doctrine')->getManager();

		$setvalues=function(array $data) use ($em) {
			$item=new BorrowedItem;
			$item->setName($data[0]);
			$item->setBorrowedFrom($data[1]);
			$item->setDateBorrowed($data[2]);
			$em->persist($item);
		};

		$data=[ ["Snow Crash book", "My brother", new \DateTime('2015-01-12')],
			["A wireless keyboard", "My Mother", new \DateTime('2015-02-22')],
			["My Dark Side of the Moon record", "My Father", new \DateTime('2017-04-25')]];

		array_walk($data, $setvalues);

		$em->flush();

		return $this->render('master.html.twig');
	}

	public function readBorrowedItemsAction() {

		$retrieved_item_names=[];
		$append_data=function(BorrowedItem $item=null) use (&$retrieved_item_names) {
			if($item) {
				$retrieved_item_names[]=$item->getName().' from '.$item->getBorrowedFrom();
			}
		};

		$repository=$this->get('doctrine')->getRepository(BorrowedItem::class);
		$append_data($repository->find(1));
		$append_data($repository->findOneByName('My Dark Side of the Moon record'));
		$append_data($repository->findOneByBorrowedFrom('My Mother'));
		$append_data($repository->findOneById(9999));

		return $this->render('first-template.html.twig', ['something' => implode($retrieved_item_names, ', ')]);
	}

	public function updateBorrowedItemsAction() {

		$item=$this->get('doctrine')->getRepository(BorrowedItem::class)->find(1);
		$item->setName("Antique jacket")->setDateBorrowed(new \DateTime())->setBorrowedFrom("My friend");

		$em=$this->get('doctrine')->getManager();
		$em->persist($item);
		$em->flush();

		return $this->render('master.html.twig');
	}

	public function deleteBorrowedItemsAction() {

		$d=$this->get('doctrine');
		$item=$d->getRepository(BorrowedItem::class)->findOneByBorrowedFrom('My Mother');

		$em=$d->getManager();
		$em->remove($item);
		$em->flush();

		return $this->render('master.html.twig');
	}
}

