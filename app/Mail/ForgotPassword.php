<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

use Illuminate\Queue\SerializesModels;


class ForgotPassword extends Mailable {

	use Queueable;
	use SerializesModels;

	public $user;
	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct( $user ) {
		$this->user = $user;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build() {
		$user     = $this->user;
		$HOST_URL = env( 'APP_URL' );
		return $this->view( 'mail.forgot_email' )
			->subject( 'Forgot email link' )
			->with( 'HOST_URL', $HOST_URL )
			->with( 'user', $user );
	}
}
