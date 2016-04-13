<?php

namespace Api\Commands;

abstract class CreateCommand extends Command
{
    /**
     * Message to be display if job(command) is completed Successfully
     *
     * @var string
     */
    protected $message = 'Successfully saved.';

    /**
     * Default error
     *
     * @var string
     */
    protected $error = 'Unable to save, please try later.';

    /**
     * Job(Command) Status
     *
     * @var integer
     */
    protected $statusCode = 200;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($request)
	{
        $this->setRequestAndFields($request);
	}

}
