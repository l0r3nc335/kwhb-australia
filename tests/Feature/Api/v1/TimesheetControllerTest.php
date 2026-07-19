<?php

namespace Tests\Feature\Api\v1;

use App\Core\v1\Timesheet\Models\Timesheet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TimesheetControllerTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     */
    public function test_categories(): void
    {
        $response = $this->getJson('/api/v1/timesheets/categories');
        $response->assertOk();

        $categories = json_decode($response->getContent(), true);

        $this->assertEquals(Timesheet::categories(), $categories);
    }

    public function test_timesheet_list_success()
    {
        $response = $this->getJson('/api/v1/timesheets');
        $response->assertOk();

        $timesheets = json_decode($response->getContent());
    }
}
