<?php

namespace Repository;

/**
 * Selection layer for repository
 *
 * @author Mohammed Mudasir
 */
class Selector
{
    public static function mapSelect(array $selections, $model, $relation)
    {
        $selections = count($selections) > 0 ? $selections : ['*'];

        return $model->with([$relation => function($q) use ($selections)
        {
            call_user_func_array([$q, 'addSelect'], $selections);
        }]);
    }
}
