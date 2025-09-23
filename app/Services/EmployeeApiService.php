<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EmployeeApiService
{
    protected $apiUrl = 'https://mdm.dbcorp.co.in/getEmployees?=';
    protected $authHeader = 'Basic TUFUUklYOnVvaT1rai1YZWxGa3JvcGVbUllCXXVu';

    /**
     * Fetch employee data from external API
     */
    public function getEmployeeData(string $alias): array
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => ['ALIAS' => $alias],
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $this->authHeader
                ],
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($error) {
                Log::error('Employee API cURL Error: ' . $error);
                return [
                    'success' => false,
                    'message' => 'Failed to connect to employee service',
                    'error' => $error
                ];
            }

            if ($httpCode !== 200) {
                Log::error('Employee API Error - HTTP Code: ' . $httpCode . ', Response: ' . $response);
                return [
                    'success' => false,
                    'message' => 'Employee service returned an error',
                    'http_code' => $httpCode,
                    'response' => $response
                ];
            }

            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Employee API JSON Decode Error: ' . json_last_error_msg());
                return [
                    'success' => false,
                    'message' => 'Invalid response format from employee service',
                    'response' => $response
                ];
            }

            Log::info('Employee API Response', ['alias' => $alias, 'response' => $data]);

            return [
                'success' => true,
                'data' => $data
            ];

        } catch (\Exception $e) {
            Log::error('Employee API Service Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while fetching employee data',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse employee data from API response
     */
    public function parseEmployeeData(array $apiData): array
    {
        try {
            // Check if the API response has the expected structure
            if (!isset($apiData['status']) || $apiData['status'] !== 'success') {
                return [
                    'success' => false,
                    'message' => 'API returned unsuccessful status'
                ];
            }

            if (!isset($apiData['EMPLOYEE']) || empty($apiData['EMPLOYEE'])) {
                return [
                    'success' => false,
                    'message' => 'No employee data found in API response'
                ];
            }

            $employeeData = $apiData['EMPLOYEE'];
            
            // Map API response fields to our database fields based on the actual response structure
            $parsedData = [
                'employee_id' => $employeeData['employee_id'] ?? $employeeData['username'] ?? '',
                'email' => $employeeData['email_address'] ?? '',
                'department' => $employeeData['department'] ?? '',
                'full_name' => $employeeData['full_name'] ?? '',
                'phone_number' => $employeeData['phone_number'] ?? '',
                'designation' => $employeeData['designation'] ?? $employeeData['job_profile'] ?? '',
            ];

            // Validate that we have at least the essential data
            if (empty($parsedData['employee_id']) || empty($parsedData['full_name'])) {
                return [
                    'success' => false,
                    'message' => 'Incomplete employee data received from API'
                ];
            }

            return [
                'success' => true,
                'data' => $parsedData
            ];

        } catch (\Exception $e) {
            Log::error('Employee Data Parsing Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to parse employee data',
                'error' => $e->getMessage()
            ];
        }
    }
}
