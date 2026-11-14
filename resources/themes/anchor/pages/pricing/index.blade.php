<?php
    use function Laravel\Folio\{middleware, name};
    name('pricing');
?>


<x-layouts.marketing
    :seo="[
        'title' => 'Planes y precios — Yammbo Tv',
        'description' => 'Planes de Yammbo Tv.',
        'type' => 'website',
    ]"
>

    <x-container class="py-16 sm:py-24">
        <x-marketing.sections.pricing />
    </x-container>

</x-layouts.marketing>
