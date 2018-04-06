<?php
namespace Burzum\CakeGeocoder\Model;

use ArrayObject;
use Cake\ORM\Query;

trait GeoFindersTrait {

	/**
	 * @link https://stackoverflow.com/questions/38462718/cakephp-query-closest-latitude-longitude-from-database
	 */
	public function findNearby(Query $query, ArrayObject $options) {
		if (!isset($options['mode'])) {
			$options['mode'] = 'where';
		}

		$distanceField = '(3959 * acos (cos ( radians(:latitude) )
			 * cos( radians( Sightings.latitude ) )
			 * cos( radians( Sightings.longitude )
			 - radians(:longitude) )
			 + sin ( radians(:latitude) )
			 * sin( radians( Sightings.latitude ) )))';

		$query->select(['distance' => $distanceField]);

		if ($options['mode'] === 'where') {
			$query->where(["$distanceField < " => $options['distance']]);
		} else {
			$query->having(['distance < ' => $options['distance']]);
		}

		return $query
			->bind(':latitude', $options['latitude'], 'float')
			->bind(':longitude', $options['longitude'], 'float');
	}

	public function getNearbyQuery($longitude, $latitude, $distance, $mode = 'where') {
		return $this->find('nearby', [
			'longitude' => $longitude,
			'latitude' => $latitude,
			'distance' => $distance,
			'mode' => $mode
		]);
	}

}

class DistanceQueryBuilder {

	const WHERE_MODE = 'where';
	const HAVING_MODE = 'having';

	public $mode = self::WHERE_MODE;
	public $longitudeField = 'longitude';
	public $latitudeField = 'latitude';
	public $distanceFieldSql = '(3959 * acos (cos ( radians(:latitude) )
		 * cos( radians( Sightings.latitude ) )
		 * cos( radians( Sightings.longitude )
		 - radians(:longitude) )
		 + sin ( radians(:latitude) )
		 * sin( radians( Sightings.latitude ) )))';

	public function __construct(Query $query) {
		$this->query;
	}

	public function setLongitudeField($fieldName) {
		$this->longitudeField = $fieldName;

		return $this;
	}

	public function setLatitudeField($fieldName) {
		$this->latitudeField = $fieldName;

		return $this;
	}

	public function setMode() {

	}

	public function build(float $longitude, float $latitude, float $distance) {
		$distanceField = '(3959 * acos (cos ( radians(:latitude) )
			 * cos( radians( Sightings.latitude ) )
			 * cos( radians( Sightings.longitude )
			 - radians(:longitude) )
			 + sin ( radians(:latitude) )
			 * sin( radians( Sightings.latitude ) )))';

		$this->query->select(['distance' => $distanceField]);

		if ($this->mode === self::WHERE_MODE) {
			$this->query->where(["$distanceField < " => $distance]);
		} else {
			$this->query->having(['distance < ' => $distance]);
		}

		return $this->query
			->bind(':latitude', $latitude, 'float')
			->bind(':longitude', $longitude, 'float');
	}
}
