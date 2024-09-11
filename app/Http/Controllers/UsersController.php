<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\users;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = users::with('roles')->get();

        $users->transform(function ($user) {
            if ($user->img) {
                $user->img = base64_encode($user->img);
            }
            return $user;
        });

        return response()->json($users, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'nisn' => 'required|integer',
                'email' => 'required|email',
                'username' => 'required|string',
                'nama_lengkap' => 'required|string',
                'role_id' => 'nullable|integer',
                'asal_sekolah' => 'required|string',
            ]);

            $user = users::create([
                'nisn' => $validated['nisn'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'nama_lengkap' => $validated['nama_lengkap'] ?? 'tidak diketahui',
                'asal_sekolah' => $validated['asal_sekolah'] ?? 'tidak diketahui',
                'tanggal_bergabung' => $validated['tanggal_bergabung'] ?? now(),
                'role_id' => $validated['role_id'] ?? 1,
                'usertype' => 'user',
                'img' => null,
            ]);
            
            return response()->json(['message' => 'success', 'data' => $user], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Missing or invalid field', 'error' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nisn' => 'required|integer',
            'email' => 'required|email',
            'username' => 'required|string',
            'nama_lengkap' => 'required|string',
            'role_id' => 'nullable|integer',
            'tanggal_bergabung' => 'nullable|date',
            'asal_sekolah' => 'nullable|string',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'usertype' => 'nullable|string',

        ]);

        $image = $request->file('img');
        $image->storeAs('public/posts', $image->hashName());

        try {
            $user = users::create([
                'nisn' => $validated['nisn'],
                'username' => $validated['username'] ?? null,
                'email' => $validated['email'] ?? 'tidak diketahui',
                'nama_lengkap' => $validated['nama_lengkap'] ?? 'tidak diketahui',
                'asal_sekolah' => $validated['asal_sekolah'] ?? 'tidak diketahui',
                'tanggal_bergabung' => $validated['tanggal_bergabung'] ?? now(),
                'role_id' => $validated['role_id'] ?? 1,
                'usertype' => $validated['usertype'] ?? 'user',
                'img' => $image->hashName() ?? null,
            ]);
            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Tidak dapat input data', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $user = users::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        
        if ($user->img) {
            $user->img = base64_encode($user->img);
        }
    
        return response()->json(['data' => $user], 200);
    }    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nisn' => 'nullable|integer',
                'email' => 'nullable|email',
                'username' => 'nullable|string',
                'nama_lengkap' => 'nullable|string',
                'role_id' => 'nullable|integer',
                'asal_sekolah' => 'nullable|string',
                'usertype' => 'nullable|string',
                'tanggal_bergabung' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = users::find($id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $user->update([
                'nisn' => $request->nisn ?? $user->nisn,
                'email' => $request->email ?? $user->email,
                'username' => $request->username ?? $user->username,
                'nama_lengkap' => $request->nama_lengkap ?? $user->nama_lengkap,
                'role_id' => $request->role_id ?? $user->role_id,
                'asal_sekolah' => $request->asal_sekolah ?? $user->asal_sekolah,
                'usertype' => $request->usertype ?? $user->usertype,
                'tanggal_bergabung' => $request->tanggal_bergabung ?? $user->tanggal_bergabung,
                'img' => $request->img ?? $user->img,
            ]);

            return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = users::find($id);
        if (!$user) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Post deleted'], 200);
    }

    public function uploadphoto(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'img' => 'nullable|image',
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            $user = users::find($id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
    
            if ($request->hasFile('img')) {
                $image = $request->file('img');
                $imageData = file_get_contents($image->getRealPath());

                $user->img = $imageData;
            }
    
            $user->nisn = $request->nisn ?? $user->nisn;
            $user->email = $request->email ?? $user->email;
            $user->username = $request->username ?? $user->username;
            $user->nama_lengkap = $request->nama_lengkap ?? $user->nama_lengkap;
            $user->role_id = $request->role_id ?? $user->role_id;
            $user->asal_sekolah = $request->asal_sekolah ?? $user->asal_sekolah;
            $user->usertype = $request->usertype ?? $user->usertype;
            $user->tanggal_bergabung = $request->tanggal_bergabung ?? $user->tanggal_bergabung;
    
            $user->save();
    
            $user->img = base64_encode($user->img);
    
            return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }    
  
    public function updatephoto(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nisn' => 'nullable|integer',
            'email' => 'nullable|email',
            'username' => 'nullable|string',
            'nama_lengkap' => 'nullable|string',
            'role_id' => 'nullable|integer',
            'asal_sekolah' => 'nullable|string',
            'usertype' => 'nullable|string',
            'tanggal_bergabung' => 'nullable|date',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi file gambar
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
 
        $user = users::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        
        if ($request->hasFile('img')) {
            $imageData = file_get_contents($request->file('img')->getRealPath());
            $user->img = $imageData; 
        }

        $user->nisn = $request->nisn ?? $user->nisn;
        $user->email = $request->email ?? $user->email;
        $user->username = $request->username ?? $user->username;
        $user->nama_lengkap = $request->nama_lengkap ?? $user->nama_lengkap;
        $user->role_id = $request->role_id ?? $user->role_id;
        $user->asal_sekolah = $request->asal_sekolah ?? $user->asal_sekolah;
        $user->usertype = $request->usertype ?? $user->usertype;
        $user->tanggal_bergabung = $request->tanggal_bergabung ?? $user->tanggal_bergabung;

        $user->save();

        $user->img = base64_encode($user->img);

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }
 
    public function showImage($id)
    {
        $user = users::find($id);
        if (!$user || !$user->img) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        $mimeType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $user->img);

        return response($user->img)
            ->header('Content-Type', $mimeType);
    }
}
