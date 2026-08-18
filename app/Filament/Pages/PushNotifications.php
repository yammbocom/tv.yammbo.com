<?php

namespace App\Filament\Pages;

use App\Models\PushLog;
use App\Services\FcmPushService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class PushNotifications extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'Push Notifications';

    protected static ?int $navigationSort = 8;

    protected string $view = 'filament.pages.push-notifications';

    public ?array $data = [
        'topic' => 'yammbo_news',
        'title' => '',
        'body' => '',
        'click_url' => '',
        'image_url' => '',
    ];

    public function mount(): void
    {
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('topic')
                    ->label('Topic FCM')
                    ->default('yammbo_news')
                    ->required()
                    ->helperText('Default: yammbo_news (todos los devices que abrieron la APK v29+)'),
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(120),
                Textarea::make('body')
                    ->label('Mensaje')
                    ->required()
                    ->rows(3)
                    ->maxLength(500),
                TextInput::make('click_url')
                    ->label('URL al tocar (opcional)')
                    ->url()
                    ->placeholder('https://tv.yammbo.com/...'),
                TextInput::make('image_url')
                    ->label('Imagen (opcional)')
                    ->url()
                    ->placeholder('https://tv.yammbo.com/images/...'),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(PushLog::query()->latest()->limit(50))
            ->columns([
                TextColumn::make('created_at')->label('Enviado')->dateTime('d/m H:i')->sortable(),
                TextColumn::make('topic')->label('Topic')->badge(),
                TextColumn::make('title')->label('Título')->limit(40)->wrap(),
                TextColumn::make('body')->label('Mensaje')->limit(60)->wrap(),
                IconColumn::make('success')
                    ->label('OK')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('error')->label('Error')->limit(30)->wrap()->color('danger'),
            ])
            ->paginated(false);
    }

    public function sendAction(): Action
    {
        return Action::make('send')
            ->label('Enviar push')
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Confirmar envío')
            ->modalDescription(fn () => 'Vas a enviar el push al topic "'.($this->data['topic'] ?? 'yammbo_news').'". Llegará a TODOS los devices subscritos. ¿Continuar?')
            ->action(function () {
                $state = $this->form->getState();
                $this->send($state);
            });
    }

    private function send(array $state): void
    {
        $service = app(FcmPushService::class);

        try {
            $result = $service->sendToTopic($state['topic'], [
                'title' => $state['title'],
                'body' => $state['body'],
                'click_url' => $state['click_url'] ?: null,
                'image' => $state['image_url'] ?: null,
            ]);
        } catch (\Throwable $e) {
            $result = ['success' => false, 'error' => $e->getMessage(), 'response' => []];
        }

        PushLog::create([
            'user_id' => auth()->id(),
            'topic' => $state['topic'],
            'title' => $state['title'],
            'body' => $state['body'],
            'click_url' => $state['click_url'] ?: null,
            'image_url' => $state['image_url'] ?: null,
            'success' => $result['success'],
            'fcm_message_id' => $result['message_id'] ?? null,
            'error' => $result['error'] ?? null,
            'response' => $result['response'] ?? null,
        ]);

        if ($result['success']) {
            Notification::make()
                ->title('Push enviado')
                ->body('FCM message id: '.($result['message_id'] ?? 'n/a'))
                ->success()
                ->send();

            $this->form->fill([
                'topic' => $state['topic'],
                'title' => '',
                'body' => '',
                'click_url' => '',
                'image_url' => '',
            ]);
        } else {
            Notification::make()
                ->title('Error al enviar push')
                ->body($result['error'] ?? 'Error desconocido')
                ->danger()
                ->send();
        }
    }
}
