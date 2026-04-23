<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{
    /**
     * Halaman "Mulai Berjualan"
     */
    public function index()
    {
        // Kalau sudah seller, langsung ke my-products
        if (Auth::user()->isSeller()) {
            return redirect()->route('user.marketplace.sell.my-products');
        }

        return view('user.marketplace.become-seller');
    }

    /**
     * Aktivasi is_seller (flow sederhana untuk sementara)
     */
    public function activate()
    {
        $user = Auth::user();

        if ($user->isSeller()) {
            return redirect()->route('user.marketplace.sell.my-products')
                ->with('info', 'Anda sudah terdaftar sebagai penjual.');
        }

        $user->update(['is_seller' => true]);

        return redirect()->route('user.marketplace.sell.my-products')
            ->with('success', 'Selamat! Akun penjual Anda sudah aktif. Mulai jual barang sekarang!');
    }
}