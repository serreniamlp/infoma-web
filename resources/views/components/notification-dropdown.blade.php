{{-- resources/views/components/notification-dropdown.blade.php --}}

<div class="relative" x-data="notificationBell()" x-init="init()">

    {{-- Bell button --}}
    <button @click="toggle()"
            class="relative p-2 text-gray-500 hover:text-blue-600 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none">
        <i class="fas fa-bell text-lg"></i>
        {{-- Badge --}}
        <span x-show="unreadCount > 0"
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 leading-none">
        </span>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden">

        {{-- Header --}}
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 text-sm">Notifikasi</h3>
            <button x-show="unreadCount > 0"
                    @click="markAllRead()"
                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                Tandai semua dibaca
            </button>
        </div>

        {{-- List notifikasi --}}
        <div class="max-h-96 overflow-y-auto divide-y divide-gray-50">

            {{-- Loading --}}
            <div x-show="loading" class="px-4 py-6 text-center text-gray-400 text-sm">
                <i class="fas fa-spinner fa-spin mr-2"></i>Memuat...
            </div>

            {{-- Empty state --}}
            <div x-show="!loading && notifications.length === 0"
                 class="px-4 py-10 text-center">
                <i class="fas fa-bell-slash text-gray-300 text-3xl mb-2 block"></i>
                <p class="text-sm text-gray-400">Belum ada notifikasi</p>
            </div>

            {{-- Items --}}
            <template x-for="notif in notifications" :key="notif.id">
                <a :href="'/notifications/' + notif.id + '/read'"
                   class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors block"
                   :class="notif.is_unread ? 'bg-blue-50/40' : ''">

                    {{-- Icon --}}
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center"
                             :class="{
                                 'bg-blue-100 text-blue-600':   notif.color === 'blue',
                                 'bg-green-100 text-green-600': notif.color === 'green',
                                 'bg-red-100 text-red-600':     notif.color === 'red',
                                 'bg-indigo-100 text-indigo-600': notif.color === 'indigo',
                                 'bg-gray-100 text-gray-600':   notif.color === 'gray',
                             }">
                            <i class="text-xs" :class="'fas ' + notif.icon"></i>
                        </div>
                    </div>

                    {{-- Konten --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 leading-snug"
                           :class="notif.is_unread ? 'font-medium' : 'font-normal'"
                           x-text="notif.message">
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="notif.time"></p>
                    </div>

                    {{-- Dot unread --}}
                    <div x-show="notif.is_unread"
                         class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-1.5">
                    </div>
                </a>
            </template>
        </div>

        {{-- Footer --}}
        <div x-show="!loading && notifications.length > 0"
             class="px-4 py-2 border-t border-gray-100 text-center">
            <span class="text-xs text-gray-400">Menampilkan 15 notifikasi terbaru</span>
        </div>
    </div>
</div>

<script>
function notificationBell() {
    return {
        open:          false,
        loading:       false,
        unreadCount:   0,
        notifications: [],
        pollingTimer:  null,

        init() {
            this.fetchCount();
            // Polling setiap 30 detik
            this.pollingTimer = setInterval(() => this.fetchCount(), 30000);
        },

        toggle() {
            this.open = !this.open;
            if (this.open && this.notifications.length === 0) {
                this.fetchList();
            }
        },

        async fetchCount() {
            try {
                const res  = await fetch('/notifications/count', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.unreadCount = data.count;
            } catch (e) {
                // Silent fail — jangan ganggu user
            }
        },

        async fetchList() {
            this.loading = true;
            try {
                const res  = await fetch('/notifications/list', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.notifications = data.notifications;
            } catch (e) {
                this.notifications = [];
            } finally {
                this.loading = false;
            }
        },

        async markAllRead() {
            try {
                await fetch('/notifications/read-all', {
                    method:  'PATCH',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });
                // Update UI langsung tanpa refetch
                this.unreadCount = 0;
                this.notifications = this.notifications.map(n => ({ ...n, is_unread: false }));
            } catch (e) {}
        },

        destroy() {
            clearInterval(this.pollingTimer);
        }
    }
}
</script>