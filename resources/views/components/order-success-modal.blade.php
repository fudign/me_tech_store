<div
    x-data="{
        show: false,
        orderData: null,

        showModal(data) {
            this.orderData = data;
            this.show = true;
            document.body.style.overflow = 'hidden';

            // Play success sound (optional)
            // this.playSuccessSound();
        },

        closeModal() {
            this.show = false;
            document.body.style.overflow = '';
        },

        playSuccessSound() {
            // Optional: Add a subtle success sound
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZSA0PVqzn77BdGAg+ltzyxngpBSl+zPLaizsIGGS57OihUBALTKXh8bllHAU2jdXzzn0vBSF1xe/glEILElyx6OyrWBUIQ5zd8sFuJAUuhM/z1YU5BxxqvO/mnEoPDlOq5O+zYBoGPJPY88p5KwUme8rx3I4+CRZiturqpVIRC0mi4PKyZBwFM4/T88yAMQYeb8Dv45ZGDQ9Sp+Twsmccj==');
            audio.volume = 0.3;
            audio.play().catch(() => {});
        }
    }"
    @order-modal.window="showModal($event.detail)"
    @keydown.escape.window="closeModal()"
    x-show="show"
    class="fixed inset-0 z-[200] overflow-y-auto"
    style="display: none;"
>
    <!-- Backdrop -->
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeModal()"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm"
    ></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
            @click.stop
        >
            <!-- Confetti Animation Container -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="confetti-container"></div>
            </div>

            <!-- Close Button -->
            <button
                @click="closeModal()"
                class="absolute top-4 right-4 z-10 text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-full"
            >
                <iconify-icon icon="solar:close-circle-linear" width="24"></iconify-icon>
            </button>

            <!-- Success Icon -->
            <div class="pt-8 pb-6 text-center bg-gradient-to-b from-green-50 to-white">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4 animate-bounce-in">
                    <iconify-icon icon="solar:bag-check-bold" width="48" class="text-green-600 animate-scale-in"></iconify-icon>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">🎉 Заказ оформлен!</h2>
                <p class="text-gray-600 text-sm px-4">Спасибо за покупку в Mi Tech</p>
            </div>

            <!-- Order Details -->
            <div class="px-6 py-5 space-y-4">
                <!-- Order Number -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs text-gray-500 uppercase font-semibold tracking-wider">Номер заказа</span>
                        <span class="text-lg font-bold text-gray-900" x-text="orderData?.orderNumber"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 uppercase font-semibold tracking-wider">Сумма к оплате</span>
                        <span class="text-xl font-bold text-brand-600" x-text="orderData?.total"></span>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <iconify-icon icon="solar:phone-calling-bold" width="18" class="text-blue-600"></iconify-icon>
                        </div>
                        <div>
                            <h3 class="font-semibold text-blue-900 text-sm mb-1">Что дальше?</h3>
                            <p class="text-blue-800 text-xs leading-relaxed">
                                Наш менеджер свяжется с вами в течение <strong>15-60 минут</strong> для подтверждения заказа.
                                Пожалуйста, держите телефон под рукой.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2 text-gray-600">
                        <iconify-icon icon="solar:user-linear" width="16"></iconify-icon>
                        <span x-text="orderData?.customerName"></span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <iconify-icon icon="solar:phone-linear" width="16"></iconify-icon>
                        <span x-text="orderData?.customerPhone"></span>
                    </div>
                    <div class="flex items-start gap-2 text-gray-600">
                        <iconify-icon icon="solar:map-point-linear" width="16" class="mt-0.5"></iconify-icon>
                        <span x-text="orderData?.customerAddress"></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 pb-6 pt-2 space-y-3">
                <a
                    :href="orderData?.orderUrl"
                    class="block w-full text-center bg-brand-500 hover:bg-brand-600 text-white font-semibold py-3 rounded-xl transition-colors shadow-lg shadow-brand-500/30"
                >
                    Посмотреть детали заказа
                </a>
                <button
                    @click="closeModal(); window.location.href = '{{ route('products.index') }}'"
                    class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 rounded-xl transition-colors"
                >
                    Продолжить покупки
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes bounce-in {
    0% {
        transform: scale(0) rotate(-180deg);
        opacity: 0;
    }
    50% {
        transform: scale(1.2) rotate(10deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

@keyframes scale-in {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.15);
    }
    100% {
        transform: scale(1);
    }
}

.animate-bounce-in {
    animation: bounce-in 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.animate-scale-in {
    animation: scale-in 0.6s ease-out 0.2s backwards;
}

/* Optional: Confetti effect styles */
.confetti-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
</style>
