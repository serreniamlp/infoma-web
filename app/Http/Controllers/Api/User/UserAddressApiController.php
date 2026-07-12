<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAddressApiController extends Controller
{
    public function index()
    {
        $addresses = UserAddress::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'label'          => 'required|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string',
            'is_default'     => 'boolean',
        ]);

        $isFirst = UserAddress::where('user_id', Auth::id())->count() === 0;

        $address = UserAddress::create([
            'user_id'        => Auth::id(),
            'label'          => $request->label,
            'recipient_name' => $request->recipient_name,
            'phone'          => $request->phone,
            'address'        => $request->address,
            'is_default'     => $isFirst ? true : ($request->is_default ?? false),
        ]);

        if ($address->is_default && !$isFirst) {
            $address->setAsDefault();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Alamat berhasil ditambahkan.',
            'data'    => $address,
        ], 201);
    }

    public function update(Request $request, UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'label'          => 'sometimes|string|max:50',
            'recipient_name' => 'sometimes|string|max:255',
            'phone'          => 'sometimes|string|max:20',
            'address'        => 'sometimes|string',
            'is_default'     => 'boolean',
        ]);

        $address->update($request->only(['label', 'recipient_name', 'phone', 'address']));

        if ($request->has('is_default') && $request->is_default) {
            $address->setAsDefault();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Alamat berhasil diupdate.',
            'data'    => $address->fresh(),
        ]);
    }

    public function destroy(UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if ($address->is_default) {
            $other = UserAddress::where('user_id', Auth::id())
                ->where('id', '!=', $address->id)
                ->first();
                
            if ($other) {
                $other->setAsDefault();
            }
        }

        $address->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Alamat berhasil dihapus.',
        ]);
    }

    public function setDefault(UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $address->setAsDefault();

        return response()->json([
            'status'  => 'success',
            'message' => 'Alamat utama berhasil diubah.',
            'data'    => $address->fresh(),
        ]);
    }
}
