<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResidenceRequest;
use App\Http\Requests\UpdateResidenceRequest;
use App\Models\Residence;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResidenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Residence::where('provider_id', auth()->id())
            ->with(['category'])
            ->withCount(['bookings', 'ratings'])
            ->withAvg('ratings', 'rating');

        // Filters
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $residences = $query->orderBy('created_at', 'desc')->paginate(10);
        $categories = Category::where('type', 'residence')->get();

        return view('provider.residences.index', compact('residences', 'categories'));
    }

    public function show(Residence $residence)
    {
        $this->authorize('view', $residence);

        $residence->load(['category', 'bookings.user', 'ratings.user']);

        return view('provider.residences.show', compact('residence'));
    }

    public function create()
    {
        $categories = Category::where('type', 'residence')->get();

        return view('provider.residences.create', compact('categories'));
    }

    public function store(StoreResidenceRequest $request)
    {
        try {
            $data = $request->validated();
            $data['provider_id'] = auth()->id();

            // Ensure price is set from price_per_month (validated key)
            $data['price'] = $request->input('price', $request->input('price_per_month'));

            // Handle image uploads
            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('residences', 'public');
                    $images[] = $path;
                }
                $data['images'] = $images; // rely on $casts to store as JSON
            }

            // Handle facilities
            if (isset($data['facilities'])) {
                $data['facilities'] = array_values($data['facilities']);
            }

            // Set available_slots to capacity initially
            $data['available_slots'] = $data['capacity'];

            $residence = Residence::create($data);

            return redirect()->route('provider.residences.show', $residence)
                ->with('success', 'Residence berhasil ditambahkan');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan residence: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(Residence $residence)
    {
        $this->authorize('update', $residence);

        $categories = Category::where('type', 'residence')->get();

        return view('provider.residences.edit', compact('residence', 'categories'));
    }

    public function update(UpdateResidenceRequest $request, Residence $residence)
    {
        $this->authorize('update', $residence);

        try {
            $data = $request->validated();

            // Ensure price is set from price_per_month (validated key)
            $data['price'] = $request->input('price', $request->input('price_per_month'));

            // Handle image uploads
            if ($request->hasFile('images')) {
                // Delete old images
                $oldImages = $residence->images ?? [];
                foreach ($oldImages as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }

                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('residences', 'public');
                    $images[] = $path;
                }
                $data['images'] = $images; // rely on $casts to store as JSON
            }

            // Handle facilities
            if (isset($data['facilities'])) {
                $data['facilities'] = array_values($data['facilities']);
            }

            $residence->update($data);

            return redirect()->route('provider.residences.show', $residence)
                ->with('success', 'Residence berhasil diupdate');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate residence: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Residence $residence)
    {
        $this->authorize('delete', $residence);

        try {
            // Check if there are active bookings
            $activeBookings = $residence->bookings()
                ->whereIn('status', ['pending', 'approved'])
                ->count();

            if ($activeBookings > 0) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus residence dengan booking aktif');
            }

            // Delete images
            $images = json_decode($residence->images, true) ?? [];
            foreach ($images as $image) {
                Storage::disk('public')->delete($image);
            }

            $residence->delete();

            return redirect()->route('provider.residences.index')
                ->with('success', 'Residence berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus residence: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Residence $residence)
    {
        $this->authorize('update', $residence);

        $residence->update([
            'is_active' => !$residence->is_active
        ]);

        $status = $residence->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Residence berhasil {$status}");
    }
}

