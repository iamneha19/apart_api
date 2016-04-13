<?php

namespace ApartmentApi\Console\ProgressBar;

use Api\Traits\InstantiableTrait;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Bill Counter console progress
 *
 * @author Mohammed Mudasir
 */
class BillCounterProgressBar
{
    use InstantiableTrait;

    public static function start($count)
    {
        $instance = new self;

        return $instance->newBar($count);
    }

    public function newBar($count)
    {
        $progressBar = new ProgressBar($this->createOuput(), $count);
        $progressBar->setBarCharacter('<fg=magenta>=</>');

        return $progressBar;
    }

    public function createOuput()
    {
        $output = new ConsoleOutput;
        $output->setFormatter(new OutputFormatter(true));

        return $output;
    }
}
