<?php
namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * Locations Table
 */
class LocationsTable extends Table
{

	public function initialize(array $config = [])
	{
		parent::initialize($config);

		$this->addBehavior('Burzum/CakeGeocoder.Geocoder', [

		]);
	}

}
