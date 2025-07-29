<?php

namespace OpenBackend\AiImageGenerator\Console\Commands;

use Illuminate\Console\Command;
use OpenBackend\AiImageGenerator\Services\AIImageGenerator;

class ListProvidersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ai-image:providers';

    /**
     * The console command description.
     */
    protected $description = 'List all available AI image providers and their status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $generator = app(AIImageGenerator::class);
        $providers = $generator->getProviders();

        $this->info('Available AI Image Providers:');
        $this->line('');

        $tableData = [];

        foreach ($providers as $providerName) {
            try {
                $config = $generator->getProviderConfig($providerName);
                $providerInstance = $generator->getProviderInstance($providerName);
                
                $tableData[] = [
                    'name' => $providerName,
                    'driver' => $config['driver'] ?? $providerName,
                    'status' => $providerInstance->isAvailable() ? '✅ Available' : '❌ Not configured',
                    'models' => implode(', ', $providerInstance->getSupportedModels()),
                ];
                
            } catch (\Exception $e) {
                $tableData[] = [
                    'name' => $providerName,
                    'driver' => 'Unknown',
                    'status' => '❌ Error: ' . $e->getMessage(),
                    'models' => 'N/A',
                ];
            }
        }

        $this->table(
            ['Provider', 'Driver', 'Status', 'Supported Models'],
            $tableData
        );

        return Command::SUCCESS;
    }
}
