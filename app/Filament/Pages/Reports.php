<?php

namespace App\Filament\Pages;

use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Reports';
    protected static ?string $title = 'Generate Reports';
    protected static string $view = 'filament.pages.reports';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('report_type')
                    ->options([
                        'daily' => 'Daily Report',
                        'monthly' => 'Monthly Report',
                        'yearly' => 'Yearly Report',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, $set) => $set('date', null)),
                
                DatePicker::make('date')
                    ->label('Select Date')
                    ->visible(fn ($get) => $get('report_type') === 'daily')
                    ->required(fn ($get) => $get('report_type') === 'daily')
                    ->default(now()),

                Select::make('month')
                    ->options([
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                    ])
                    ->visible(fn ($get) => $get('report_type') === 'monthly')
                    ->required(fn ($get) => $get('report_type') === 'monthly')
                    ->default(now()->month),

                Select::make('year')
                    ->options(array_combine(range(now()->year - 5, now()->year + 1), range(now()->year - 5, now()->year + 1)))
                    ->visible(fn ($get) => in_array($get('report_type'), ['monthly', 'yearly']))
                    ->required(fn ($get) => in_array($get('report_type'), ['monthly', 'yearly']))
                    ->default(now()->year),
            ])
            ->statePath('data');
    }

    public function generateReport()
    {
        $data = $this->form->getState();
        $service = new ReportService();
        $reportData = [];

        try {
            switch ($data['report_type']) {
                case 'daily':
                    $date = Carbon::parse($data['date']);
                    $reportData = $service->getDailyData($date);
                    $filename = 'daily-report-' . $date->format('Y-m-d') . '.pdf';
                    break;
                case 'monthly':
                    $reportData = $service->getMonthlyData($data['month'], $data['year']);
                    $filename = 'monthly-report-' . $data['month'] . '-' . $data['year'] . '.pdf';
                    break;
                case 'yearly':
                    $reportData = $service->getYearlyData($data['year']);
                    $filename = 'yearly-report-' . $data['year'] . '.pdf';
                    break;
            }

            return response()->streamDownload(function () use ($reportData) {
                echo Pdf::loadView('reports.pdf', $reportData)->output();
            }, $filename);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error generating report')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate PDF')
                ->submit('generateReport'),
        ];
    }
}
