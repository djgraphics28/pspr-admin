<?php

namespace App\Http\Controllers\API;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Register a new student.
     *
     * This endpoint allows a new student to register by providing their details.
     *
     * @group Student Authentication
     *
     * @bodyParam first_name string required The first name of the student. Example: John
     * @bodyParam middle_name string The middle name of the student. Example: A
     * @bodyParam last_name string required The last name of the student. Example: Doe
     * @bodyParam email string required The email address of the student. Must be unique. Example: john.doe@example.com
     * @bodyParam password string required The password for the student account (minimum 8 characters). Example: secret123
     * @bodyParam password_confirmation string required Password confirmation (must match the password). Example: secret123
     *
     * @response 201 {
     *    "message": "Student registered successfully",
     *    "access_token": "2|a1s2d3f4g5h6j7k8l9m0",
     *    "token_type": "Bearer"
     * }
     *
     * @response 422 {
     *    "errors": {
     *        "email": ["The email has already been taken."]
     *    }
     * }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $student = Student::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $student->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Student registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Student login.
     *
     * This endpoint allows a student to log in by providing their email and password.
     *
     * @group Student Authentication
     *
     * @bodyParam email string required The email address of the student. Example: john.doe@example.com
     * @bodyParam password string required The password of the student. Example: secret123
     *
     * @response 200 {
     *    "access_token": "2|a1s2d3f4g5h6j7k8l9m0",
     *    "token_type": "Bearer"
     * }
     *
     * @response 401 {
     *    "message": "Invalid login details"
     * }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $student = Student::where('email', $request->email)->first();

        if (!$student || !Hash::check($request->password, $student->password)) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $token = $student->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
