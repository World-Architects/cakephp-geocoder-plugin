<?php
declare(strict_types = 1);

namespace Burzum\CakeGeocoder\Model\Behavior;

use Burzum\CakeGeocoder\Geocoder\Geocoder;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;

/**
 * Geocoder Behaviors
 */
class GeocoderBehavior extends Behavior
{
	/**
	 * Default Config
	 *
	 * @var array
	 */
	protected $_defaultConfig = [
		'geoFields' => [
			'longitude' => 'longitude',
			'latitude' => 'latitude',
		],
		'addressFields' => [
			'zip' => 'zip',
			'city' => 'city',
			'street' => 'street',
			'country' => 'country',
			'state' => 'state'
		],
		'geoStringBuilder' => 'buildGeoString',
		'implementedMethods' => [
			'buildGeoString',
			'getGeoStringFromEntity',
			'geocodeEntity',
			'requiresGeocoding'
		]
	];

	/**
	 * Geocoder Service class instance
	 */
	protected $geocoder;

	/**
	 * Model.beforeSave event handler
	 *
	 * @param \Cake\Event\Event $event Event
	 * @param \Cake\Datasource\EntityInterface $entity Entity
	 * @return void
	 */
	public function beforeSave(Event $event, EntityInterface $entity)
	{
		if ($this->requiresGeocoding($entity)) {
			$this->geocodeEntity($entity);
		}
	}

	/**
	 * Checks if an entity requires a new geocode lookup
	 *
	 * The entity will require a new lookup if:
	 * - the entity is new
	 * - or one of the entities address fields is dirty
	 *
	 * @param \Cake\Datasource\EntityInterface $entity Entity
	 * @return bool True if it requires a new lookup, the address has changed
	 */
	public function requiresGeocoding(EntityInterface $entity) {
		if ($entity->isNew()) {
			return true;
		}

		$addressFields = $this->getConfig('addressFields');
		foreach ($addressFields as $field) {
			if ($entity->isDirty($field)) {
				return true;
			}
		}

		return false;
	}

	public function geocodeEntity(EntityInterface $entity)
	{
		$eventManager = $this->getTable()->getEventManager();

		$location = $this->getGeoStringFromEntity($entity);
		$result = $this->getGeocoder()->geocode($location);

		if (empty($result)) {
			return false;
		}

		$this->mapGeocodeResultToEntity($result,  $entity);

		return true;
	}

	public function mapGeocodeResultToEntity($result, $entity) {
		$geoFields = $this->getConfig('geoFields');
		$addressFields = $this->getConfig('addressFields');

		$entity->set([
			$geoFields['longitude'] => $result['geometry']['coordinates'][0],
			$geoFields['latitude'] => $result['geometry']['coordinates'][1]
		]);
	}

	public function buildGeoString(EntityInterface $entity, $fields)
	{
		$parts = [];
		foreach ($fields as $field) {
			$parts[] = $entity->get($field);
		}

		return implode(', ', array_filter($parts));
	}

	public function getGeoStringFromEntity(EntityInterface $entity)
	{
		$geoString = '';
		$fields = $this->getConfig('addressFields');
		$builder = $this->getConfig('geoStringBuilder');

		if (is_string($builder)) {
			if ($builder === 'buildGeoString') {
				$geoString = $this->buildGeoString($entity, $fields);
			} else {
				$geoString = $this->getTable()->{$builder}($entity, $fields);
			}
		}

		if (is_callable($builder)) {
			$geoString = $builder($entity, $fields);
		}

		return $geoString;
	}

	/**
	 * Sets the geocoder instance
	 *
	 * @param mixed $geocoder Geocoder instance
	 * @return void
	 */
	public function setGeocoder($geocoder)
	{
		$this->getcoder = $geocoder;
	}

	/**
	 * Get geocoder
	 *
	 * @return mixed
	 */
	public function getGeocoder()
	{
		if (empty($this->geocoder)) {
			$this->geocoder = new Geocoder();
		}

		return $this->geocoder;
	}
}
