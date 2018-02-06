<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ContactBookRepository extends EntityRepository {

	public function findByContactsGreaterThan($number) {

		$qb=$this->getEntityManager()->createQueryBuilder();
		return $qb->select('cb')
			->from('AppBundle:ContactBook', 'cb')
			->join('cb.contacts', 'c')
			->groupBy('cb.id')
			->having($qb->expr()->gt($qb->expr()->count('c.id'), $number))
			->getQuery()
			->getResult();
	}
}
