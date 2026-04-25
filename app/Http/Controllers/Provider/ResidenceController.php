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

        return view('provider_residence.residences.index', compact('residences', 'categories'));
    }

    public function show(Residence $residence)
    {
        $this->authorize('view', $residence);

        $residence->load(['category', 'bookings.user', 'ratings.user']);

        return view('provider_residence.residences.show', compact('residence'));
    }

    public function create()
    {
        $categories = Category::where('type', 'residence')->get();

        return view('provider_residence.residences.create', compact('categories'));
    }

    public function store(StoreResidenceRequest $request)
    {
        try {
            $data = $request->validated();

            // Peta price_per_month → kolom price di database
            $data['price'] = (float) $request->input('price_per_month');
            unset($data['price_per_month']);
            $data['provider_id'] = auth()->id();

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
            $facilities = isset($data['facilities']) ? array_values($data['facilities']) : [];
            if ($request->filled('custom_facilities')) {
                $custom = array_filter(array_map('trim', explode(',', $request->input('custom_facilities'))));
                $facilities = array_values(array_unique(array_merge($facilities, $custom)));
            }
            $data['facilities'] = $facilities;

            // Set available_slots to capacity initially
            $data['available_slots'] = $data['capacity'];

            $residence = Residence::create($data);

            return redirect()->route('provider.residence.residences.show', $residence)
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

        return view('provider_residence.residences.edit', compact('residence', 'categories'));
    }

    public function update(UpdateResidenceRequest $request, Residence $residence)
    {
        $this->authorize('update', $residence);

        try {
            $data = $request->validated();

            // Peta price_per_month → kolom price di database
            $data['price'] = (float) $request->input('price_per_month');
            unset($data['price_per_month']); // buang key yang tidak ada di fillable

            // ── Kelola gambar ──────────────────────────────────────────
            // Ambil gambar yang sudah ada (sudah array karena model cast)
            $existingImages = is_array($residence->images) ? $residence->images : [];

            // Hapus gambar yang ditandai dihapus
            $removedRaw = $request->input('removed_images', '[]');
            $removedIndexes = is_array($removedRaw)
                ? $removedRaw
                : (json_decode($removedRaw, true) ?? []);

            foreach ($removedIndexes as $idx) {
                if (isset($existingImages[$idx])) {
                    Storage::disk('public')->delete($existingImages[$idx]);
                    unset($existingImages[$idx]);
                }
            }
            $existingImages = array_values($existingImages);

            // Tambahkan gambar baru yang diupload
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('residences', 'public');
                        $existingImages[] = $path;
                    }
                }
            }
            $data['images'] = $existingImages;

            // ── Kelola fasilitas ───────────────────────────────────────
            $facilities = isset($data['facilities']) ? array_values($data['facilities']) : [];
            if ($request->filled('custom_facilities')) {
                $custom = array_filter(array_map('trim', explode(',', $request->input('custom_facilities'))));
                $facilities = array_values(array_unique(array_merge($facilities, $custom)));
            }
            $data['facilities'] = $facilities;

            $residence->update($data);

            return redirect()->route('provider.residence.residences.show', $residence)
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

            // Delete images (sudah array karena model cast, tidak perlu json_decode)
            $images = $residence->images ?? [];
            foreach ($images as $image) {
                Storage::disk('public')->delete($image);
            }

            $residence->delete();

            return redirect()->route('provider.residence.residences.index')
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
