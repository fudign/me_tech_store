<div
    x-data="{
        show: false,
        message: '',
        type: 'success',

        showToast(msg, toastType = 'success') {
            this.message = msg;
            this.type = toastType;
            this.show = true;

            setTimeout(() => { this.show = false }, 3000);
        }
    }"
    @cart-added.window="showToast($event.detail.message)"
    @cart-removed.window="showToast($event.detail.message)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-4 right-4 z-50 max-w-sm"
    style="display: none;"
>
    <div
        class="rounded-lg shadow-lg px-6 py-4"
        :class="{
            'bg-green-500 text-white': type === 'success',
            'bg-red-500 text-white': type === 'error',
            'bg-blue-500 text-white': type === 'info'
        }"
    >
        <p x-text="message"></p>
    </div>
</div>
