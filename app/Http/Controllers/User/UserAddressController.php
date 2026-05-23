<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    public function index()
    {
        $addresses = UserAddress::where('user_id', auth()->id())
            ->orderByDesc('is_default')
            ->orderBy('created_at')
            ->get();

        return view('user.addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label'          => 'required|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string',
            'is_default'     => 'nullable|boolean',
        ], [
            'label.required'          => 'Label alamat wajib diisi.',
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'phone.required'          => 'Nomor telepon wajib diisi.',
            'address.required'        => 'Alamat lengkap wajib diisi.',
        ]);

        $userId    = auth()->id();
        $isDefault = $request->boolean('is_default', false);

        // Jika ini alamat pertama, otomatis jadikan default
        $count = UserAddress::where('user_id', $userId)->count();
        if ($count === 0) {
            $isDefault = true;
        }

        $address = UserAddress::create([
            'user_id'        => $userId,
            'label'          => $request->label,
            'recipient_name' => $request->recipient_name,
            'phone'          => $request->phone,
            'address'        => $request->address,
            'is_default'     => $isDefault,
        ]);

        // Jika di-set default, unset yang lain
        if ($isDefault) {
            $address->setAsDefault();
        }

        return redirect()->route('user.addresses.index')
            ->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(Request $request, UserAddress $address)
    {
        // Pastikan alamat milik user yang login
        abort_if($address->user_id !== auth()->id(), 403);

        $request->validate([
            'label'          => 'required|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string',
            'is_default'     => 'nullable|boolean',
        ]);

        $address->update([
            'label'          => $request->label,
            'recipient_name' => $request->recipient_name,
            'phone'          => $request->phone,
            'address'        => $request->address,
        ]);

        if ($request->boolean('is_default')) {
            $address->setAsDefault();
        }

        return redirect()->route('user.addresses.index')
            ->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(UserAddress $address)
    {
        abort_if($address->user_id !== auth()->id(), 403);

        $wasDefault = $address->is_default;
        $address->delete();

        // Jika yang dihapus adalah default, set alamat pertama yang tersisa sebagai default
        if ($wasDefault) {
            $first = UserAddress::where('user_id', auth()->id())->first();
            if ($first) {
                $first->update(['is_default' => true]);
            }
        }

        return redirect()->route('user.addresses.index')
            ->with('success', 'Alamat berhasil dihapus.');
    }

    public function setDefault(UserAddress $address)
    {
        abort_if($address->user_id !== auth()->id(), 403);

        $address->setAsDefault();

        return redirect()->route('user.addresses.index')
            ->with('success', "\"{$address->label}\" dijadikan alamat default.");
    }
}