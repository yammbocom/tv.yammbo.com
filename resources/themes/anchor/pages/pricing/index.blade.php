<?php
    use function Laravel\Folio\{middleware, name};
    name('pricing');
?>


<x-layouts.marketing
    :seo="[
        'title' => 'Planes y precios — Yammbo Tv',
        'description' => 'Elige tu plan de Yammbo Tv: películas, series y TV en vivo en móvil y Smart TV, desde 1 hasta 3 dispositivos. 7 días de prueba y cancelas cuando quieras.',
        'type' => 'website',
    ]"
>

    <x-container class="py-16 sm:py-24">
        <x-marketing.sections.pricing />
    </x-container>

</x-layouts.marketing>
