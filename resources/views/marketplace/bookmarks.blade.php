@extends('layouts.app')

@section('title', 'Bookmark Marketplace')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('marketplace.index') }}">Marketplace</a></li>
            <li class="breadcrumb-item active">Bookmark</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Produk yang Disimpan</h2>
        <a href="{{ route('marketplace.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Kembali ke Marketplace
        </a>
    </div>

    @if($bookmarks->count() > 0)
        <div class="row">
            @foreach($bookmarks as $bookmark)
                @php
                    $product = $bookmark->bookmarkable;
                @endphp
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 product-card">
                        <div class="position-relative">
                            <img src="{{ $product->main_image }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-{{ $product->condition == 'new' ? 'success' : ($product->condition == 'like_new' ? 'primary' : 'secondary') }}">
                                    {{ $product->condition_label }}
                                </span>
                            </div>
                            <button class="btn btn-sm btn-outline-light position-absolute top-0 start-0 m-2 bookmark-btn"
                                    data-product-id="{{ $product->id }}"
                                    data-bookmarked="true">
                                <i class="fas fa-heart text-danger"></i>
                            </button>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">{{ Str::limit($product->name, 50) }}</h6>
                            <p class="card-text text-muted small">{{ Str::limit($product->description, 80) }}</p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="text-primary mb-0">Rp {{ number_format($product->price, 0, ',', '.') }}</h5>
                                    <small class="text-muted">
                                        <i class="fas fa-eye"></i> {{ $product->views_count }}
                                    </small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> {{ $product->location }}
                                    </small>
                                    <small class="text-muted">
                                        Stok: {{ $product->stock_quantity }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('marketplace.show', $product) }}" class="btn btn-primary w-100">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $bookmarks->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-heart fa-3x text-muted mb-3"></i>
            <h4>Belum ada produk yang disimpan</h4>
            <p class="text-muted">Simpan produk favorit Anda untuk melihatnya nanti</p>
            <a href="{{ route('marketplace.index') }}" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Jelajahi Marketplace
            </a>
        </div>
    @endif
</div>

<script>
// Bookmark functionality
document.addEventListener('DOMContentLoaded', function() {
    const bookmarkBtns = document.querySelectorAll('.bookmark-btn');

    bookmarkBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;

            fetch(`/marketplace/${productId}/bookmark`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (!data.isBookmarked) {
                    // Remove the card from the page
                    this.closest('.col-lg-4').remove();

                    // Check if there are any bookmarks left
                    const remainingBookmarks = document.querySelectorAll('.product-card');
                    if (remainingBookmarks.length === 0) {
                        location.reload(); // Reload to show empty state
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
</script>
@endsection
