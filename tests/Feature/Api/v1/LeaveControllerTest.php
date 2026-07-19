<?php

namespace Tests\Feature\Api\v1;

use App\Core\v1\Leave\Models\Leave;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LeaveControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_fetch_leaves()
    {
        $response = $this->getJson('/api/v1/leaves');
        $response->assertOk();
    }

    public function test_create_leave_success()
    {
        $employee = $this->user;
        $assignTo = User::query()->whereNot('id', $employee->id)->first();

        $params = [
            'user_id' => $employee->id,
            'assigned_to' => $assignTo->id,
            'type' => Leave::TYPE_PERSONAL,
            'date' => now()->format('Y-m-d')
        ];

        $response = $this->postJson('/api/v1/leaves', $params);
        $response->assertOk();
        $data = json_decode($response->getContent());
        $createdLeave = $data->leave;

        $this->assertNotEmpty($createdLeave);
        $this->assertEquals(Leave::TYPE_PERSONAL, $createdLeave->type);
    }

    public function test_update_leave_success()
    {
        $leave = Leave::first();
        $this->assertNotEmpty($leave);
    }
}
