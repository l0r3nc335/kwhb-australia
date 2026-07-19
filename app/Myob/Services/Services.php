<?php

namespace App\Myob\Services;

use App\Myob\Model\Repositories\BaseRepository;

class Services extends BaseRepository
{

	public $params;
	public $attrs;

	public function params($params)
	{
		$this->params = $params;
		return $this;
	}
}
