<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Residence;
use App\Models\User;
use Illuminate\Http\Request;

class ResidenceManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Residence::with(['provider']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('provider', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $residences = $query->withCount('bookings')->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total'    => Residence::count(),
            'active'   => Residence::where('is_active', true)->count(),
            'inactive' => Residence::where('is_active', false)->count(),
        ];

        return view('admin.residences.index', compact('residences', 'stats'));
    }

    public function show(Residence $residence)
    {
        $residence->load(['provider', 'bookings.user']);
        return view('admin.residences.show', compact('residence'));
    }

    public function toggleStatus(Residence $residence)
    {
        $residence->update(['is_active' => !$residence->is_active]);
        $status = $residence->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Hunian \"{$residence->name}\" berhasil {$status}.");
    }

    public function destroy(Residence $residence)
    {
        $name = $residence->name;
        $residence->delete();
        return redirect()->route('admin.residences.index')->with('success', "Hunian \"{$name}\" berhasil dihapus.");
    }
}
