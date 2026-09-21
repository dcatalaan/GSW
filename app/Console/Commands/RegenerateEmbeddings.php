<?php

namespace App\Http\Console\Commands;

use App\Services\SemanticSearchService;
use Illuminate\Console\Command;

class RegenerateEmbeddings extends Command
{
    protected $signature = 'embeddings:regenerate';
    protected $description = 'Regenerar embeddings vectoriales para todos los productos activos';

    public function handle(SemanticSearchService $searchService): int
    {
        $this->info('Iniciando regeneración de embeddings...');

        try {
            $count = $searchService->regenerateAllEmbeddings();
            $this->info("Embeddings generados para {$count} productos.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error al regenerar embeddings: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
