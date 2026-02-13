<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CexService;
use App\Models\Category;

class RefreshCexPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cex:refresh {--category-id= : CeX Category ID to refresh} {--limit=10 : Number of items per category} {--all : Sync all Apple and Android phones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh prices from CeX API';

    /**
     * Execute the console command.
     */
    public function handle(CexService $cexService)
    {
        $categoryId = $this->option('category-id');
        $limit = (int) $this->option('limit');
        $syncAll = $this->option('all');

        if ($syncAll) {
            $this->info("Refreshing all Apple and Android phones...");
            $count = $cexService->syncAllPhones($limit);
            $this->info("Synced $count products in total.");
        } elseif ($categoryId) {
            $this->info("Refreshing products for CeX Category ID: $categoryId...");
            $count = $cexService->syncCategoryProducts($categoryId, $limit);
            $this->info("Synced $count products.");
        } else {
            $this->info("No options provided. Syncing default category 1225 (iPhone 11)...");
            $count = $cexService->syncCategoryProducts(1225, $limit);
            $this->info("Synced $count products.");
        }

        return 0;
    }
}
