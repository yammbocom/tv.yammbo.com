<?php
    use function Laravel\Folio\name;
    name('help');
?>

<x-layouts.marketing
    :seo="[
        'title' => 'Help &amp; Support — Yammbo Tv',
        'description' => 'Yammbo Tv help, FAQ, and support channels.',
        'type' => 'website',
    ]"
>
    <x-container class="max-w-3xl py-12 sm:py-24 prose prose-lg">
        <h1>Help &amp; Support</h1>

        <p>
            Need help with Yammbo Tv? Email us at
            <a href="mailto:support@yammbo.com">support@yammbo.com</a>.
        </p>

        <h2>Common topics</h2>
        <ul>
            <li>Account &amp; billing — visit <a href="/settings">Settings</a></li>
            <li>Installing the Yammbo addon — add <code>https://tv.yammbo.com/manifest.json</code> in your Stremio client</li>
            <li>Report a bug — email support with steps to reproduce</li>
        </ul>
    </x-container>
</x-layouts.marketing>
