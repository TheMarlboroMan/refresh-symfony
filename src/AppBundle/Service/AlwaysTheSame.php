<?php
namespace AppBundle\Service;

class AlwaysTheSame {

	private $counter;

	public function __construct() {

		$this->counter=0;
	}

	public function addToCounter() {

		++$this->counter;
	}

	public function getCounter() {

		return $this->counter;
	}
}
