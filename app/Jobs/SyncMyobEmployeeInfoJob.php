<?php

namespace App\Jobs;

use App\Models\BankInformation;
use App\Models\User;
use App\Services\Myob\MyobApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMyobEmployeeInfoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;
    /**
     * Create a new job instance.
     */
    public function __construct($userId)
    {
        $this->user = User::find($userId);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $myobApiService = new MyobApiService;

        $data = $myobApiService->getEmployeeById()->call([
            'employeeId' => $this->user->employee->employee_number
        ]);
        $payDetails = optional($data)->payDetails;
        $payDistribution = optional($payDetails)->payDistribution;
        $payDistributionDetails = optional($payDistribution)->payDistributionDetails;

        if (is_array($payDistributionDetails) && count($payDistributionDetails) > 0) {
            foreach ($payDistributionDetails as $key => $payDistributionDetail) {
                $bankInfo = new BankInformation;
                $bankInfo->employee_id = $this->user->employee->id;
                $bankInfo->account_name = $payDistributionDetail->nameOnAccount;
                $bankInfo->bsb = $payDistributionDetail->bsbNumber;
                $bankInfo->account = $payDistributionDetail->accountNumber;
                $bankInfo->reimbursement = false;
                $bankInfo->primary = false;

                if ($payDistributionDetail->percent > 0) {
                    $bankInfo->pay_split = 'Percentage';
                    $bankInfo->pay_split_value = $payDistributionDetail->percent;
                } else {
                    $bankInfo->pay_split = 'Whole Number';
                    $bankInfo->pay_split_value = $payDistributionDetail->fixedAmount;
                }

                if ($key === 0) {
                    $bankInfo->primary = true;
                }

                $bankInfo->save();
            }
        }
    }
}
