<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Create a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.verify');
    }

    /**
     * Display a listing of the users.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Admin and Manager can view all users
        // Client can only view their own profile (handled in me() of AuthController)
        
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Only Admin can create new users with any role
        // Manager can create only client users
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:client,manager,admin',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Only admin can create manager or admin users
        $user = auth('api')->user();
        if ($user->role !== 'admin' && $request->role !== 'client') {
            return response()->json([
                'message' => 'You are not authorized to create users with this role.'
            ], 403);
        }

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'User successfully created',
            'user' => $newUser
        ], 201);
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $authUser = auth('api')->user();
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Client can only view their own profile
        if ($authUser->role === 'client' && $authUser->id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to view this user.'
            ], 403);
        }

        return response()->json($user);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $authUser = auth('api')->user();
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Client can only update their own profile
        if ($authUser->role === 'client' && $authUser->id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to update this user.'
            ], 403);
        }

        // Only admin can update roles
        if (isset($request->role) && $authUser->role !== 'admin') {
            return response()->json([
                'message' => 'You are not authorized to update user roles.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'role' => 'sometimes|string|in:client,manager,admin',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if (isset($request->name)) {
            $user->name = $request->name;
        }

        if (isset($request->email)) {
            $user->email = $request->email;
        }

        if (isset($request->password)) {
            $user->password = Hash::make($request->password);
        }

        if (isset($request->role) && $authUser->role === 'admin') {
            $user->role = $request->role;
        }

        $user->save();

        return response()->json([
            'message' => 'User successfully updated',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $authUser = auth('api')->user();
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Client cannot delete any users
        if ($authUser->role === 'client') {
            return response()->json([
                'message' => 'You are not authorized to delete users.'
            ], 403);
        }

        // Manager can only delete clients
        if ($authUser->role === 'manager' && $user->role !== 'client') {
            return response()->json([
                'message' => 'You are not authorized to delete this user.'
            ], 403);
        }

        // Admin cannot be deleted (for safety)
        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'Admin users cannot be deleted for security reasons.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User successfully deleted'
        ]);
    }
}