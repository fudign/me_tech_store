<div
    x-data="{
        show: false,
        message: '',
        title: '',
        type: 'success',
        details: null,
        actionText: '',
        actionUrl: '',
        duration: 5000,

        showToast(msg, toastType = 'success', options = {}) {
            this.message = msg;
            this.type = toastType;
            this.title = options.title || '';
            this.details = options.details || null;
            this.actionText = options.actionText || '';
            this.actionUrl = options.actionUrl || '';
            this.duration = options.duration || 5000;
            this.show = true;

            setTimeout(() => { this.show = false }, this.duration);
        },

        getIcon() {
            const icons = {
                'success': 'solar:check-circle-bold',
                'error': 'solar:close-circle-bold',
                'info': 'solar:info-circle-bold',
                'warning': 'solar:danger-circle-bold',
                'order': 'solar:bag-check-bold'
            };
            return icons[this.type] || icons['info'];
        }
    }"
    @cart-added.window="showToast($event.detail.message, 'success')"
    @cart-removed.window="showToast($event.detail.message, 'info')"
    @order-success.window="showToast($event.detail.message, 'order', $event.detail)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
    class="fixed bottom-4 right-4 z-[100] max-w-sm w-full mx-4 sm:mx-0"
    style="display: none;"
>
    <div
        class="rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm"
        :class="{
            'bg-white border-2 border-green-500': type === 'success' || type === 'order',
            'bg-white border-2 border-red-500': type === 'error',
            'bg-white border-2 border-blue-500': type === 'info',
            'bg-white border-2 border-yellow-500': type === 'warning'
        }"
    >
        <!-- Progress Bar -->
        <div class="h-1 bg-gray-100 relative overflow-hidden">
            <div
                class="h-full absolute top-0 left-0 transition-all"
                :class="{
                    'bg-green-500': type === 'success' || type === 'order',
                    'bg-red-500': type === 'error',
                    'bg-blue-500': type === 'info',
                    'bg-yellow-500': type === 'warning'
                }"
                :style="`animation: progress ${duration}ms linear forwards;`"
            ></div>
        </div>

        <div class="p-4">
            <div class="flex items-start gap-3">
                <!-- Icon -->
                <div
                    class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                    :class="{
                        'bg-green-100': type === 'success' || type === 'order',
                        'bg-red-100': type === 'error',
                        'bg-blue-100': type === 'info',
                        'bg-yellow-100': type === 'warning'
                    }"
                >
                    <iconify-icon
                        :icon="getIcon()"
                        width="24"
                        :class="{
                            'text-green-600': type === 'success' || type === 'order',
                            'text-red-600': type === 'error',
                            'text-blue-600': type === 'info',
                            'text-yellow-600': type === 'warning'
                        }"
                    ></iconify-icon>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <h4
                        x-show="title"
                        x-text="title"
                        class="font-semibold text-gray-900 text-sm mb-1"
                    ></h4>
                    <p
                        x-text="message"
                        class="text-sm text-gray-700"
                        :class="{ 'font-medium': !title }"
                    ></p>

                    <!-- Order Details -->
                    <div x-show="details" class="mt-2 space-y-1 text-xs text-gray-600">
                        <div x-show="details?.orderNumber" class="flex items-center gap-1.5">
                            <iconify-icon icon="solar:document-text-linear" width="14"></iconify-icon>
                            <span>Заказ: <strong x-text="details?.orderNumber"></strong></span>
                        </div>
                        <div x-show="details?.total" class="flex items-center gap-1.5">
                            <iconify-icon icon="solar:wallet-linear" width="14"></iconify-icon>
                            <span>Сумма: <strong x-text="details?.total"></strong></span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <a
                        x-show="actionText && actionUrl"
                        :href="actionUrl"
                        class="inline-block mt-3 text-sm font-medium underline hover:no-underline transition-all"
                        :class="{
                            'text-green-700': type === 'success' || type === 'order',
                            'text-red-700': type === 'error',
                            'text-blue-700': type === 'info',
                            'text-yellow-700': type === 'warning'
                        }"
                        x-text="actionText"
                    ></a>
                </div>

                <!-- Close Button -->
                <button
                    @click="show = false"
                    class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes progress {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}
</style>
