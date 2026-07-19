<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Mail\ForgotPassword as ForgotPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ForgotPassword implements ShouldQueue {

	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	private $user;
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct( $user ) {
		$this->user = $user;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		Mail::to( $this->user->employee->work_email )->send( new ForgotPasswordMail( $this->user ) );
	}
}
