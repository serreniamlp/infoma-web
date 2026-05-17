<?php
// app/Http/Controllers/Api/Provider/ResidenceController.php
// TULIS ULANG — hapus semua kode lama, ganti dengan ini

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResidenceResource;
use App\Models\Residence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResidenceController extends Controller
{
    private function checkApproved()
    {
        if (Auth::user()->provider_status !== 'approved') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun kamu belum diverifikasi admin.',
            ], 403);
        }
        return null;
    }

    public function index()
    {
        $residences = Residence::with(['category'])
            ->where('provider_id', Auth::id())
            ->withCount('bookings')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return ResidenceResource::collection($residences);
    }

    public function show(Residence $residence)
    {
        if ($residence->provider_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $residence->load(['category']);

        return response()->json([
            'status' => 'success',
            'data'   => new ResidenceResource($residence),
        ]);
    }

    public function store(Request $request)
    {
        if ($err = $this->checkApproved()) return $err;

        $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'required|string',
            'category_id'     => 'required|exists:categories,id',
            'rental_period'   => 'required|string|max:100',
            'address'         => 'required|string|max:500',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'price_per_month' => 'required|numeric|min:0',
            'capacity'        => 'required|integer|min:1',
            'discount_type'   => 'nullable|in:percentage,fixed',
            'discount_value'  => 'nullable|numeric|min:0',
            'facilities'      => 'nullable|array',
            'images'          => 'nullable|array|max:10',
            'images.*'        => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data                    = $request->except(['images', 'price_per_month']);
        $data['price']           = $request->price_per_month;
        $data['provider_id']     = Auth::id();
        $data['available_slots'] = $request->capacity;
        $data['is_active']       = true;

        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $img) {
                $images[] = $img->store('residences', 'public');
            }
            $data['images'] = $images;
        }

        $residence = Residence::create($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Hunian berhasil ditambahkan.',
            'data'    => new ResidenceResource($residence->load('category')),
        ], 201);
    }

    public function update(Request $request, Residence $residence)
    {
        if ($residence->provider_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name'            => 'sometimes|string|max:255',
            'description'     => 'sometimes|string',
            'price_per_month' => 'sometimes|numeric|min:0',
            'capacity'        => 'sometimes|integer|min:1',
            'facilities'      => 'nullable|array',
            'is_active'       => 'sometimes|boolean',
        ]);

        $data = $request->except(['images', '_method', 'price_per_month']);
        if ($request->filled('price_per_month')) {
            $data['price'] = $request->price_per_month;
        }

        $residence->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Hunian berhasil diupdate.',
            'data'    => new ResidenceResource($residence->load('category')),
        ]);
    }

    public function destroy(Residence $residence)
    {
        if ($residence->provider_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $residence->delete();

        return response()->json(['status' => 'success', 'message' => 'Hunian berhasil dihapus.']);
    }

    public function toggleStatus(Residence $residence)
    {
        if ($residence->provider_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $residence->update(['is_active' => !$residence->is_active]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status hunian berhasil diubah.',
            'data'    => ['is_active' => $residence->is_active],
        ]);
    }
}