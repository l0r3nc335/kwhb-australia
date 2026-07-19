<?php

namespace App\Myob\Services\Employee;

use App\Myob\Model\Employee;
//use App\Myob\Services\Services;
//use App\Myob\Repositories\BaseRepository;

class EmployeeServices
{

	protected $repository;

	public function __construct()
	{
		// $this->repository = new BaseRepository();
	}

	public function list()
	{
		$entity = (new Employee(array()))->getAll();
		return $entity;
		return "list"; //test
	}

	public function get($entity_id)
	{
		$entity = (new Employee(array()))->getData();
		return $entity;
		//return $this->repository->getByID('contact', $entity_id, $entity); // true to return all fields
	}

	public function fetchEmployeeByDisplayID($displayID)
	{
		$id = strlen($displayID) == 6 ? $displayID : "EMP" . sprintf('%05d', $displayID);
		$entity = (new Employee(array()))->fetchEmployeeByDisplayID($id);
		return $entity;
		//return $this->repository->getByID('contact', $entity_id, $entity); // true to return all fields
	}

	public function store($params)
	{
		//$params = $this->params->all();
		$entity = (new Employee($params))->Create();
		return $entity;
		// return $this->repository->create($entity);
	}

	public function modify($entity_id)
	{
		$params = $this->params->all();
		$entity = (new Employee($params))->Update($entity_id);
		return $this->repository->update($entity);
	}

	// public function delete($entity_id)
	// {
	// 	return $this->repository->delete('contact', $entity_id);
	// }
}
