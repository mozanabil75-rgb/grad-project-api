<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $role = isset($data['role'])
            ? UserRole::from($data['role'])
            : UserRole::Student;

        $user = User::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'email' => $data['email'],
            'national_id' => $data['national_id'],
            'password' => Hash::make($data['password']),
            'role' => $role,
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Registration successful.', 201);
    }

    public function registerStudent(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'national_id' => 'required|string|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::query()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'national_id' => $data['national_id'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::Student,
            ]);

            $token = $user->createToken('api')->plainTextToken;

            return ApiResponse::success([
                'user' => new UserResource($user),
                'token' => $token,
            ], 'Student registration successful.', 201);

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Registration failed: ' . $e->getMessage(),
                500
            );
        }
    }

    public function registerProfessor(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'national_id' => 'required|string|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::query()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'national_id' => $data['national_id'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::Professor,
            ]);

            $token = $user->createToken('api')->plainTextToken;

            return ApiResponse::success([
                'user' => new UserResource($user),
                'token' => $token,
            ], 'Professor registration successful.', 201);

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Registration failed: ' . $e->getMessage(),
                500
            );
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();

            $user = User::query()->where('email', $credentials['email'])->first();

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                return ApiResponse::error(
                    'Invalid credentials. Please check your email and password.',
                    401
                );
            }

            $token = $user->createToken('api')->plainTextToken;

            return ApiResponse::success([
                'user' => new UserResource($user),
                'token' => $token,
            ], 'Login successful.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error(
                'Validation failed: ' . implode(' ', $e->errors()),
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Login failed: ' . $e->getMessage(),
                500
            );
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    public function user(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new UserResource($request->user()),
            'User retrieved successfully.'
        );
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Delete any existing OTP for this email
            PasswordOtp::where('email', $data['email'])->delete();
            
            // Create new OTP record
            PasswordOtp::create([
                'email' => $data['email'],
                'otp' => $otp,
                'expires_at' => now()->addMinutes(10),
            ]);

            return ApiResponse::success([
                'otp' => $otp, // Include OTP for testing (remove in production)
                'message' => 'OTP sent successfully. Valid for 10 minutes.'
            ], 'OTP generated successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to generate OTP: ' . $e->getMessage(),
                500
            );
        }
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
                'otp' => 'required|string|size:6',
            ]);

            $passwordOtp = PasswordOtp::where('email', $data['email'])
                ->where('otp', $data['otp'])
                ->where('expires_at', '>', now())
                ->first();

            if (! $passwordOtp) {
                return ApiResponse::error(
                    'Invalid or expired OTP.',
                    422
                );
            }

            return ApiResponse::success([
                'message' => 'OTP verified successfully. You can now reset your password.'
            ], 'OTP verified successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(
                'OTP verification failed: ' . $e->getMessage(),
                500
            );
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Find user and update password
            $user = User::where('email', $data['email'])->first();
            $user->password = Hash::make($data['password']);
            $user->save();

            // Clean up any existing OTP records for this email
            PasswordOtp::where('email', $data['email'])->delete();

            return ApiResponse::success([
                'message' => 'Password reset successfully.'
            ], 'Password reset successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Password reset failed: ' . $e->getMessage(),
                500
            );
        }
    }
}
