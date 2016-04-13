<?php

namespace ApartmentApi\Presenters;

use Illuminate\Support\Collection;
use ApartmentApi\Models\Block;

/**
 * Block presenter
 *
 * @author Mohammed Mudasir
 */
class BlockPresenter
{
    /**
     * Collection of responses which will be used by jQuery select2 plugin
     *
     * @param  Collection $blocks
     * @return [array]
     */
    public static function select2Responses(Collection $blocks)
    {
        $response = [];

        // Creating response with id and text from blocks
        foreach ($blocks as $block)
        {
            array_push($response, self::select2Response($block));
        }

        return $response;
    }

    /**
     * Response which will be use by jQuery select2 plugin
     *
     * @param  Block  $block
     * @return [array]
     */
    public static function select2Response(Block $block)
    {
        return [
                'id'   => $block->id,
                'text' => $block->societyWithBlock
            ];
    }
}
