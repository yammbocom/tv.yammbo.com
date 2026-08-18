<x-filament-panels::page>
    <x-filament::section class="w-full">
        <x-slot name="heading">Enviar notificación push</x-slot>
        <x-slot name="description">Envía una push a todos los devices YamboTV (APK v29+) subscritos al topic.</x-slot>

        <form wire:submit.prevent="sendAction" class="space-y-4">
            {{ $this->form }}

            <div class="flex justify-end pt-4">
                {{ ($this->sendAction)(['size' => 'lg']) }}
            </div>
        </form>
    </x-filament::section>

    <x-filament::section class="w-full">
        <x-slot name="heading">Historial (últimos 50)</x-slot>
        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
