@php
    // Check if paths are external URLs or local paths
    $isExternalWebp = filter_var($paths['webp'], FILTER_VALIDATE_URL);
    $isExternalJpeg = filter_var($paths['jpeg'], FILTER_VALIDATE_URL);

    $webpSrc = $isExternalWebp ? $paths['webp'] : asset('storage/' . $paths['webp']);
    $jpegSrc = $isExternalJpeg ? $paths['jpeg'] : asset('storage/' . $paths['jpeg']);
@endphp

<picture {{ $attributes->merge(['class' => 'block']) }}>
    <source type="image/webp" srcset="{{ $webpSrc }}">
    <source type="image/jpeg" srcset="{{ $jpegSrc }}">
    <img
        src="{{ $jpegSrc }}"
        alt="{{ $alt }}"
        loading="lazy"
        {{ $attributes->merge(['class' => 'w-full h-auto object-cover']) }}
    >
</picture>
