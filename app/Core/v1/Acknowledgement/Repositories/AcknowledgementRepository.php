<?php

namespace App\Core\v1\Acknowledgement\Repositories;

use App\Core\v1\Acknowledgement\Models\Acknowledgement;
use Illuminate\Database\Eloquent\Model;

class AcknowledgementRepository
{
    public function model(): Model
    {
        return new Acknowledgement;
    }

    public function store(int $resourceId, bool $isAcknowledged = false): Model
    {
        $ack = $this->model();
        $ack->resource_id = $resourceId;
        $ack->is_acknowledged = $isAcknowledged;
        $ack->user_id = auth()->id(); // <-- Set user_id to the authenticated user
        $ack->save();

        return $ack->fresh(['resource', 'user']);
    }

    public function update(Acknowledgement $ack, bool $isAcknowledged = false): Model
    {
        $ack->is_acknowledged = $isAcknowledged;
        $ack->save();

        return $ack->fresh(['resource', 'user']);
    }
}
