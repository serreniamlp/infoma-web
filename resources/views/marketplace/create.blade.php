@extends('layouts.app')

@section('title', 'Jual Produk - Marketplace INFOMA')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav class="mb-6">
                <ol class="flex items-center gap-1 text-sm text-gray-500">
                    <li><a href="{{ route('marketplace.index') }}"
                            class="hover:text-orange-600 transition-colors">Marketplace</a></li>
                    <li><i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i></li>
                    <li class="text-gray-900 font-medium">Jual Produk</li>
                </ol>
            </nav>

            {{-- Header --}}
            <div class="mb-8 text-center">
                <div class="w-14 h-14 bg-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-sm">
                    <i class="fas fa-tag text-white text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Jual Produk Baru</h1>
                <p class="text-gray-500 text-sm mt-1">Isi detail produk untuk mulai berjualan</p>
            </div>

            {{-- Step Indicator --}}
            <div class="flex items-center justify-center mb-8">
                @php $steps = ['Informasi Produk', 'Tags', 'Foto Produk']; @endphp
                @foreach ($steps as $idx => $label)
                    <div class="flex items-center {{ $idx < count($steps) - 1 ? 'flex-1' : '' }}">
                        <div class="flex flex-col items-center">
                            <div id="stepDot{{ $idx + 1 }}"
                                class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300
                                {{ $idx === 0 ? 'bg-orange-500 text-white shadow-md shadow-orange-200' : 'bg-white border-2 border-gray-200 text-gray-400' }}">
                                {{ $idx + 1 }}
                            </div>
                            <span id="stepLabel{{ $idx + 1 }}"
                                class="text-xs mt-1.5 font-medium hidden sm:block
                            {{ $idx === 0 ? 'text-orange-600' : 'text-gray-400' }}">
                                {{ $label }}
                            </span>
                        </div>
                        @if ($idx < count($steps) - 1)
                            <div id="stepLine{{ $idx + 1 }}"
                                class="flex-1 h-0.5 bg-gray-200 mx-2 transition-colors duration-300"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Validation errors global --}}
            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-red-700 mb-2 flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i> Ada kesalahan pada form:
                    </p>
                    <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- FORM --}}
            <div id="formCard" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- ======================= STEP 1 ======================= --}}
                <div id="step1" class="p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span
                            class="w-6 h-6 bg-orange-100 text-orange-600 rounded-full text-xs font-bold flex items-center justify-center">1</span>
                        Informasi Produk
                    </h2>
                    <p class="text-sm text-gray-500 mb-6">Detail dasar tentang produk yang ingin Anda jual</p>

                    <div class="space-y-5">

                        {{-- Nama Produk --}}
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Nama Produk <span class="text-orange-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                placeholder="Cth: Laptop ASUS VivoBook 14"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-all @error('name') border-red-400 @enderror">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kategori & Kondisi (row) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Kategori <span class="text-orange-500">*</span>
                                </label>
                                <select id="category_id" name="category_id"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 bg-white @error('category_id') border-red-400 @enderror">
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="condition" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Kondisi <span class="text-orange-500">*</span>
                                </label>
                                <select id="condition" name="condition"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 bg-white @error('condition') border-red-400 @enderror">
                                    <option value="">Pilih Kondisi</option>
                                    <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>Baru
                                    </option>
                                    <option value="like_new" {{ old('condition') == 'like_new' ? 'selected' : '' }}>Seperti
                                        Baru</option>
                                    <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Baik
                                    </option>
                                    <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Cukup
                                    </option>
                                    <option value="needs_repair"
                                        {{ old('condition') == 'needs_repair' ? 'selected' : '' }}>
                                        Perlu Perbaikan</option>
                                </select>
                                @error('condition')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Harga & Stok (row) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="price" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Harga (Rp) <span class="text-orange-500">*</span>
                                </label>
                                <div class="relative">
                                    <span
                                        class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">Rp</span>
                                    <input type="number" id="price" name="price" value="{{ old('price') }}"
                                        min="0" step="500" placeholder="0"
                                        class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 @error('price') border-red-400 @enderror">
                                </div>
                                @error('price')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="stock_quantity" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Jumlah Stok <span class="text-orange-500">*</span>
                                </label>
                                <input type="number" id="stock_quantity" name="stock_quantity"
                                    value="{{ old('stock_quantity', 1) }}" min="1"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 @error('stock_quantity') border-red-400 @enderror">
                                @error('stock_quantity')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Lokasi --}}
                        <div>
                            <label for="location" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Lokasi <span class="text-orange-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-map-marker-alt text-sm"></i>
                                </span>
                                <input type="text" id="location" name="location" value="{{ old('location') }}"
                                    placeholder="Cth: Bogor, Jawa Barat"
                                    class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 @error('location') border-red-400 @enderror">
                            </div>
                            @error('location')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Deskripsi Produk <span class="text-orange-500">*</span>
                            </label>
                            <textarea id="description" name="description" rows="5"
                                placeholder="Jelaskan kondisi, spesifikasi, riwayat penggunaan, dan detail lain yang relevan..."
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 resize-none @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end mt-8">
                        <button type="button" onclick="nextStep(2)"
                            class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors flex items-center gap-2">
                            Lanjut <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                {{-- ======================= STEP 2 ======================= --}}
                <div id="step2" class="p-6 sm:p-8 hidden">
                    <h2 class="text-lg font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span
                            class="w-6 h-6 bg-orange-100 text-orange-600 rounded-full text-xs font-bold flex items-center justify-center">2</span>
                        Tags Produk
                    </h2>
                    <p class="text-sm text-gray-500 mb-6">Tambahkan tag untuk memudahkan pembeli menemukan produk Anda</p>

                    {{-- Tags hidden field --}}
                    <input type="hidden" id="tagsHidden" name="tags" value="{{ old('tags') }}">

                    {{-- Tags input --}}
                    <div class="mb-3">
                        <label for="tagsInput" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Tags <span class="text-gray-400">(opsional)</span>
                        </label>
                        <input type="text" id="tagsInput" placeholder="Ketik tag lalu tekan koma atau Enter..."
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                        <p class="text-xs text-gray-400 mt-1.5">Pisahkan dengan koma atau tekan Enter. Cth: elektronik,
                            laptop, gaming</p>
                    </div>

                    {{-- Tags preview --}}
                    <div id="tagsPreview"
                        class="flex flex-wrap gap-2 min-h-[40px] p-3 bg-orange-50 rounded-xl border border-orange-100 mb-6">
                        <span class="text-xs text-gray-400 italic" id="tagsEmpty">Belum ada tag...</span>
                    </div>

                    {{-- Example suggestions --}}
                    <div class="mb-6">
                        <p class="text-xs text-gray-500 mb-2 font-medium">Saran tag populer:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['elektronik', 'buku', 'fashion', 'olahraga', 'perabot', 'gadget', 'sepeda', 'kamera'] as $sug)
                                <button type="button" onclick="tambahTagSaran('{{ $sug }}')"
                                    class="px-3 py-1 text-xs border border-gray-200 rounded-full text-gray-600 hover:border-orange-400 hover:text-orange-600 hover:bg-orange-50 transition-all">
                                    + {{ $sug }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-between mt-4">
                        <button type="button" onclick="prevStep(1)"
                            class="border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold px-5 py-2.5 rounded-xl transition-colors flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" onclick="nextStep(3)"
                            class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors flex items-center gap-2">
                            Lanjut <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                {{-- ======================= STEP 3 ======================= --}}
                <div id="step3" class="p-6 sm:p-8 hidden">
                    <h2 class="text-lg font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span
                            class="w-6 h-6 bg-orange-100 text-orange-600 rounded-full text-xs font-bold flex items-center justify-center">3</span>
                        Foto Produk
                    </h2>
                    <p class="text-sm text-gray-500 mb-6">Upload foto produk Anda (maks. 5 foto, 2MB per foto)</p>

                    {{-- Drop Zone --}}
                    <div id="dropZone"
                        class="border-2 border-dashed border-orange-300 rounded-2xl p-8 text-center cursor-pointer hover:border-orange-500 hover:bg-orange-50 transition-all duration-200 mb-4"
                        onclick="document.getElementById('imageInput').click()">
                        <div class="w-14 h-14 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-cloud-upload-alt text-2xl text-orange-400"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-700 mb-1">Klik untuk pilih foto</p>
                        <p class="text-xs text-gray-400">atau drag & drop di sini</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF • Maks 2MB per foto • Maks 5 foto</p>
                    </div>
                    <input type="file" id="imageInput" accept="image/*" multiple class="hidden">

                    {{-- Error msg --}}
                    <div id="imageError"
                        class="hidden mb-3 flex items-center gap-2 text-red-600 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="imageErrorMsg">Upload minimal 1 foto produk.</span>
                    </div>

                    {{-- Preview Grid --}}
                    <div id="previewGrid" class="grid grid-cols-3 sm:grid-cols-5 gap-3 mb-6"></div>

                    {{-- Photo count info --}}
                    <p class="text-xs text-gray-400 mb-6" id="photoCount">0 foto dipilih</p>

                    <div class="flex justify-between">
                        <button type="button" onclick="prevStep(2)"
                            class="border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold px-5 py-2.5 rounded-xl transition-colors flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" onclick="submitForm()" id="submitBtn"
                            class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-7 py-2.5 rounded-xl transition-colors flex items-center gap-2 shadow-sm">
                            <i class="fas fa-upload"></i> Simpan Produk
                        </button>
                    </div>
                </div>

            </div>{{-- end formCard --}}
        </div>
    </div>

    <script>
        // ==================== STATE ====================
        let currentStep = 1;
        const selectedFiles = [];
        const tagsList = [];
        const MAX_FILES = 5;

        // ==================== STEP NAVIGATION ====================
        function goToStep(n) {
            for (let i = 1; i <= 3; i++) {
                const el = document.getElementById('step' + i);
                el.classList.toggle('hidden', i !== n);
            }
            updateStepDots(n);
            currentStep = n;
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function nextStep(n) {
            if (n === 2 && !validateStep1()) return;
            goToStep(n);
        }

        function prevStep(n) {
            goToStep(n);
        }

        function updateStepDots(active) {
            for (let i = 1; i <= 3; i++) {
                const dot = document.getElementById('stepDot' + i);
                const label = document.getElementById('stepLabel' + i);
                if (i < active) {
                    dot.className =
                        'w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 bg-orange-500 text-white';
                    dot.innerHTML = '<i class="fas fa-check text-xs"></i>';
                    label.className = 'text-xs mt-1.5 font-medium hidden sm:block text-orange-500';
                } else if (i === active) {
                    dot.className =
                        'w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 bg-orange-500 text-white shadow-md shadow-orange-200';
                    dot.innerHTML = i;
                    label.className = 'text-xs mt-1.5 font-medium hidden sm:block text-orange-600';
                } else {
                    dot.className =
                        'w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 bg-white border-2 border-gray-200 text-gray-400';
                    dot.innerHTML = i;
                    label.className = 'text-xs mt-1.5 font-medium hidden sm:block text-gray-400';
                }
                if (i < 3) {
                    const line = document.getElementById('stepLine' + i);
                    line.className = i < active ?
                        'flex-1 h-0.5 bg-orange-400 mx-2 transition-colors duration-300' :
                        'flex-1 h-0.5 bg-gray-200 mx-2 transition-colors duration-300';
                }
            }
        }

        // ==================== VALIDATION ====================
        function validateStep1() {
            const required = ['name', 'category_id', 'condition', 'price', 'stock_quantity', 'location', 'description'];
            let ok = true;
            required.forEach(id => {
                const el = document.getElementById(id);
                if (!el || !el.value.trim()) {
                    el.classList.add('border-red-400');
                    el.classList.remove('border-gray-200');
                    ok = false;
                    el.addEventListener('input', () => {
                        el.classList.remove('border-red-400');
                        el.classList.add('border-gray-200');
                    }, {
                        once: true
                    });
                }
            });
            if (!ok) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
            return ok;
        }

        // ==================== TAGS ====================
        function renderTags() {
            const preview = document.getElementById('tagsPreview');
            const empty = document.getElementById('tagsEmpty');
            preview.querySelectorAll('.tag-chip').forEach(e => e.remove());

            if (tagsList.length === 0) {
                empty.style.display = '';
            } else {
                empty.style.display = 'none';
                tagsList.forEach((tag, idx) => {
                    const chip = document.createElement('span');
                    chip.className =
                        'tag-chip inline-flex items-center gap-1.5 px-3 py-1 bg-orange-500 text-white rounded-full text-xs font-semibold';
                    chip.innerHTML =
                        `${tag} <button type="button" onclick="hapusTag(${idx})" class="hover:text-orange-200 ml-0.5"><i class="fas fa-times text-xs"></i></button>`;
                    preview.appendChild(chip);
                });
            }

            document.getElementById('tagsHidden').value = tagsList.join(',');
        }

        function tambahTag(raw) {
            const tags = raw.split(',').map(t => t.trim().toLowerCase()).filter(Boolean);
            tags.forEach(t => {
                if (t && !tagsList.includes(t) && tagsList.length < 10) tagsList.push(t);
            });
            renderTags();
        }

        function hapusTag(idx) {
            tagsList.splice(idx, 1);
            renderTags();
        }

        function tambahTagSaran(tag) {
            if (!tagsList.includes(tag) && tagsList.length < 10) {
                tagsList.push(tag);
                renderTags();
            }
        }

        document.getElementById('tagsInput').addEventListener('keydown', function(e) {
            if (e.key === ',' || e.key === 'Enter') {
                e.preventDefault();
                const val = this.value.replace(',', '').trim();
                if (val) tambahTag(val);
                this.value = '';
            }
        });

        document.getElementById('tagsInput').addEventListener('blur', function() {
            const val = this.value.trim();
            if (val) {
                tambahTag(val);
                this.value = '';
            }
        });

        // Pre-fill old tags
        @if (old('tags'))
            tambahTag('{{ old('tags') }}');
        @endif

        // ==================== IMAGE UPLOAD ====================
        function renderPreviews() {
            const grid = document.getElementById('previewGrid');
            grid.innerHTML = '';
            selectedFiles.forEach((file, idx) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'relative rounded-xl overflow-hidden aspect-square bg-gray-100 group';
                    wrapper.innerHTML = `
                <img src="${e.target.result}" class="w-full h-full object-cover">
                ${idx === 0 ? '<span class="absolute top-1 left-1 bg-orange-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">Utama</span>' : ''}
                <button type="button" onclick="hapusFoto(${idx})"
                    class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <i class="fas fa-times"></i>
                </button>
            `;
                    grid.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
            document.getElementById('photoCount').textContent = `${selectedFiles.length} foto dipilih`;
        }

        function hapusFoto(idx) {
            selectedFiles.splice(idx, 1);
            renderPreviews();
        }

        function prosesFile(files) {
            const remaining = MAX_FILES - selectedFiles.length;
            Array.from(files).slice(0, remaining).forEach(file => {
                if (file.size > 2 * 1024 * 1024) {
                    alert(`File "${file.name}" melebihi 2MB`);
                    return;
                }
                if (!file.type.startsWith('image/')) {
                    alert(`File "${file.name}" bukan gambar`);
                    return;
                }
                selectedFiles.push(file);
            });
            renderPreviews();
        }

        document.getElementById('imageInput').addEventListener('change', function() {
            prosesFile(this.files);
            this.value = '';
        });

        // Drag & Drop
        const dz = document.getElementById('dropZone');
        dz.addEventListener('dragover', e => {
            e.preventDefault();
            dz.classList.add('bg-orange-50', 'border-orange-500');
        });
        dz.addEventListener('dragleave', () => dz.classList.remove('bg-orange-50', 'border-orange-500'));
        dz.addEventListener('drop', e => {
            e.preventDefault();
            dz.classList.remove('bg-orange-50', 'border-orange-500');
            prosesFile(e.dataTransfer.files);
        });

        // ==================== SUBMIT via FormData + fetch ====================
        function submitForm() {
            if (selectedFiles.length === 0) {
                document.getElementById('imageError').classList.remove('hidden');
                document.getElementById('imageErrorMsg').textContent = 'Upload minimal 1 foto produk.';
                return;
            }
            document.getElementById('imageError').classList.add('hidden');

            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            const fd = new FormData();
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            fd.append('name', document.getElementById('name').value);
            fd.append('category_id', document.getElementById('category_id').value);
            fd.append('condition', document.getElementById('condition').value);
            fd.append('price', document.getElementById('price').value);
            fd.append('stock_quantity', document.getElementById('stock_quantity').value);
            fd.append('location', document.getElementById('location').value);
            fd.append('description', document.getElementById('description').value);
            fd.append('tags', document.getElementById('tagsHidden').value);
            selectedFiles.forEach(file => fd.append('images[]', file));

            fetch('{{ route('user.marketplace.seller.store') }}', {
                    method: 'POST',
                    body: fd,
                })
                .then(res => {
                    if (res.redirected) {
                        window.location.href = res.url;
                    } else if (res.ok) {
                        window.location.href = '{{ route('user.marketplace.seller.my-products') }}';
                    } else {
                        return res.text().then(html => {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-upload"></i> Simpan Produk';
                            document.getElementById('imageError').classList.remove('hidden');
                            document.getElementById('imageErrorMsg').textContent =
                                'Terjadi kesalahan. Periksa kembali form Anda.';
                        });
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-upload"></i> Simpan Produk';
                    document.getElementById('imageError').classList.remove('hidden');
                    document.getElementById('imageErrorMsg').textContent = 'Koneksi gagal. Coba lagi.';
                });
        }
    </script>

@endsection
