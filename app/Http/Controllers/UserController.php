<?php

namespace App\Http\Controllers;

use App\Jobs\ForgotPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Acme\Facades\AppResponse;
use Illuminate\Http\Request;

class UserController extends Controller {

	public function login( Request $request ) {

		$request->validate(
			array(
				'username' => 'required|string',
				'password' => 'required|string',
			)
		);

		$credentials = request( array( 'username', 'password' ) );
        try {
            if ( Auth::attempt( $credentials ) ) {
                $user = Auth::user()->load( array( 'employee.profile_image', 'roles') );
                if ( strtolower($user->employee->status) == 'active' ) {
                    $request->session()->regenerate();
                    return response()->json( $user, 204 );
                } else {
                    // return response()->json(['error' => 'Inactive user! Please contact the administrator.'], 403);
                    throw ValidationException::withMessages(
                        array(
                            'username' => 'Inactive user! Please contact the administrator.',
                        )
                    );
                }
            } else {
                // return response()->json(['error' => 'Invalid Username or Password.'], 404);
                throw ValidationException::withMessages(
                    array(
                        'username' => 'Invalid Username or Password.',
                    )
                );
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
	}

	public function logout( Request $request ) {
		try {
			Auth::guard( 'web' )->logout();
			$request->session()->invalidate();
			$request->session()->regenerateToken();
		} catch ( \Throwable $th ) {
			return AppResponse::badRequest( $th->getMessage() );
		}
	}

	public function create( Request $request ) {
		$data = $request->all();
		$user = User::create(
			array(
				'username' => $data['username'],
				'password' => bcrypt( $data['password'] ),
			// 'status'  => 'active',
			)
		);
		return response()->json( compact( 'user' ) );
	}

	public function me() {
		$user = Auth::user();
		$user->load(
			array(
				'user_role.role',
				'employee.profile_image',
				'employee.division',
				'employee.bank_info',
				'employee.address',
				'employee.location',
				'employee.emergency_contact',
				'employee.medical',
				'employee.cover_image',
				'employee.manager',
			)
		);
		return AppResponse::responseData( $user );
	}

	public function forgotPassword( Request $request ) {

		$user = User::getUserByUsername( $request->username );

		if ( ! empty( $user ) ) {

			$email_check = $user->employee->work_email ?? null;

			if ( $email_check ) {

				$user->remember_token = Str::random( 30 );
				$user->save();

				ForgotPassword::dispatch( $user );

				return true;
			}

			return false;

		} else {
			return false;
		}
	}

	public function resetPassword( Request $request ) {

		$user = User::getTokenSingle( $request->token );

		if ( ! empty( $user ) ) {

			$user->password       = bcrypt( $request->password );
			$user->remember_token = Str::random( 30 );
			$user->save();
			return true;

		} else {
			return false;
		}
	}
}
