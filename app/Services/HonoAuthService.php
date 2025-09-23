<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HonoAuthService
{
    /**
     * Authenticate with Hono API and return user data
     */
    public function authenticateWithHono($username, $password): array
    {
        $postRequest = [
            'uname' => base64_encode($username),
            'pass'  => base64_encode($password)
        ];

        try {
            $response = Http::withHeaders(['Authorization' => env("HONO_HR_AUTH_TOKEN")])
                ->timeout(30) // Add timeout
                ->asForm()
                ->post(config('project.HONO_HR_AUTH_URL'), $postRequest);

            // Check for HTTP errors first
            if ($response->failed()) {
                $statusCode = $response->status();
                Log::warning('Hono authentication HTTP error', [
                    'username' => $username,
                    'status_code' => $statusCode,
                    'response_body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'user_data' => null,
                    'raw_response' => null,
                    'error' => $this->getHttpErrorMessage($statusCode)
                ];
            }

            $responseData = $response->json();
            $status = isset($responseData['status']) && $responseData['status'] == 1 ? true : false;

            if ($status) {
                Log::info('Hono authentication successful', [
                    'username' => $username,
                    'response' => $responseData
                ]);

                return [
                    'success' => true,
                    'user_data' => $this->extractUserDataFromResponse($username, $responseData),
                    'raw_response' => $responseData
                ];
            } else {
                Log::warning('Hono authentication failed', [
                    'username' => $username,
                    'response' => $responseData
                ]);

                return [
                    'success' => false,
                    'user_data' => null,
                    'raw_response' => $responseData,
                    'error' => $this->getAuthenticationErrorMessage($responseData)
                ];
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Hono authentication connection error', [
                'username' => $username,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'user_data' => null,
                'raw_response' => null,
                'error' => 'Unable to connect to authentication service. Please check your internet connection and try again.'
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Hono authentication request error', [
                'username' => $username,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'user_data' => null,
                'raw_response' => null,
                'error' => 'Authentication service request failed. Please try again later.'
            ];
        } catch (\Exception $e) {
            Log::error('Hono authentication exception', [
                'username' => $username,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'user_data' => null,
                'raw_response' => null,
                'error' => 'An unexpected error occurred during authentication. Please try again later.'
            ];
        }
    }

    /**
     * Get HTTP error message based on status code
     */
    protected function getHttpErrorMessage(int $statusCode): string
    {
        switch ($statusCode) {
            case 401:
                return 'Authentication service credentials are invalid. Please contact your administrator.';
            case 403:
                return 'Access to authentication service is forbidden. Please contact your administrator.';
            case 404:
                return 'Authentication service endpoint not found. Please contact your administrator.';
            case 500:
                return 'Authentication service is experiencing technical difficulties. Please try again later.';
            case 502:
            case 503:
            case 504:
                return 'Authentication service is temporarily unavailable. Please try again later.';
            default:
                return 'Authentication service returned an error. Please try again later.';
        }
    }

    /**
     * Get authentication error message from response
     */
    protected function getAuthenticationErrorMessage(array $responseData): string
    {
        // Check for specific error messages in the response
        if (isset($responseData['message'])) {
            return $responseData['message'];
        }

        if (isset($responseData['error'])) {
            return $responseData['error'];
        }

        if (isset($responseData['msg'])) {
            return $responseData['msg'];
        }

        // Default message for authentication failure
        return 'Invalid credentials provided. Please check your username and password.';
    }

    /**
     * Extract user data from Hono API response
     */
    protected function extractUserDataFromResponse($username, $responseData): array
    {
        // Default values
        $userData = [
            'username' => $username,
            'email' => $username . '@hono.local',
            'first_name' => 'Hono',
            'last_name' => 'User',
            'middle_name' => null,
            'mobile_number' => null,
            'address' => null,
            'gender' => null,
            'date_of_birth' => null,
            'avatar' => null,
            'status' => 1,
            'source' => 'hono'
        ];

        // Try to extract data from response
        if (isset($responseData['data']) && is_array($responseData['data'])) {
            $honoData = $responseData['data'];
            
            // Map Hono API fields to our user fields
            $userData['username'] = $honoData['username'] ?? $username;
            $userData['email'] = $honoData['email'] ?? $honoData['username'] . '@hono.local';
            $userData['first_name'] = $honoData['first_name'] ?? $honoData['fname'] ?? $honoData['firstname'] ?? 'Hono';
            $userData['last_name'] = $honoData['last_name'] ?? $honoData['lname'] ?? $honoData['lastname'] ?? 'User';
            $userData['middle_name'] = $honoData['middle_name'] ?? $honoData['mname'] ?? $honoData['middlename'] ?? null;
            $userData['mobile_number'] = $honoData['mobile_number'] ?? $honoData['mobile'] ?? $honoData['phone'] ?? null;
            $userData['address'] = $honoData['address'] ?? $honoData['location'] ?? null;
            $userData['gender'] = $honoData['gender'] ?? $honoData['sex'] ?? null;
            $userData['date_of_birth'] = $honoData['date_of_birth'] ?? $honoData['dob'] ?? $honoData['birth_date'] ?? null;
            $userData['avatar'] = $honoData['avatar'] ?? $honoData['profile_picture'] ?? $honoData['image'] ?? null;
            $userData['status'] = $honoData['status'] ?? $honoData['active'] ?? 1;
        }

        // If no structured data, try to parse from username
        if ($userData['first_name'] === 'Hono' && $userData['last_name'] === 'User') {
            $nameParts = explode(' ', $username, 2);
            if (count($nameParts) > 1) {
                $userData['first_name'] = $nameParts[0];
                $userData['last_name'] = $nameParts[1];
            } else {
                $userData['first_name'] = $username;
                $userData['last_name'] = 'User';
            }
        }

        Log::info('Extracted user data from Hono response', [
            'username' => $username,
            'extracted_data' => $userData
        ]);

        return $userData;
    }

    /**
     * Legacy method for backward compatibility
     */
    public function getUserDetailsFromHonoResponse($username, $response = null): array
    {
        return $this->extractUserDataFromResponse($username, $response ?? []);
    }
}