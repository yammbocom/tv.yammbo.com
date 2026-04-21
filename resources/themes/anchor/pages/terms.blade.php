<?php
    use function Laravel\Folio\name;
    name('terms');
?>

<x-layouts.marketing
    :seo="[
        'title' => 'Terms of Service — Yammbo Tv',
        'description' => 'Yammbo Tv Terms of Service.',
        'type' => 'website',
    ]"
>
    <x-container class="max-w-3xl py-12 sm:py-24 prose prose-lg">
        <h1>Terms of Service</h1>
        <p class="text-zinc-500"><em>Last updated: {{ date('F j, Y') }}</em></p>

        <h2>Placeholder</h2>
        <p>
            These Terms of Service govern your access to and use of Yammbo Tv. By using Yammbo Tv
            you agree to these terms. Full legal text pending review.
        </p>

        <h2>Contact</h2>
        <p>Questions? Email <a href="mailto:support@yammbo.com">support@yammbo.com</a>.</p>
    </x-container>
</x-layouts.marketing>
