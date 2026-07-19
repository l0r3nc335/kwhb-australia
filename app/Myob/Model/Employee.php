<?php

namespace App\Myob\Model;

use App\Myob\Http\MyobAuthMiddleware as http;
use App\Myob\Http\MyobSettings;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class Employee
{

	private $uid;
	private $firstName;
	private $lastName;
	private $displayID;
	private $addresses;
	private $email;
	private $employmentDetails;
	protected $fillable = [
		'first_name', 'last_name', 'gender', 'pronouns', 'indigenous',
		'personal_phone', 'personal_email', 'work_phone', 'work_email', 'job_title',
		'status', 'address', 'abn', 'tax_number', 'dob',
		'date_hired', 'location_id', 'division_id', 'employment_id'
	];

	public function __construct(array $request)
	{
		$this->uid = $request['uid'] ?? null;
		$this->displayID = $request['DisplayID'] ?? null;
		$this->firstName = $request['firstName'] ?? null;
		$this->lastName = $request['lastName'] ?? null;
		$this->addresses = $request['addresses'] ?? null;
		$this->email = $request['email'] ?? null;
		$this->employmentDetails = $request['employmentDetails'] ?? null;
	}

	public function Create()
	{
		$employeeData = [
			'DisplayID' => $this->displayID,
			'firstName' => $this->firstName,
			'lastName' => $this->lastName,
			'email' => $this->email ?? null,
			"IsIndividual" => true,
			//Darby Street, Cooks Hill NSW, Australia, 2300
			// 'addresses' => [
			// 	[
			// 		'type' => 'Street',
			// 		'street' => '123 Main St',
			// 		'city' => 'Sydney',
			// 		'state' => 'NSW',
			// 		'postalCode' => '2000',
			// 		'country' => 'Australia',
			// 	],
			// ],
			'employmentDetails' => $this->employmentDetails
			// [
			// 	'startDate' => '2023-06-01',
			// 	'employmentBasis' => 'FullTime',
			// 	'classification' => 'Employee',
			// ],
		];

		$setting = new MyobSettings();
		$http = new http($setting);

		$http->refreshToken();

		$httpClient = $http->postHttpClient(json_encode($employeeData));
		$endpoint = $setting->postEndpointURI('Contact/Employee');
		$ret = $httpClient->post($endpoint, [
			'body' => json_encode($employeeData)
		]);

		return $ret;
	}

	public function Update($entity_id)
	{
		$data = new \AlexaCRM\Xrm\Entity('contact', $entity_id);

		$data['firstname'] = $this->firstName;

		return $data;
	}

	public function getData()
	{

		//return $data;
	}
	public function getAll()
	{
		try {

			$setting = new MyobSettings();
			$http = new http($setting);

			$http->refreshToken();

			$httpClient = $http->getHttpClient();

			$endpoint = $setting->getEndpointURI('Contact/Employee');
			$ret = $httpClient->get($endpoint);

			if ($ret->getStatusCode() == 200) {
				$data = json_decode($ret->getBody(), true);
				return response()->json($data);
			} else {
				return response()->json(['error' => 'Failed to fetch employees'], $ret->getStatusCode());
			}
		} catch (Exception $ex) {
			return response()->json(['error' => $ex->getMessage()], 500);
		}


		//return $data;
	}

	public function fetchEmployeeByDisplayID($displayID)
	{
		try {

			$setting = new MyobSettings();
			$http = new http($setting);

			$http->refreshToken();

			$httpClient = $http->getHttpClient();

			$endpoint = $setting->postEndpointURI('Contact/Employee?$filter=DisplayID eq \'' . $displayID . '\'');
			//return $endpoint;
			$ret = $httpClient->get($endpoint);
			if ($ret->getStatusCode() == 200) {
				$data = json_decode($ret->getBody(), true);
				foreach ($data['Items'] as $item) {
					$employeePaymentDetails = $item['EmployeePayrollDetails'];
					$ret2 = $httpClient->get($employeePaymentDetails['URI']);
					$data2 = json_decode($ret2->getBody(), true);
					return $data2;
				}
			} else {
				return response()->json(['error' => 'Failed to fetch employee'], $ret->getStatusCode());
			}
		} catch (RequestException $e) {
			if ($e->hasResponse()) {
				$responseBody = $e->getResponse()->getBody()->getContents();
				// Handle error response
				return ('Error fetching employee: ' . $responseBody);
			} else {
				// Handle other errors
				return ('Error fetching employee: ' . $e->getMessage());
			}
		}
	}
}
