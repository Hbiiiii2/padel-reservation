<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TestReport extends Command
{
    protected $signature = 'report:test';
    protected $description = 'Test report generation';

    public function handle()
    {
        $this->info('Testing Report Generation...');

        $service = new ReportService();
        
        // Test Daily Report
        $this->info('Generating Daily Report...');
        $dailyData = $service->getDailyData(now());
        $pdf = Pdf::loadView('reports.pdf', $dailyData);
        $pdf->save(storage_path('app/daily-test.pdf'));
        $this->info('Daily Report saved to storage/app/daily-test.pdf');

        // Test Monthly Report
        $this->info('Generating Monthly Report...');
        $monthlyData = $service->getMonthlyData(now()->month, now()->year);
        $pdf = Pdf::loadView('reports.pdf', $monthlyData);
        $pdf->save(storage_path('app/monthly-test.pdf'));
        $this->info('Monthly Report saved to storage/app/monthly-test.pdf');

        $this->info('Test Complete!');
    }
}
