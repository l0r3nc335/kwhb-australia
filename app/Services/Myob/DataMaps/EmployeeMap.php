<?php

namespace App\Services\Myob\DataMaps;

class EmployeeMap extends DataMapper
{
    public function fields(): array
    {
        return [
            'employee_guid' => 'id',
            'employee_number' => 'employeeID',
            'first_name' => 'firstName',
            'last_name' => 'lastName',
            'gender' => 'gender',
            'personal_phone' => 'phone1',
            'personal_email' => 'email',
            'status' => 'status'
        ];
    }

    public function computedFields(): array
    {
        return [
            'job_title' => [
                'key' => 'employmentHistory.position',
                'computation' => 'getNestedArrayField'
            ],
            'address' => [
                'key' => 'addressLine1.addressLine2.city.country.postalCode',
                'computation' => 'concatFields'
            ],
            'dob' => [
                'key' => 'dateOfBirth',
                'computation' => 'parseDateOnly'
            ]
        ];
    }
}
