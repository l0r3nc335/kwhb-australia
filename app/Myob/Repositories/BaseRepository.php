<?php

namespace App\Myob\Model\Repositories;

use App\Myob\Interfaces\RepositoryInterface;

class BaseRepository implements RepositoryInterface {

	private $client;

	public function __construct() {
		$settings                    = new \AlexaCRM\WebAPI\OData\OnlineSettings();
		$settings->instanceURI       = env( 'CRM_URL' );
		$settings->applicationID     = env( 'CRM_CLIENT_ID' );
		$settings->applicationSecret = env( 'CRM_CLIENT_SECRET' );
		$settings->apiVersion        = env( 'CRM_VERSION' );

		$middleware = new \AlexaCRM\WebAPI\OData\OnlineAuthMiddleware( $settings );

		$odataClient = new \AlexaCRM\WebAPI\OData\Client( $settings, $middleware );

		$this->client = new \AlexaCRM\WebAPI\Client( $odataClient );
	}

	// create a new record
	public function create( $entity ) {
		// dd($entity);
		$result = $this->client->Create( $entity );
		return $result;
	}

	// update record
	public function update( $entity ) {
		$result = $this->client->Update( $entity );
		return $result;
	}

	// remove record
	public function delete( $entity_name, $entity_id ) {
		$result = $this->client->Delete( $entity_name, $entity_id );
		return $result;
	}

	// get record by id
	public function getByID( $entity_name, $entity_id, $entity ) {
		return $this->client->Retrieve( $entity_name, $entity_id, new \AlexaCRM\Xrm\ColumnSet( $entity ) )->Attributes;
	}

	public function getAllData( $entity ) {
		$result = $this->client->RetrieveMultiple( $entity );
		return response()->json( compact( 'result' ) );
	}
}
