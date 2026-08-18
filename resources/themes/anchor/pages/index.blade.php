<?php
    use function Laravel\Folio\{name, render};
    name('home');

    render(function (\Illuminate\View\View $view) {
        if (auth()->check()) {
            return redirect('/app');
        }
        return $view;
    });
?>

<x-layouts.marketing
    :seo="[
        'title'         => __('landing.seo.title'),
        'description'   => __('landing.seo.description'),
        'image'         => asset('/images/yambo-icon.png'),
        'type'          => 'website'
    ]"
    bodyClass="bg-black text-white"
>
    <x-marketing.sections.hero />

    <x-container class="py-16 sm:py-24 border-t border-zinc-800">
        <x-marketing.sections.features />
    </x-container>

    <x-container class="py-16 sm:py-24 border-t border-zinc-800">
        <x-marketing.sections.pricing />
    </x-container>

    <x-container class="py-16 sm:py-24 border-t border-zinc-800">
        <x-marketing.sections.faq />
    </x-container>

    <x-container class="py-16 sm:py-24 border-t border-zinc-800">
        <x-marketing.sections.cta />
    </x-container>
</x-layouts.marketing>
