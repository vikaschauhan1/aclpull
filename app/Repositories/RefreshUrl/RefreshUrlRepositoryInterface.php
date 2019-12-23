<?php

namespace App\Repositories\RefreshUrl;

interface RefreshUrlRepositoryInterface
{
    public function refreshUrl();

    public function storeSeriesMasterData();

    public function storeOperatorMappingData();

    public function storeOperatorMasterData();
    
    public function flushallRedisdata();
}
