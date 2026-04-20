@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('marketplace.index') }}">Marketplace</a></li>
            <li class="breadcrumb-item"><a href="{{ route('marketplace.my-products') }}">Produk Saya</a></li>
            <li class="breadcrumb-item active">Edit Produk</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-edit"></i> Edit Produk</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('provider.marketplace.update', $product) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Product Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror"
                                    id="category_id" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Condition -->
                        <div class="mb-3">
                            <label for="condition" class="form-label">Kondisi Produk <span class="text-danger">*</span></label>
                            <select class="form-select @error('condition') is-invalid @enderror"
                                    id="condition" name="condition" required>
                                <option value="">Pilih Kondisi</option>
                                <option value="new" {{ old('condition', $product->condition) == 'new' ? 'selected' : '' }}>Baru</option>
                                <option value="like_new" {{ old('condition', $product->condition) == 'like_new' ? 'selected' : '' }}>Seperti Baru</option>
                                <option value="good" {{ old('condition', $product->condition) == 'good' ? 'selected' : '' }}>Baik</option>
                                <option value="fair" {{ old('condition', $product->condition) == 'fair' ? 'selected' : '' }}>Cukup</option>
                                <option value="needs_repair" {{ old('condition', $product->condition) == 'needs_repair' ? 'selected' : '' }}>Perlu Perbaikan</option>
                            </select>
                            @error('condition')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="mb-3">
                            <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror"
                                   id="price" name="price" value="{{ old('price', $product->price) }}" min="0" step="100" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Stock Quantity -->
                        <div class="mb-3">
                            <label for="stock_quantity" class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                                   id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="mb-3">
                            <label for="location" class="form-label">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror"
                                   id="location" name="location" value="{{ old('location', $product->location) }}"
                                   placeholder="Contoh: Jakarta Selatan, DKI Jakarta" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Images -->
                        @if($product->images && count($product->images) > 0)
                            <div class="mb-3">
                                <label class="form-label">Foto Saat Ini</label>
                                <div class="row">
                                    @foreach($product->images as $image)
                                        <div class="col-md-3 mb-2">
                                            <div class="card">
                                                <img src="{{ asset('storage/' . $image) }}" class="card-img-top" style="height: 100px; object-fit: cover;">
                                                <div class="card-body p-2">
                                                    <small class="text-muted">Foto {{ $loop->iteration }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="form-text">Upload foto baru untuk mengganti foto yang ada</div>
                            </div>
                        @endif

                        <!-- New Images -->
                        <div class="mb-3">
                            <label for="images" class="form-label">Foto Produk Baru</label>
                            <input type="file" class="form-control @error('images.*') is-invalid @enderror"
                                   id="images" name="images[]" multiple accept="image/*">
                            <div class="form-text">Upload foto baru untuk mengganti foto yang ada. Format: JPG, PNG, GIF. Maksimal 2MB per foto.</div>
                            @error('images.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tags -->
                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control @error('tags') is-invalid @enderror"
                                   id="tags" name="tags" value="{{ old('tags', $product->tags ? implode(', ', $product->tags) : '') }}"
                                   placeholder="Contoh: elektronik, gadget, smartphone (pisahkan dengan koma)">
                            <div class="form-text">Pisahkan tags dengan koma untuk memudahkan pencarian</div>
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi Produk <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="5" required
                                      placeholder="Jelaskan detail produk, kondisi, spesifikasi, dll.">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('provider.marketplace.my-products') }}" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview functionality
document.getElementById('images').addEventListener('change', function(e) {
    const files = e.target.files;
    const previewContainer = document.getElementById('image-preview');

    if (previewContainer) {
        previewContainer.remove();
    }

    if (files.length > 0) {
        const container = document.createElement('div');
        container.id = 'image-preview';
        container.className = 'mt-3';
        container.innerHTML = '<h6>Preview Gambar Baru:</h6><div class="row"></div>';

        const row = container.querySelector('.row');

        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-2';
                    col.innerHTML = `
                        <div class="card">
                            <img src="${e.target.result}" class="card-img-top" style="height: 100px; object-fit: cover;">
                            <div class="card-body p-2">
                                <small class="text-muted">${file.name}</small>
                            </div>
                        </div>
                    `;
                    row.appendChild(col);
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('images').parentNode.appendChild(container);
    }
});

// Price formatting
document.getElementById('price').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^\d]/g, '');
    e.target.value = value;
});
</script>
@endsection
