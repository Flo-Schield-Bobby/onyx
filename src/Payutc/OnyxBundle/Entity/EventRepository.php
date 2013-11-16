<?php

namespace Payutc\OnyxBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

use Payutc\OnyxBundle\Entity\Deletable\DeletableEntityRepositoryInterface;

/**
 * EventRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EventRepository extends EntityRepository implements DeletableEntityRepositoryInterface
{
	/**
     * Find all entities that have is_deleted property set up to false.
     *
     * @return array
     */
	public function findAllNotDeleted()
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('e')
			->from('PayutcOnyxBundle:Event', 'e')
			->where('e.isDeleted = :isDeleted')
			->setParameter('isDeleted', false)
		;

		return $qb->getQuery()->getResult();
	}

	/**
     * Find all entities that have is_deleted and is_hidden property set up to false.
     *
     * @return array
     */
	public function findAllActive()
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('e')
			->from('PayutcOnyxBundle:Event', 'e')
			->where('e.isDeleted = :isDeleted')
			->andWhere('e.isHidden = :isHidden')
			->setParameter('isDeleted', false)
			->setParameter('isHidden', false)
		;

		return $qb->getQuery()->getResult();
	}

	/**
     * Find one entity by id that have is_deleted and is_hidden property set up to false.
     *
     * @return array
     */
	public function findOneActive(int $id)
	{
		$event = null;

		$qb = $this->_em->createQueryBuilder();

		$qb->select('e')
			->from('PayutcOnyxBundle:Event', 'e')
			->where('e.isDeleted = :isDeleted')
			->andWhere('e.isHidden = :isHidden')
			->setParameter('isDeleted', false)
			->setParameter('isHidden', false)
		;

		try {
			$event = $qb->getQuery()->getSingleResult();
		}
		catch (NoResultException $e) {}

		return $event;
	}
}