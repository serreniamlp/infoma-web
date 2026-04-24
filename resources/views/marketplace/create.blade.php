@extends('layouts.app')

@section('title', 'Tambah Produk - EduLiving')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('user.marketplace.seller.my-products') }}"
                   class="text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Produk Baru</h1>
            </div>
            <p class="text-gray-600 ml-7">Isi informasi produk yang ingin kamu jual</p>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                <div>
                    <h3 class="font-medium text-red-800 mb-1">Ada kesalahan input:</h3>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <form action="{{ route('user.marketplace.seller.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Informasi Dasar -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-100">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>Informasi Dasar
                </h2>

                <div class="space-y-5">
                    <!-- Nama Produk -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Nama Produk <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               placeholder="Contoh: Laptop Asus VivoBook 14"
                               class="w-full px-4 py-2.5 border {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kategori & Kondisi -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select id="category_id" name="category_id"
                                    class="w-full px-4 py-2.5 border {{ $errors->has('category_id') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="condition" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Kondisi <span class="text-red-500">*</span>
                            </label>
                            <select id="condition" name="condition"
                                    class="w-full px-4 py-2.5 border {{ $errors->has('condition') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                <option value="">Pilih Kondisi</option>
                                <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>Baru</option>
                                <option value="like_new" {{ old('condition') == 'like_new' ? 'selected' : '' }}>Seperti Baru</option>
                                <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Baik</option>
                                <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Cukup</option>
                                <option value="needs_repair" {{ old('condition') == 'needs_repair' ? 'selected' : '' }}>Perlu Perbaikan</option>
                            </select>
                            @error('condition')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Harga & Stok -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Harga <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium text-sm">Rp</span>
                                <input type="number" id="price" name="price" value="{{ old('price') }}"
                                       placeholder="0" min="0" step="100"
                                       class="w-full pl-10 pr-4 py-2.5 border {{ $errors->has('price') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            </div>
                            @error('price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Stok <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="stock_quantity" name="stock_quantity"
                                   value="{{ old('stock_quantity', 1) }}" min="1"
                                   class="w-full px-4 py-2.5 border {{ $errors->has('stock_quantity') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            @error('stock_quantity')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Lokasi -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Lokasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}"
                               placeholder="Contoh: Bandung, Jawa Barat"
                               class="w-full px-4 py-2.5 border {{ $errors->has('location') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        @error('location')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Deskripsi Produk <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" name="description" rows="5"
                                  placeholder="Jelaskan detail produk, kondisi, spesifikasi, alasan dijual, dll."
                                  class="w-full px-4 py-2.5 border {{ $errors->has('description') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-none">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tags -->
                    <div>
                        <label for="tags" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Tags <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <input type="text" id="tags" name="tags" value="{{ old('tags') }}"
                               placeholder="Contoh: elektronik, laptop, second (pisahkan dengan koma)"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        <p class="text-gray-500 text-xs mt-1">Pisahkan dengan koma untuk memudahkan pencarian</p>
                    </div>
                </div>
            </div>

            <!-- Foto Produk -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b border-gray-100">
                    <i class="fas fa-images text-blue-500 mr-2"></i>Foto Produk
                </h2>

                <!-- Upload Area -->
                <div id="upload-area"
                     class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-pointer"
                     onclick="document.getElementById('images').click()">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-600 font-medium">Klik untuk upload foto</p>
                    <p class="text-gray-400 text-sm mt-1">JPG, PNG, GIF • Maksimal 2MB per foto • Minimal 1 foto</p>
                </div>
                <input type="file" id="images" name="images[]" multiple accept="image/*"
                       class="hidden" onchange="previewImages(this)">

                @error('images.*')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror

                <!-- Preview Container -->
                <div id="preview-container" class="grid grid-cols-3 md:grid-cols-5 gap-3 mt-4 hidden">
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-between bg-white rounded-xl shadow-sm p-6">
                <a href="{{ route('user.marketplace.seller.my-products') }}"
                   class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Produk
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImages(input) {
    const container = document.getElementById('preview-container');
    container.innerHTML = '';

    if (input.files && input.files.length > 0) {
        container.classList.remove('hidden');

        Array.from(input.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative group';
                    div.innerHTML = `
                        <img src="${e.target.result}"
                             class="w-full h-24 object-cover rounded-lg border border-gray-200">
                        <div class="absolute inset-0 bg-black bg-opacity-40 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="text-white text-xs font-medium">Foto ${index + 1}</span>
                        </div>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    } else {
        container.classList.add('hidden');
    }
}
</script>
@endsection