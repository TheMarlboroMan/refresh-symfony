<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PersonRepository extends EntityRepository {

	private static $sum=1;

	public function findAllNameLike($name) {

		echo self::$sum;
		++self::$sum;

		$dql_string="SELECT p FROM AppBundle:Person p WHERE p.name LIKE :paramname ORDER by p.name ASC";

		return $this->getEntityManager()
			->createQuery($dql_string)
			->setParameter('paramname', '%'.$name.'%')
			->getResult();
	}
}
