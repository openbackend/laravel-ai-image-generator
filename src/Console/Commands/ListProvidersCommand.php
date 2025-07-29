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
    public function handle(AIImageGenerator $generator): int
    {
        $this->info('Available AI Image Providers:');
        $this->newLine();

        $providers = $generator->getProviders();
        $tableData = [];

        foreach ($providers as $providerName) {
            try {
                $provider = $generator->provider($providerName);
                $config = $generator->getProviderConfig($providerName);
                
                $tableData[] = [
                    'name' => $providerName,
                    'driver' => $config['driver'] ?? $providerName,
                    'status' => $provider->isAvailable() ? '✅ Available' : '❌ Not configured',
                    'models' => implode(', ', $provider->getSupportedModels()),
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
