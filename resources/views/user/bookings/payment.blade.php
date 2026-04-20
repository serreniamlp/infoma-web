@extends('layouts.app')

@section('title', 'Pembayaran - Infoma')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Pembayaran</h1>
            <p class="text-gray-600 mt-2">Lakukan pembayaran untuk booking #{{ $booking->booking_code }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Payment Form -->
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('user.bookings.processPayment', $booking) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('POST')

                    <!-- Payment Method -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Metode Pembayaran</h2>

                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="radio" name="payment_method" id="bank_transfer" value="bank_transfer"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" checked>
                                <label for="bank_transfer" class="ml-3 flex items-center">
                                    <i class="fas fa-university text-blue-600 mr-3"></i>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Transfer Bank</div>
                                        <div class="text-sm text-gray-500">Transfer ke rekening yang tersedia</div>
                                    </div>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="radio" name="payment_method" id="e_wallet" value="e_wallet"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="e_wallet" class="ml-3 flex items-center">
                                    <i class="fas fa-mobile-alt text-green-600 mr-3"></i>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">E-Wallet</div>
                                        <div class="text-sm text-gray-500">DANA, OVO, GoPay, dll</div>
                                    </div>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="radio" name="payment_method" id="credit_card" value="credit_card"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="credit_card" class="ml-3 flex items-center">
                                    <i class="fas fa-credit-card text-purple-600 mr-3"></i>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Kartu Kredit</div>
                                        <div class="text-sm text-gray-500">Visa, Mastercard, dll</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Transfer Details -->
                    <div id="bankTransferDetails" class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Transfer Bank</h3>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <h4 class="font-medium text-blue-900 mb-2">Rekening Tujuan</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-blue-700">Bank:</span>
                                    <span class="font-medium text-blue-900">Bank Mandiri</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-700">No. Rekening:</span>
                                    <span class="font-medium text-blue-900">1234567890</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-700">Atas Nama:</span>
                                    <span class="font-medium text-blue-900">PT Infoma Indonesia</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bank Pengirim <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="bank_name" id="bank_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('bank_name') border-red-500 @enderror"
                                       placeholder="Nama bank pengirim">
                                @error('bank_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    No. Rekening Pengirim <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="account_number" id="account_number" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('account_number') border-red-500 @enderror"
                                       placeholder="Nomor rekening pengirim">
                                @error('account_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="account_holder" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Pemilik Rekening <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="account_holder" id="account_holder" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('account_holder') border-red-500 @enderror"
                                       placeholder="Nama pemilik rekening">
                                @error('account_holder')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- E-Wallet Details -->
                    <div id="eWalletDetails" class="bg-white rounded-lg shadow-sm p-6 hidden">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail E-Wallet</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="ewallet_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Jenis E-Wallet <span class="text-red-500">*</span>
                                </label>
                                <select name="ewallet_type" id="ewallet_type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Pilih E-Wallet</option>
                                    <option value="dana">DANA</option>
                                    <option value="ovo">OVO</option>
                                    <option value="gopay">GoPay</option>
                                    <option value="shopeepay">ShopeePay</option>
                                </select>
                            </div>

                            <div>
                                <label for="ewallet_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor E-Wallet <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="ewallet_number" id="ewallet_number"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Nomor E-Wallet">
                            </div>
                        </div>
                    </div>

                    <!-- Credit Card Details -->
                    <div id="creditCardDetails" class="bg-white rounded-lg shadow-sm p-6 hidden">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Kartu Kredit</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="card_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Kartu <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="card_number" id="card_number"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="1234 5678 9012 3456">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal Kadaluarsa <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="expiry_date" id="expiry_date"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="MM/YY">
                                </div>

                                <div>
                                    <label for="cvv" class="block text-sm font-medium text-gray-700 mb-2">
                                        CVV <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="cvv" id="cvv"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="123">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Proof -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Bukti Pembayaran</h3>

                        <div>
                            <label for="payment_proof" class="block text-sm font-medium text-gray-700 mb-2">
                                Upload Bukti Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="payment_proof" id="payment_proof" accept=".pdf,.jpg,.jpeg,.png" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('payment_proof') border-red-500 @enderror">
                            <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, PNG (Max: 5MB)</p>
                            @error('payment_proof')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('user.bookings.show', $booking) }}"
                           class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                            <i class="fas fa-credit-card mr-2"></i>Konfirmasi Pembayaran
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Payment Summary -->
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pembayaran</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Harga Dasar</span>
                            <span class="font-medium">Rp {{ number_format($booking->transaction->original_amount) }}</span>
                        </div>

                        @if($booking->transaction->discount_amount > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Diskon</span>
                                <span>- Rp {{ number_format($booking->transaction->discount_amount) }}</span>
                            </div>
                        @endif

                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between text-lg font-semibold">
                                <span>Total</span>
                                <span>Rp {{ number_format($booking->transaction->final_amount) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-yellow-400 mr-3 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-medium text-yellow-800">Perhatian</h4>
                                <ul class="text-xs text-yellow-700 mt-1 space-y-1">
                                    <li>• Pastikan nominal transfer sesuai</li>
                                    <li>• Upload bukti pembayaran yang jelas</li>
                                    <li>• Pembayaran akan diverifikasi dalam 1x24 jam</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Show/hide payment method details
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Hide all details
        document.getElementById('bankTransferDetails').classList.add('hidden');
        document.getElementById('eWalletDetails').classList.add('hidden');
        document.getElementById('creditCardDetails').classList.add('hidden');

        // Show selected details
        if (this.value === 'bank_transfer') {
            document.getElementById('bankTransferDetails').classList.remove('hidden');
        } else if (this.value === 'e_wallet') {
            document.getElementById('eWalletDetails').classList.remove('hidden');
        } else if (this.value === 'credit_card') {
            document.getElementById('creditCardDetails').classList.remove('hidden');
        }
    });
});

// File size validation
document.getElementById('payment_proof').addEventListener('change', function() {
    const file = this.files[0];
    if (file && file.size > 5 * 1024 * 1024) { // 5MB
        alert('File terlalu besar. Maksimal 5MB.');
        this.value = '';
    }
});

// Format card number
document.getElementById('card_number').addEventListener('input', function() {
    let value = this.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    this.value = formattedValue;
});

// Format expiry date
document.getElementById('expiry_date').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    this.value = value;
});

// Format CVV
document.getElementById('cvv').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 3);
});
</script>
@endpush
@endsection
