<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activities;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

use Illuminate\Validation\ValidationException;

class ActivitiesController extends Controller
{
    public function index(Request $request)
{
    $query = Activities::query();

    if ($request->has('users_id')) {
        $query->where('users_id', $request->users_id);
    }

    $aktivitas = $query->with(['user'])->get();

    // Mengonversi BLOB menjadi base64
    foreach ($aktivitas as $item) {
        if ($item->user && $item->user->img) {
            $item->user->img = base64_encode($item->user->img);
        }
    }

    return response()->json($aktivitas, 200);
}


    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama_aktivitas' => 'required|string',
                'uraian' => 'required|string',
                'tanggal' => 'required|date',
                'users_id' => 'required|integer',
            ]);

            $aktivitas = Activities::create([
                'nama_aktivitas' => $validatedData['nama_aktivitas'],
                'uraian' => $validatedData['uraian'],
                'tanggal' => $validatedData['tanggal'] ?? now(),
                'users_id' => $validatedData['users_id'], 
            ]);

            return response()->json(['message' => 'success create new activities', 'data' => $aktivitas], Response::HTTP_CREATED);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Missing or invalid field', 'errors' => $e->errors()], 422);
        }
    }

    public function show(int $id)
    {
        $aktivitas = Activities::with('user')->find($id);
            
        if (!$aktivitas) {
            return response()->json(['message' => 'activities not found'], 404);
        }
        
        return response()->json($aktivitas);
    }

    public function update(Request $request, int $id)
    {
        $aktivitas = Activities::find($id);

        if (!$aktivitas) {
            return response()->json(['message' => 'activities not found'], Response::HTTP_NOT_FOUND);
        }
    
        $validatedData = $request->validate([
            'nama_aktivitas' => 'nullable|string|max:255',
            'uraian' => 'nullable|string|max:255',
            'tanggal' => 'nullable|date',  
        ]);
    
        $validatedData['tanggal'] = $validatedData['tanggal'] ?? Carbon::today()->toDateString();
    
        $aktivitas->update($validatedData);
    
        return response()->json(['message' => 'update activities success', 'data' => $aktivitas], Response::HTTP_OK);
    }

    public function updateStatus(Request $request, int $id)
    {
        $aktivitas = Activities::find($id);

        if (!$aktivitas) {
            return response()->json(['message' => 'activities not found'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'status' => 'required|string|in:PROSES,SELESAI',
        ]);

        $aktivitas->status = $request->status;
        $aktivitas->save();

        return response()->json(['message' => 'Status updated successfully', 'data' => $aktivitas], Response::HTTP_OK);
    }

    public function destroy(int $id)
    {
        $aktivitas = Activities::find($id);

        if (!$aktivitas) {
            return response()->json(['message' => 'activities not found'], Response::HTTP_NOT_FOUND);
        }

        $aktivitas->delete();
        return response()->json(['message' => 'activities deleted successfully']);
    }
}
