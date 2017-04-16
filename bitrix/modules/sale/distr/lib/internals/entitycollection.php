<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Sale;
use Bitrix\Main;

abstract class EntityCollection
	extends CollectionBase
{
	private $index = -1;

	protected function __construct()
	{

	}

	/**
	 * @param CollectableEntity $item
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Sale\Result
	 */
	public function onItemModify(CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
		return new Sale\Result();
	}

	/**
	 * @internal
	 *
	 * @param $index
	 * @return mixed
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function deleteItem($index)
	{
		if (!isset($this->collection[$index]))
			throw new Main\ArgumentOutOfRangeException("collection item index wrong");

		$oldItem = $this->collection[$index];

		/** @var Main\Entity\Event $event */
		$event = new Main\Event('sale', 'OnBeforeCollectionDeleteItem', array(
			'COLLECTION' => $this->collection,
			'ENTITY' => $oldItem,
		));
		$event->send();

		unset($this->collection[$index]);

		return $oldItem;
	}

	/**
	 * @param CollectableEntity $item
	 * @return CollectableEntity
	 * @throws Main\ArgumentTypeException
	 */
	protected function addItem(CollectableEntity $item)
	{
		$this->index++;
		$item->setInternalIndex($this->index);
		$this->collection[$this->index] = $item;

		/** @var Main\Entity\Event $event */
		$event = new Main\Event('sale', 'OnCollectionAddItem', array(
			'COLLECTION' => $this->collection,
			'ENTITY' => $item,
		));
		$event->send();

		return $item;
	}

	public function clearCollection()
	{
		/** @var Main\Entity\Event $event */
		$event = new Main\Event('sale', 'OnBeforeCollectionClear', array(
			'COLLECTION' => $this->collection,
		));
		$event->send();
		/** @var CollectableEntity $item */
		foreach ($this->collection as $item)
			$item->delete();
	}


	/**
	 * @param $id
	 * @return CollectableEntity| bool
	 */
	public function getItemById($id)
	{
		if (intval($id) <= 0)
		{
			return false;
		}

		$index = $this->getIndexById($id);
		if ($index === false)
		{
			return false;
		}

		if (isset($this->collection[$index]))
		{
			return $this->collection[$index];
		}

		return false;
	}


	/**
	 * @param $id
	 * @return int|bool
	 */
	public function getIndexById($id)
	{
		if (intval($id) <= 0)
		{
			return false;
		}

		/** @var CollectableEntity $item */
		foreach ($this->collection as $item)
		{
			if ($item->getId() > 0 && $id == $item->getId())
			{
				return $item->getInternalIndex();
			}
		}
		return false;
	}

	abstract protected function getEntityParent();

	/**
	 * @param bool $isMeaningfulField
	 * @return bool
	 */
	public function isStartField($isMeaningfulField = false)
	{
		$parent = $this->getEntityParent();
		if ($parent === null)
			return false;

		return $parent->isStartField($isMeaningfulField);
	}

	/**
	 * @return bool
	 */
	public function clearStartField()
	{
		$parent = $this->getEntityParent();
		if ($parent === null)
			return false;
		return $parent->clearStartField();
	}

	/**
	 * @return bool
	 */
	public function hasMeaningfulField()
	{
		$parent = $this->getEntityParent();
		if ($parent === null)
			return false;
		return $parent->hasMeaningfulField();
	}

	/**
	 * @param bool $hasMeaningfulField
	 * @return Sale\Result
	 */
	public function doFinalAction($hasMeaningfulField = false)
	{
		$parent = $this->getEntityParent();
		if ($parent === null)
			return new Sale\Result();

		return $parent->doFinalAction($hasMeaningfulField);
	}

	/**
	 * @return bool
	 */
	public function isMathActionOnly()
	{
		$parent = $this->getEntityParent();
		if ($parent === null)
			return false;

		return $parent->isMathActionOnly();
	}

	/**
	 * @param bool|false $value
	 * @return bool
	 */
	public function setMathActionOnly($value = false)
	{
		$parent = $this->getEntityParent();
		if ($parent == null)
			return false;

		return $parent->setMathActionOnly($value);
	}

	/**
	 * @return bool
	 */
	public function isChanged()
	{
		if (count($this->collection) > 0)
		{
			/** @var Entity $item */
			foreach ($this->collection as $item)
			{
				if ($item->isChanged())
					return true;
			}
		}
		return false;
	}
}
