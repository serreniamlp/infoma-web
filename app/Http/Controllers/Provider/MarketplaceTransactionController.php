<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceTransactionController extends Controller
{
    /**
     * Display a listing of marketplace transactions for provider.
     */
    public function index(Request $request)
    {
        $query = MarketplaceTransaction::with(['product', 'buyer', 'seller'])
            ->where('seller_id', Auth::id());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by transaction code or buyer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);

        // Statistics for dashboard
        $stats = [
            'total' => MarketplaceTransaction::where('seller_id', Auth::id())->count(),
            'pending' => MarketplaceTransaction::where('seller_id', Auth::id())->where('status', 'pending')->count(),
            'completed' => MarketplaceTransaction::where('seller_id', Auth::id())->where('status', 'completed')->count(),
            'total_revenue' => MarketplaceTransaction::where('seller_id', Auth::id())
                ->where('status', 'completed')->sum('total_amount'),
        ];

        return view('provider.marketplace.transactions.index', compact('transactions', 'stats'));
    }

    /**
     * Display the specified transaction.
     */
    public function show(MarketplaceTransaction $transaction)
    {
        // Check if user is the seller
        if ($transaction->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $transaction->load(['product', 'buyer', 'seller', 'rating']);

        return view('provider.marketplace.transactions.show', compact('transaction'));
    }

    /**
     * Update transaction status.
     */
    public function updateStatus(Request $request, MarketplaceTransaction $transaction)
    {
        // Check if user is the seller
        if ($transaction->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'status' => 'required|in:confirmed,in_progress,completed,cancelled',
            'seller_notes' => 'nullable|string|max:1000',
            'cancellation_reason' => 'nullable|string|max:1000|required_if:status,cancelled',
        ]);

        $data = $request->only(['status', 'seller_notes']);

        if ($request->status === 'cancelled') {
            $data['cancellation_reason'] = $request->cancellation_reason;
            $data['cancelled_at'] = now();

            // Restore product stock
            $transaction->product->increment('stock_quantity', $transaction->quantity);
        } elseif ($request->status === 'completed') {
            $data['completed_at'] = now();
        }

        $transaction->update($data);

        return redirect()->back()->with('success', 'Status transaksi berhasil diperbarui!');
    }

    /**
     * Confirm payment for transaction.
     */
    public function confirmPayment(Request $request, MarketplaceTransaction $transaction)
    {
        // Check if user is the seller
        if ($transaction->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'payment_status' => 'required|in:paid,failed',
            'seller_notes' => 'nullable|string|max:1000',
        ]);

        $transaction->update([
            'payment_status' => $request->payment_status,
            'seller_notes' => $request->seller_notes,
        ]);

        $message = $request->payment_status === 'paid' 
            ? 'Pembayaran berhasil dikonfirmasi!' 
            : 'Pembayaran ditandai sebagai gagal!';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Get transaction statistics for charts.
     */
    public function getStatistics(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        $transactions = MarketplaceTransaction::where('seller_id', Auth::id())
            ->where('created_at', '>=', $startDate)
            ->get();

        // Daily revenue for chart
        $dailyRevenue = $transactions->where('status', 'completed')
            ->groupBy(function ($transaction) {
                return $transaction->created_at->format('Y-m-d');
            })
            ->map(function ($transactions) {
                return $transactions->sum('total_amount');
            });

        // Status distribution
        $statusDistribution = $transactions->groupBy('status')
            ->map(function ($transactions) {
                return $transactions->count();
            });

        return response()->json([
            'daily_revenue' => $dailyRevenue,
            'status_distribution' => $statusDistribution,
            'total_transactions' => $transactions->count(),
            'total_revenue' => $transactions->where('status', 'completed')->sum('total_amount'),
        ]);
    }
}
