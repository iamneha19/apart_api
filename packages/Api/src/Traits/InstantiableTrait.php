<?php

namespace Api\Traits;
use Symfony\Component\Process\Exception\LogicException;

/**
 * Those method which are require to make class instance with directly calling instance
 * static method
 *
 * @author Mohammed Mudasir
 */
trait InstantiableTrait
{
    public static function instance()
    {
        if (func_num_args() > 0)
        {
            switch (func_num_args()) {
                case 1:
                    return new static(func_get_arg(0));
                    break;

                case 2:
                    return new static(func_get_arg(0), func_get_arg(1));
                    break;

                case 3:
                    return new static(func_get_arg(0), func_get_arg(1), func_get_arg(2));
                    break;

                case 4:
                    return new static(func_get_arg(0), func_get_arg(1), func_get_arg(2), func_get_arg(3));
                    break;

                case 5:
                    return new static(func_get_arg(0), func_get_arg(1), func_get_arg(2), func_get_arg(3), func_get_arg(4));
                    break;

                default:
                    throw new LogicException("instance will not be able to handle more then 5 dependency at a time");
                    break;
            }
        }

        return new static;
    }
}
