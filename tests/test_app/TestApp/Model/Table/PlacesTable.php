<?php
namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * Places Table
 */
class PlacesTable extends Table
{

	public function initialize(array $config = [])
	{
		parent::initialize($config);

		$this->addBehavior('Burzum/CakeGeocoder.Geocoder', [

		]);
	}

}
