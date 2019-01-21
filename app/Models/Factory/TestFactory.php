<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\ProductTag;
use App\Models\Orm\TagSeo;
use Illuminate\Support\Facades\DB;

/**
 * Test
 */
class TestFactory extends AbsModelFactory
{
    /**
     * @return array
     * 所有的tagid
     */
    public static function fetchTagsIds()
    {
        $tagIdsAndPositions = ProductTag::select([
            DB::raw('GROUP_CONCAT(DISTINCT tag_id) as tag_id'),
        ])
            ->where('status', '!=', 9)
            ->groupBy('platform_product_id')
            ->get()->toArray();
        //dd($tagIdsAndPositions);
        return $tagIdsAndPositions ? $tagIdsAndPositions : [];
    }
}
