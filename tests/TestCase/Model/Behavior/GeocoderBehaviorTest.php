<?php
declare(strict_types = 1);

namespace Burzum\CakeGeocoder\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * GeocoderBehavior Test
 */
class GeocoderBehaviorTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.Burzum/CakeGeocoder.Locations',
		'plugin.Burzum/CakeGeocoder.Places'
	];

	/**
	 * Setup
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * testGetGeoStringFromEntity
	 */
	public function testGetGeoStringFromEntity() {
		$locations = TableRegistry::get('Locations');
		$location = $locations->find()->first();
		$result = $locations->behaviors()->get('Geocoder')->getGeoStringFromEntity($location);
		//$result = $locations->getGeoStringFromEntity($location);
		dd($result);
	}

	/**
	 * testGeoCodeEntity
	 */
	public function testGeoCodeEntity() {
		$locations = TableRegistry::get('Locations');
		$behavior = $locations->behaviors()->get('Geocoder');

		$entity = $locations->find()->first();
		$entity->isNew(true);

		$result = $behavior->geocodeEntity($entity);
		debug($result);
		debug($entity);
	}

}
