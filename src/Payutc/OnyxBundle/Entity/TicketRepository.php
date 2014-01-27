<?php

namespace Payutc\OnyxBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

use Payutc\OnyxBundle\Entity\Base\Deletable\DeletableEntityRepositoryInterface;

/**
 * TicketRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TicketRepository extends EntityRepository implements DeletableEntityRepositoryInterface
{
	/**
     * Find all entities that have removed_at set up to null.
     *
     * @return array
     */
	public function findAll()
	{
		return $this->findAllNotDeleted();
	}

	/**
     * Find all entities that have removed_at set up to null.
     *
     * @return array
     */
	public function findAllNotDeleted()
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('t')
			->from('PayutcOnyxBundle:Ticket', 't')
			->where($qb->expr()->isNull('t.removedAt'))
		;

		return $qb->getQuery()->getResult();
	}

	/**
     * Find all entities that have removed_at set up to null and a defined price.
     *
     * @param Price $price
     * @return array
     */
	public function findAllNotDeletedByPrice($price)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('t')
			->from('PayutcOnyxBundle:Ticket', 't')
			->where($qb->expr()->isNull('t.removedAt'))
			->andWhere('t.price = :price')
			->setParameter('price', $price)
		;

		return $qb->getQuery()->getResult();
	}

	/**
     * Find all entities that have removed_at set up to null and a defined seller.
     *
     * @param User $seller
     * @return array
     */
	public function findAllNotDeletedBySeller($seller)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('t')
			->from('PayutcOnyxBundle:Ticket', 't')
			->where($qb->expr()->isNull('t.removedAt'))
			->andWhere('t.seller = :seller')
			->setParameter('seller', $seller)
		;

		return $qb->getQuery()->getResult();
	}

	/**
     * Find all entities that have removed_at set up to null and a defined buyer.
     *
     * @param User $buyer
     * @return array
     */
	public function findAllNotDeletedByBuyer($buyer)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('t')
			->from('PayutcOnyxBundle:Ticket', 't')
			->where($qb->expr()->isNull('t.removedAt'))
			->andWhere('t.buyer = :buyer')
			->setParameter('buyer', $buyer)
		;

		return $qb->getQuery()->getResult();
	}

	/**
     * Find all entities that have removed_at set up to null, a defined price and a defined buyer.
     *
     * @param Price $price
     * @param User $buyer
     * @return array
     */
	public function findAllNotDeletedByPriceAndBuyer($price, $buyer)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('t')
			->from('PayutcOnyxBundle:Ticket', 't')
			->where($qb->expr()->isNull('t.removedAt'))
			->andWhere('t.price = :price')
			->andWhere('t.buyer = :buyer')
			->setParameter('price', $price)
			->setParameter('buyer', $buyer)
		;

		return $qb->getQuery()->getResult();
	}

	/**
     * Get the count of all paid tickets for a given event.
     *
     * @param Event $event
     * @return integer
     */
	public function countAllPaidForEvent($event)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select($qb->expr()->count('t.id'))
			->from('PayutcOnyxBundle:Ticket', 't')
			->leftJoin('t.price', 'p')
			->where($qb->expr()->isNull('t.removedAt'))
			->andWhere($qb->expr()->isNotNull('t.paidAt'))
			->andWhere('p.event = :event')
			->setParameter('event', $event)
		;

		return $qb->getQuery()->getSingleScalarResult();
	}

	/**
     * Get the count of all paid tickets for the given event and buyer.
     *
     * @param Event $event
     * @param User $buyer
     * @return integer
     */
	public function countAllPaidForEventAndBuyer($event, $buyer)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select($qb->expr()->count('t.id'))
			->from('PayutcOnyxBundle:Ticket', 't')
			->leftJoin('t.price', 'p')
			->where($qb->expr()->isNull('t.removedAt'))
			->andWhere($qb->expr()->isNotNull('t.paidAt'))
			->andWhere('t.buyer = :buyer')
			->andWhere('p.event = :event')
			->setParameter('buyer', $buyer)
			->setParameter('event', $event)
		;

		return $qb->getQuery()->getSingleScalarResult();
	}

	/**
     * Get the count of all paid tickets for the given event and buyer.
     *
     * @param Event $event
     * @param Price $price
     * @param User $buyer
     * @return integer
     */
	public function countAllPaidForEventAndPriceAndBuyer($event, $price, $buyer)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select($qb->expr()->count('t.id'))
			->from('PayutcOnyxBundle:Ticket', 't')
			->leftJoin('t.price', 'p')
			->where($qb->expr()->isNull('t.removedAt'))
			->andWhere($qb->expr()->isNotNull('t.paidAt'))
			->andWhere('t.price = :price')
			->andWhere('t.buyer = :buyer')
			->andWhere('p.event = :event')
			->setParameter('price', $price)
			->setParameter('buyer', $buyer)
			->setParameter('event', $event)
		;

		return $qb->getQuery()->getSingleScalarResult();
	}

	/**
     * Find one entity by id that have removed_at set up to null.
     *
     * @return Event
     */
	public function findOneNotDeleted($id)
	{
		$ticket = null;

		$qb = $this->_em->createQueryBuilder();

		$qb->select('t')
			->from('PayutcOnyxBundle:Ticket', 't')
			->where($qb->expr()->isNull('t.removedAt'))
			->andWhere('t.id = :id')
			->setParameter('id', $id)
		;

		try {
			$ticket = $qb->getQuery()->getSingleResult();
		}
		catch (NoResultException $e) {}

		return $ticket;
	}
}