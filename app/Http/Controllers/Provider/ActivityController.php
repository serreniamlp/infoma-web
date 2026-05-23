<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Concerns\CheckProfileComplete;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    use CheckProfileComplete;

    public function index(Request $request)
    {
        $query = Activity::where('provider_id', auth()->id())
            ->with(['category'])
            ->withCount(['bookings', 'ratings'])
            ->withAvg('ratings', 'rating');

        // Filters
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)
                    ->where('registration_deadline', '>', now());
            } elseif ($request->status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)
                        ->orWhere('registration_deadline', '<=', now());
                });
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $activities = $query->orderBy('event_date', 'asc')->paginate(10);
        $categories = Category::where('type', 'activity')->get();

        return view('provider_event.activities.index', compact('activities', 'categories'));
    }

    public function show(Activity $activity)
    {
        $this->authorize('view', $activity);

        $activity->load(['category', 'bookings.user', 'ratings.user']);

        return view('provider_event.activities.show', compact('activity'));
    }

    // app/Http/Controllers/Provider/ResidenceController.php
    // Tambahkan method ini, lalu panggil di create() dan store()

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
        if ($redirect = $this->checkProfileComplete(route('provider.event.activities.create'))) return $redirect;
        $categories = Category::where('type', 'activity')->get();
        return view('provider_event.activities.create', compact('categories'));
        // return view('provider_event.activities.create');
    }

    public function store(Request $request)
    {
        if ($redirect = $this->checkProviderApproved()) return $redirect;

        try {
            $request->validate([
                'name'                  => 'required|string|max:255',
                'description'           => 'required|string',
                'category_id'           => 'required|exists:categories,id',
                'location'              => 'required|string|max:500',
                'event_date'            => 'required|date|after:now',
                'registration_deadline' => 'required|date|before:event_date',
                'latitude'              => 'nullable|numeric|between:-90,90',
                'longitude'             => 'nullable|numeric|between:-180,180',
                'price'                 => 'required|numeric|min:0',
                'capacity'              => 'required|integer|min:1',
                'discount_type'         => 'nullable|in:percentage,fixed',
                'discount_value'        => 'nullable|numeric|min:0',
                'images'                => 'required|array|max:10',
                'images.*'              => 'image|mimes:jpg,jpeg,png,webp|max:2048',
                'speakers'              => 'nullable|array',
                'speakers.*.name'       => 'required_with:speakers|string|max:255',
                'speakers.*.title'      => 'nullable|string|max:255',
                'benefits'              => 'nullable|array',
                'benefits.*'            => 'string|max:255',
                'is_active'             => 'nullable|boolean',
            ], [
                'name.required'                  => 'Nama kegiatan wajib diisi.',
                'description.required'           => 'Deskripsi wajib diisi.',
                'category_id.required'           => 'Kategori wajib dipilih.',
                'category_id.exists'             => 'Kategori tidak valid.',
                'location.required'              => 'Lokasi wajib diisi.',
                'event_date.required'            => 'Tanggal event wajib diisi.',
                'event_date.after'               => 'Tanggal event harus setelah hari ini.',
                'registration_deadline.required' => 'Deadline pendaftaran wajib diisi.',
                'registration_deadline.before'   => 'Deadline pendaftaran harus sebelum tanggal event.',
                'price.required'                 => 'Harga wajib diisi.',
                'price.numeric'                  => 'Harga harus berupa angka.',
                'capacity.required'              => 'Kapasitas wajib diisi.',
                'capacity.integer'               => 'Kapasitas harus berupa angka.',
                'images.required'                => 'Minimal 1 foto kegiatan wajib diupload.',
                'images.*.image'                 => 'File harus berupa gambar.',
                'images.*.max'                   => 'Ukuran gambar maksimal 2MB.',
            ]);

            $data = $request->except(['_token', '_method', 'images']);

            $data['provider_id'] = auth()->id();
            $data['is_active']   = $request->boolean('is_active', true);

            // Discount — kosongkan value kalau tidak ada tipe
            if (empty($data['discount_type'])) {
                $data['discount_type']  = null;
                $data['discount_value'] = null;
            }

            // Handle image uploads
            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $images[] = $image->store('activities', 'public');
                }
                $data['images'] = $images;
            }

            // Bersihkan speakers — hapus entry yang name-nya kosong
            if (!empty($data['speakers'])) {
                $data['speakers'] = array_values(
                    array_filter($data['speakers'], fn($s) => !empty($s['name']))
                );
            }

            // Bersihkan benefits — hapus entry yang kosong
            if (!empty($data['benefits'])) {
                $data['benefits'] = array_values(
                    array_filter($data['benefits'], fn($b) => !empty(trim($b)))
                );
            }

            $data['available_slots'] = $data['capacity'];

            $activity = Activity::create($data);

            return redirect()->route('provider.event.activities.show', $activity)
                ->with('success', 'Kegiatan berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan kegiatan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(Activity $activity)
    {
        $this->authorize('update', $activity);

        $categories = Category::where('type', 'activity')->get();

        return view('provider_event.activities.edit', compact('activity', 'categories'));
    }

    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        try {
            $data = $request->validated();

            // Handle image uploads
            if ($request->hasFile('images')) {
                // Delete old images
                $oldImages = $activity->images ?? [];
                foreach ($oldImages as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }

                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('activities', 'public');
                    $images[] = $path;
                }
                $data['images'] = $images; // rely on $casts to store as JSON
            }

            $activity->update($data);

            return redirect()->route('provider.event.activities.show', $activity)
                ->with('success', 'Activity berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate activity: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Activity $activity)
    {
        $this->authorize('delete', $activity);

        try {
            // Check if there are active bookings
            $activeBookings = $activity->bookings()
                ->whereIn('status', ['pending', 'approved'])
                ->count();

            if ($activeBookings > 0) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus activity dengan booking aktif');
            }

            // Delete images
            $images = $activity->images ?? [];
            foreach ($images as $image) {
                Storage::disk('public')->delete($image);
            }

            $activity->delete();

            return redirect()->route('provider.event.activities.index')
                ->with('success', 'Activity berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus activity: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Activity $activity)
    {
        $this->authorize('update', $activity);

        $activity->update([
            'is_active' => !$activity->is_active
        ]);

        $status = $activity->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Activity berhasil {$status}");
    }
}