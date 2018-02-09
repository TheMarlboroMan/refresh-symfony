<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ServicesController extends Controller {

	public function firstServiceAction() {

		$service_says=$this->container->get('hello')->sayHello();
		return $this->render('first-template.html.twig', ['something' => 'Service says: '.$service_says]);
	}

	public function alwaysTheSameServiceAction() {

		$this->container->get('alwaysTheSame')->addToCounter();
		$this->container->get('alwaysTheSame')->addToCounter();
		$this->container->get('alwaysTheSame')->addToCounter();

		return $this->render('first-template.html.twig', ['something' => 'Total counter: '.$this->container->get('alwaysTheSame')->getCounter()]);
	}

	public function uniqueServiceAction() {

		$values=[];

		$this->container->get('uniqueService')->addToCounter();
		$values[]=$this->container->get('uniqueService')->getCounter();

		$this->container->get('uniqueService')->addToCounter();
		$values[]=$this->container->get('uniqueService')->getCounter();
		
		$service=$this->container->get('alwaysTheSame');
		for($i=0; $i<3; ++$i) $service->addToCounter();
		$values[]=$service->getCounter();

		//The values will be 0, 0 and 3. The first two times we add to values[] we got a brand new service!.
		$show_values=implode(', ', $values);
		return $this->render('first-template.html.twig', ['something' => 'Values are '.$show_values]);

	}
}
