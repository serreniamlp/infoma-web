<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\CheckProfileComplete;
use App\Http\Requests\UpdateResidenceRequest;
use App\Models\Residence;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResidenceController extends Controller
{
    use CheckProfileComplete;
    public function index(Request $request)
    {
        $query = Residence::where('provider_id', auth()->id())
            ->with(['category'])
            ->withCount(['bookings', 'ratings'])
            ->withAvg('ratings', 'rating');

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('residence_type')) {
            $query->where('residence_type', $request->residence_type);
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

    private function checkProviderApproved()
    {
        $user = auth()->user();
        if ($user->provider_status !== 'approved') {
            return redirect()->route('provider.residence.dashboard')
                ->with('warning', 'Akun kamu belum diverifikasi admin. Kamu belum bisa membuat listing.');
        }
        return null;
    }

    public function create()
    {
        if ($redirect = $this->checkProviderApproved()) return $redirect;
        if ($redirect = $this->checkProfileComplete(route('provider.residence.residences.create'))) return $redirect;
        $categories = Category::where('type', 'residence')->get();
        return view('provider_residence.residences.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->checkProviderApproved()) return $redirect;
        if ($redirect = $this->checkProfileComplete()) return $redirect;

        try {
            // ── Validasi umum (semua tipe) ────────────────────────────
            $rules = [
                'name'              => 'required|string|max:255',
                'description'       => 'required|string',
                'category_id'       => 'required|exists:categories,id',
                'residence_type'    => 'required|in:kos,kontrakan,apartemen,rumah_sewa',
                'rental_period'     => 'required|in:monthly,yearly',
                'address'           => 'required|string|max:500',
                'latitude'          => 'nullable|numeric|between:-90,90',
                'longitude'         => 'nullable|numeric|between:-180,180',
                'price_per_month'   => 'required|numeric|min:0',
                'capacity'          => 'required|integer|min:1',
                'furnish_status'    => 'nullable|in:unfurnished,semi_furnished,full_furnished',
                'discount_type'     => 'nullable|in:percentage,flat',
                'discount_value'    => 'nullable|numeric|min:0',
                'facilities'        => 'nullable|array',
                'facilities.*'      => 'string|max:100',
                'custom_facilities' => 'nullable|string|max:500',
                'images'            => 'nullable|array|max:10',
                'images.*'          => 'image|mimes:jpg,jpeg,png,webp|max:2048',
                'is_active'         => 'nullable|boolean',
            ];

            // ── Validasi tambahan per tipe ────────────────────────────
            $residenceType = $request->input('residence_type');

            if ($residenceType === 'kos') {
                $rules['kos_type']   = 'required|in:putra,putri,campur';
                $rules['room_size']  = 'nullable|numeric|min:1|max:999';
            }

            if (in_array($residenceType, ['kontrakan', 'rumah_sewa'])) {
                $rules['bedroom_count']  = 'required|integer|min:1|max:20';
                $rules['bathroom_count'] = 'required|integer|min:1|max:20';
                $rules['building_size']  = 'nullable|numeric|min:1';
                $rules['land_size']      = 'nullable|numeric|min:1';
            }

            if ($residenceType === 'apartemen') {
                $rules['unit_type']      = 'required|in:studio,1BR,2BR,3BR,4BR';
                $rules['floor_number']   = 'required|integer|min:1|max:200';
                $rules['tower_name']     = 'nullable|string|max:100';
                $rules['bedroom_count']  = 'nullable|integer|min:0|max:10';
                $rules['bathroom_count'] = 'nullable|integer|min:1|max:10';
                $rules['room_size']      = 'nullable|numeric|min:1|max:999';
            }

            $messages = [
                'name.required'           => 'Nama hunian wajib diisi.',
                'description.required'    => 'Deskripsi wajib diisi.',
                'category_id.required'    => 'Kategori wajib dipilih.',
                'residence_type.required' => 'Tipe hunian wajib dipilih.',
                'rental_period.required'  => 'Periode sewa wajib dipilih.',
                'address.required'        => 'Alamat wajib diisi.',
                'price_per_month.required'=> 'Harga sewa wajib diisi.',
                'capacity.required'       => 'Jumlah kamar/unit tersedia wajib diisi.',
                'kos_type.required'       => 'Jenis kos (putra/putri/campur) wajib dipilih.',
                'bedroom_count.required'  => 'Jumlah kamar tidur wajib diisi.',
                'bathroom_count.required' => 'Jumlah kamar mandi wajib diisi.',
                'unit_type.required'      => 'Tipe unit apartemen wajib dipilih.',
                'floor_number.required'   => 'Nomor lantai wajib diisi.',
                'images.*.image'          => 'File harus berupa gambar.',
                'images.*.max'            => 'Ukuran gambar maksimal 2MB.',
            ];

            $request->validate($rules, $messages);

            // ── Bangun data yang akan disimpan ────────────────────────
            $data = [
                'provider_id'    => auth()->id(),
                'category_id'    => $request->category_id,
                'name'           => $request->name,
                'description'    => $request->description,
                'address'        => $request->address,
                'latitude'       => $request->latitude,
                'longitude'      => $request->longitude,
                'residence_type' => $residenceType,
                'rental_period'  => $request->rental_period,
                'price'          => (float) $request->price_per_month,
                'capacity'       => (int) $request->capacity,
                'available_slots'=> (int) $request->capacity,
                'furnish_status' => $request->furnish_status,
                'is_active'      => $request->boolean('is_active', true),
                'discount_type'  => $request->discount_type ?: null,
                'discount_value' => $request->discount_type ? $request->discount_value : null,
            ];

            // Field spesifik per tipe
            if ($residenceType === 'kos') {
                $data['kos_type']  = $request->kos_type;
                $data['room_size'] = $request->room_size;
            }

            if (in_array($residenceType, ['kontrakan', 'rumah_sewa'])) {
                $data['bedroom_count']  = $request->bedroom_count;
                $data['bathroom_count'] = $request->bathroom_count;
                $data['building_size']  = $request->building_size;
                $data['land_size']      = $request->land_size;
            }

            if ($residenceType === 'apartemen') {
                $data['unit_type']      = $request->unit_type;
                $data['floor_number']   = $request->floor_number;
                $data['tower_name']     = $request->tower_name;
                $data['bedroom_count']  = $request->bedroom_count;
                $data['bathroom_count'] = $request->bathroom_count;
                $data['room_size']      = $request->room_size;
            }

            // ── Handle fasilitas ──────────────────────────────────────
            $facilities = $request->input('facilities', []);
            if ($request->filled('custom_facilities')) {
                $custom     = array_filter(array_map('trim', explode(',', $request->custom_facilities)));
                $facilities = array_values(array_unique(array_merge($facilities, $custom)));
            }
            $data['facilities'] = array_values($facilities);

            // ── Handle upload foto ────────────────────────────────────
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $images[] = $image->store('residences', 'public');
                }
            }
            $data['images'] = $images;

            $residence = Residence::create($data);

            return redirect()->route('provider.residence.residences.show', $residence)
                ->with('success', 'Hunian berhasil ditambahkan!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan hunian: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(Residence $residence)
    {
        $this->authorize('update', $residence);
        $categories = Category::where('type', 'residence')->get();
        return view('provider_residence.residences.edit', compact('residence', 'categories'));
    }

    public function update(Request $request, Residence $residence)
    {
        $this->authorize('update', $residence);

        try {
            $residenceType = $request->input('residence_type', $residence->residence_type);

            $rules = [
                'name'              => 'required|string|max:255',
                'description'       => 'required|string',
                'category_id'       => 'required|exists:categories,id',
                'residence_type'    => 'required|in:kos,kontrakan,apartemen,rumah_sewa',
                'rental_period'     => 'required|in:monthly,yearly',
                'address'           => 'required|string|max:500',
                'latitude'          => 'nullable|numeric|between:-90,90',
                'longitude'         => 'nullable|numeric|between:-180,180',
                'price_per_month'   => 'required|numeric|min:0',
                'capacity'          => 'required|integer|min:1',
                'available_slots'   => 'required|integer|min:0',
                'furnish_status'    => 'nullable|in:unfurnished,semi_furnished,full_furnished',
                'discount_type'     => 'nullable|in:percentage,flat',
                'discount_value'    => 'nullable|numeric|min:0',
                'facilities'        => 'nullable|array',
                'custom_facilities' => 'nullable|string|max:500',
                'images'            => 'nullable|array|max:10',
                'images.*'          => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            ];

            if ($residenceType === 'kos') {
                $rules['kos_type']  = 'required|in:putra,putri,campur';
                $rules['room_size'] = 'nullable|numeric|min:1';
            }
            if (in_array($residenceType, ['kontrakan', 'rumah_sewa'])) {
                $rules['bedroom_count']  = 'required|integer|min:1';
                $rules['bathroom_count'] = 'required|integer|min:1';
                $rules['building_size']  = 'nullable|numeric|min:1';
                $rules['land_size']      = 'nullable|numeric|min:1';
            }
            if ($residenceType === 'apartemen') {
                $rules['unit_type']      = 'required|in:studio,1BR,2BR,3BR,4BR';
                $rules['floor_number']   = 'required|integer|min:1';
                $rules['tower_name']     = 'nullable|string|max:100';
                $rules['bedroom_count']  = 'nullable|integer|min:0';
                $rules['bathroom_count'] = 'nullable|integer|min:1';
                $rules['room_size']      = 'nullable|numeric|min:1';
            }

            $request->validate($rules);

            $data = [
                'category_id'    => $request->category_id,
                'name'           => $request->name,
                'description'    => $request->description,
                'address'        => $request->address,
                'latitude'       => $request->latitude,
                'longitude'      => $request->longitude,
                'residence_type' => $residenceType,
                'rental_period'  => $request->rental_period,
                'price'          => (float) $request->price_per_month,
                'capacity'       => (int) $request->capacity,
                'available_slots'=> min((int) $request->available_slots, (int) $request->capacity),  // ← TAMBAH
                'furnish_status' => $request->furnish_status,
                'is_active'      => $request->boolean('is_active', $residence->is_active),
                'discount_type'  => $request->discount_type ?: null,
                'discount_value' => $request->discount_type ? $request->discount_value : null,
                // Reset semua field spesifik dulu, isi ulang sesuai tipe
                'kos_type'       => null,
                'room_size'      => null,
                'bedroom_count'  => null,
                'bathroom_count' => null,
                'building_size'  => null,
                'land_size'      => null,
                'unit_type'      => null,
                'floor_number'   => null,
                'tower_name'     => null,
            ];

            if ($residenceType === 'kos') {
                $data['kos_type']  = $request->kos_type;
                $data['room_size'] = $request->room_size;
            }
            if (in_array($residenceType, ['kontrakan', 'rumah_sewa'])) {
                $data['bedroom_count']  = $request->bedroom_count;
                $data['bathroom_count'] = $request->bathroom_count;
                $data['building_size']  = $request->building_size;
                $data['land_size']      = $request->land_size;
            }
            if ($residenceType === 'apartemen') {
                $data['unit_type']      = $request->unit_type;
                $data['floor_number']   = $request->floor_number;
                $data['tower_name']     = $request->tower_name;
                $data['bedroom_count']  = $request->bedroom_count;
                $data['bathroom_count'] = $request->bathroom_count;
                $data['room_size']      = $request->room_size;
            }

            // Fasilitas
            $facilities = $request->input('facilities', []);
            if ($request->filled('custom_facilities')) {
                $custom     = array_filter(array_map('trim', explode(',', $request->custom_facilities)));
                $facilities = array_values(array_unique(array_merge($facilities, $custom)));
            }
            $data['facilities'] = array_values($facilities);

            // Kelola gambar — pertahankan yang lama, hapus yang dimarked, tambah yang baru
            $existingImages = is_array($residence->images) ? $residence->images : [];
            $removedRaw     = $request->input('removed_images', '[]');
            $removedIndexes = is_array($removedRaw) ? $removedRaw : (json_decode($removedRaw, true) ?? []);

            foreach ($removedIndexes as $idx) {
                if (isset($existingImages[$idx])) {
                    Storage::disk('public')->delete($existingImages[$idx]);
                    unset($existingImages[$idx]);
                }
            }
            $existingImages = array_values($existingImages);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if ($file && $file->isValid()) {
                        $existingImages[] = $file->store('residences', 'public');
                    }
                }
            }
            $data['images'] = $existingImages;

            $residence->update($data);

            return redirect()->route('provider.residence.residences.show', $residence)
                ->with('success', 'Hunian berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui hunian: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Residence $residence)
    {
        $this->authorize('delete', $residence);

        try {
            $activeBookings = $residence->bookings()
                ->whereIn('status', ['pending', 'approved'])
                ->count();

            if ($activeBookings > 0) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus hunian dengan booking aktif.');
            }

            foreach ($residence->images ?? [] as $image) {
                Storage::disk('public')->delete($image);
            }

            $residence->delete();

            return redirect()->route('provider.residence.residences.index')
                ->with('success', 'Hunian berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus hunian: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Residence $residence)
    {
        $this->authorize('update', $residence);
        $residence->update(['is_active' => !$residence->is_active]);
        $status = $residence->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Hunian berhasil {$status}.");
    }
}