<?php
namespace App\Services\Myob;

use GuzzleHttp\Client;

class MyobApiService
{
    // Env Variables
    protected string $apiUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $scope;
    protected string $grantType;
    protected string $username;
    protected string $password;
    // Computed variables
    protected string $token;
    protected string $endpoint;
    protected string $method;
    // Guzzle Http
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'verify' => false
        ]);

        $this->apiUrl = config('myob.api_url');
        $this->clientId = config('myob.client_id');
        $this->clientSecret = config('myob.client_secret');
        $this->scope = config('myob.scope');
        $this->grantType = config('myob.grant_type');
        $this->username = config('myob.username');
        $this->password = config('myob.password');


        $this->requestToken();
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    private function requestToken(): void
    {
        $form_params = [
            'grant_type' => $this->grantType,
            'scope' => $this->scope,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username' => $this->username,
            'password' => $this->password
        ];
        // print_r($form_params);

        $response = $this->client->post('https://kwhb.myobadvanced.com/identity/connect/token', [
            'form_params' => $form_params
        ]);

        $response = json_decode($response->getBody());
        $this->token = optional($response)->access_token;
    }

    public function getEmployees(): self
    {
        $this->endpoint = $this->apiUrl . '/Employee/GetEmployees';
        $this->method = 'get';

        return $this;
    }

    public function getEmployeeById(): self
    {
        $this->endpoint = $this->apiUrl . '/Employee/GetEmployeeByEmployeeID';
        $this->method = 'get';

        return $this;
    }

    public function getEmployeePayrollDetails($id): self
    {
        $this->endpoint = $this->apiUrl . '/Employee/GetEmployeePayrollDetails?employeeId=' . $id;
        $this->method = 'get';

        return $this;
    }

    public function createOrUpdateEmployee(): self
    {
        $this->endpoint = $this->apiUrl . '/Employee/UpsertEmployee';
        $this->method = 'post';

        return $this;
    }

    public function getTimesheet($batchNumber): self
    {
        $this->endpoint = $this->apiUrl . '/Timesheet/GetTimesheet?batchNumber=' . $batchNumber;
        $this->method = 'get';

        return $this;
    }

    public function submitTimesheet(): self
    {
        $this->endpoint = $this->apiUrl . '/Timesheet/SubmitTimesheet';
        $this->method = 'post';

        return $this;
    }

    public function call(array $params = [])
    {
        $request = [
            'headers' => $this->headers()
        ];

        switch ($this->method) {
            case 'post':
                $request['body'] = json_encode($params);
                break;
            case 'get':
                $request['query'] = $params;
                break;
        }

        return json_decode($this->client->{$this->method}($this->endpoint, $request)->getBody());
    }

    // Expense

    public function getCostCodes(): self
    {
        $this->endpoint = $this->apiUrl . '/Common/GetCostCodes';
        $this->method = 'get';

        return $this;
    }    
}
