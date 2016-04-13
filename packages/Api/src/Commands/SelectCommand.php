<?php namespace Api\Commands;

abstract class SelectCommand extends Command
{
    /**
     * Message to be display if job(command) is completed Successfully
     *
     * @var string
     */
    protected $message = 'Successfully retrieve.';

    /**
     * Default error
     *
     * @var string
     */
    protected $error = 'Unable to retrieve, please try later.';

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
	public function __construct($request = null)
	{
        if (! $request) {
            $request = app('request');
        }

        $this->setRequestAndFields($request);
	}

}
