{{-- resources/views/admin/users/_ban-panel.blade.php --}}
{{-- Di-include di dalam admin/users/show.blade.php --}}

@if(!$user->hasRole('admin'))

{{-- ── STATUS BAN AKTIF ─────────────────────────────────────────────── --}}
@if($user->isBanned())
<div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-ban text-red-500 text-xl mt-0.5"></i>
            <div>
                <h3 class="font-bold text-red-800 text-base">
                    Akun Sedang Di-ban
                    @if($user->isBannedPermanently())
                        <span class="ml-2 text-xs bg-red-600 text-white px-2 py-0.5 rounded-full">PERMANEN</span>
                    @else
                        <span class="ml-2 text-xs bg-orange-500 text-white px-2 py-0.5 rounded-full">SEMENTARA</span>
                    @endif
                </h3>
                <p class="text-sm text-red-700 mt-1">{{ $user->ban_reason }}</p>
                <div class="mt-2 text-xs text-red-600 space-y-0.5">
                    <p><i class="fas fa-calendar mr-1"></i>Di-ban sejak: {{ $user->banned_at?->format('d M Y, H:i') }}</p>
                    @if(!$user->isBannedPermanently())
                        <p><i class="fas fa-clock mr-1"></i>Berlaku hingga: {{ $user->banned_until->format('d M Y, H:i') }}</p>
                        <p><i class="fas fa-hourglass-half mr-1"></i>Sisa waktu: {{ $user->banTimeRemaining() }}</p>
                    @endif
                    @if($user->bannedByAdmin)
                        <p><i class="fas fa-user-shield mr-1"></i>Di-ban oleh: {{ $user->bannedByAdmin->name }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tombol Cabut Ban --}}
        <form method="POST" action="{{ route('admin.users.unban', $user) }}"
              onsubmit="return confirm('Yakin ingin mencabut ban akun {{ $user->name }}?')">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="shrink-0 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-unlock mr-1"></i>Cabut Ban
            </button>
        </form>
    </div>
</div>

@else
{{-- ── FORM BAN ─────────────────────────────────────────────────────── --}}
<div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
    <h3 class="font-bold text-gray-900 text-base mb-4 flex items-center gap-2">
        <i class="fas fa-ban text-red-500"></i>
        Berikan Sanksi / Ban Akun
    </h3>

    <form method="POST" action="{{ route('admin.users.ban', $user) }}" id="banForm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            {{-- Tipe Ban --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe Sanksi <span class="text-red-500">*</span></label>
                <select name="ban_type" id="banType" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <option value="">-- Pilih Tipe --</option>
                    <option value="temporary">⏳ Penangguhan Sementara</option>
                    <option value="permanent">🚫 Pemblokiran Permanen</option>
                </select>
                @error('ban_type')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Durasi (hanya muncul jika temporary) --}}
            <div id="durationGroup" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Durasi <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <input type="number" name="ban_duration" min="1" max="365" placeholder="Contoh: 7"
                           class="flex-1 px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <select name="ban_unit"
                            class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <option value="hours">Jam</option>
                        <option value="days" selected>Hari</option>
                        <option value="weeks">Minggu</option>
                    </select>
                </div>
                @error('ban_duration')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Alasan Ban --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Alasan Sanksi <span class="text-red-500">*</span></label>
            <textarea name="ban_reason" rows="3" required maxlength="1000"
                      class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent"
                      placeholder="Jelaskan alasan pemberian sanksi secara detail...">{{ old('ban_reason') }}</textarea>
            @error('ban_reason')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Warning permanen --}}
        <div id="permanentWarning" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-xs text-red-700 flex items-start gap-2">
                <i class="fas fa-exclamation-triangle mt-0.5 shrink-0"></i>
                <span>
                    <strong>Perhatian:</strong> Pemblokiran permanen akan memasukkan email
                    <strong>{{ $user->email }}</strong>
                    @if($user->seller_nik || $user->provider_nik)
                        dan NIK <strong>{{ $user->seller_nik ?? $user->provider_nik }}</strong>
                    @endif
                    ke dalam daftar hitam. User tidak akan bisa mendaftar ulang dengan identitas tersebut.
                    Tindakan ini <strong>tidak dapat dibatalkan dengan mudah</strong>.
                </span>
            </p>
        </div>

        <button type="submit" id="banSubmitBtn"
                class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold text-sm transition-colors"
                onclick="return confirmBan()">
            <i class="fas fa-ban mr-2"></i>Terapkan Sanksi
        </button>
    </form>
</div>
@endif

@endif {{-- end admin check --}}

@push('scripts')
<script>
const banTypeEl = document.getElementById('banType');
const durationGroup = document.getElementById('durationGroup');
const permanentWarning = document.getElementById('permanentWarning');

if (banTypeEl) {
    banTypeEl.addEventListener('change', function () {
        const isPermanent = this.value === 'permanent';
        const isTemporary = this.value === 'temporary';

        durationGroup.classList.toggle('hidden', !isTemporary);
        permanentWarning.classList.toggle('hidden', !isPermanent);

        // Wajibkan durasi input hanya jika temporary
        const durationInput = document.querySelector('[name="ban_duration"]');
        if (durationInput) {
            durationInput.required = isTemporary;
        }
    });
}

function confirmBan() {
    const type = banTypeEl?.value;
    const reason = document.querySelector('[name="ban_reason"]')?.value?.trim();

    if (!type) { alert('Pilih tipe sanksi terlebih dahulu.'); return false; }
    if (!reason) { alert('Alasan sanksi wajib diisi.'); return false; }

    const userName = '{{ $user->name }}';

    if (type === 'permanent') {
        return confirm(
            `⚠️ PERINGATAN!\n\nAnda akan memblokir akun "${userName}" secara PERMANEN.\n` +
            `Email dan NIK-nya akan masuk daftar hitam dan tidak bisa mendaftar ulang.\n\n` +
            `Yakin ingin melanjutkan?`
        );
    } else {
        const duration = document.querySelector('[name="ban_duration"]')?.value;
        const unit = document.querySelector('[name="ban_unit"]')?.value;
        const unitLabel = { hours: 'jam', days: 'hari', weeks: 'minggu' }[unit] || unit;
        return confirm(`Yakin ingin menangguhkan akun "${userName}" selama ${duration} ${unitLabel}?`);
    }
}
</script>
@endpush