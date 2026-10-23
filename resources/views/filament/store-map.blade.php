@if (is_numeric($latitude ?? null) && is_numeric($longitude ?? null))
    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
        <iframe
            src="https://maps.google.com/maps?q={{ $latitude }},{{ $longitude }}&z=16&output=embed"
            width="100%"
            height="320"
            style="border:0;"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Ubicación de la tienda"
        ></iframe>
    </div>
    <p class="mt-1 text-xs">
        <a
            href="https://www.google.com/maps/search/?api=1&query={{ $latitude }},{{ $longitude }}"
            target="_blank"
            rel="noopener"
            class="underline"
        >
            Abrir en Google Maps
        </a>
    </p>
@else
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Ingresa la latitud y longitud para previsualizar la ubicación en Google Maps.
    </p>
@endif
