<?php
    use function Laravel\Folio\name;
    name('privacy');
?>

<x-layouts.marketing
    :seo="[
        'title' => 'Privacy Policy — Yammbo Tv',
        'description' => 'Yammbo Tv Privacy Policy.',
        'type' => 'website',
    ]"
>
    <x-container class="max-w-3xl py-12 sm:py-24 prose prose-lg">
        <h1>Privacy Policy</h1>
        <p class="text-zinc-500"><em>Last updated: {{ date('F j, Y') }}</em></p>

        <h2>Placeholder</h2>
        <p>
            This Privacy Policy describes how Yammbo Tv collects, uses, and shares your personal
            information. Full legal text pending review.
        </p>

        <h2>Contact</h2>
        <p>Questions? Email <a href="mailto:support@yammbo.com">support@yammbo.com</a>.</p>
    </x-container>
</x-layouts.marketing>
