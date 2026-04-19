<?php
namespace Advik\LearnPatch\Plugin;

class AddLandmarkToGrid
{
	private \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavConfig;
	private \Magento\Framework\App\ResourceConnection $resourceConnection;

	public function __construct(
		\Magento\Eav\Model\ResourceModel\Entity\Attribute $eavConfig,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
	) {
		$this->eavConfig = $eavConfig;
		$this->resourceConnection = $resourceConnection;
	}

	private function joinLandmarkTable($collection)
	{
		$select = $collection->getSelect();
		if (!str_contains($select->__toString(), 'landmark_table')) {
			$columnName = 'address_landmark';
			$attributeId = $this->eavConfig->getIdByCode('customer_address', $columnName);
			$tableName = $this->resourceConnection->getTableName('customer_address_entity_varchar');

			$select->joinLeft(
				['landmark_table' => $tableName],
				"main_table.entity_id = landmark_table.entity_id AND landmark_table.attribute_id = " . $attributeId,
				[$columnName => 'value']
			);
		}
		return $collection;
	}

	public function beforeLoad($subject, $printQuery = false, $logQuery = false)
	{
		$this->joinLandmarkTable($subject);
		return [$printQuery, $logQuery];
	}

	public function aroundAddFieldToFilter($subject, \Closure $proceed, $field, $condition = null)
	{
		if ($field === 'address_landmark') {
			// Filter chalne se pehle join lagana zaroori hai
			$this->joinLandmarkTable($subject);

			$value = $condition['like'] ?? $condition;
			$subject->getSelect()->where('landmark_table.value LIKE ?', $value);
			return $subject;
		}

		return $proceed($field, $condition);
	}
}