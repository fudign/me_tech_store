<picture {{ $attributes->merge(['class' => 'block']) }}>
    <source type="image/webp" srcset="{{ asset('storage/' . $paths['webp']) }}">
    <source type="image/jpeg" srcset="{{ asset('storage/' . $paths['jpeg']) }}">
    <img
        src="{{ asset('storage/' . $paths['jpeg']) }}"
        alt="{{ $alt }}"
        loading="lazy"
        {{ $attributes->merge(['class' => 'w-full h-auto object-cover']) }}
    >
</picture>
