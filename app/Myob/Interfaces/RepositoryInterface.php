<?php

namespace App\Myob\Interfaces;

interface RepositoryInterface {

	public function getByID( $entity_name, $entity_id, $entity );
	public function create( $data );
	public function update( $data );
	public function delete( $entity_id, $entity_name );
}
