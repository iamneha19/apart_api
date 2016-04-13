<?php namespace Api\Commands;

abstract class UpdateCommand extends Command
{
    /**
     * Message to be display if job(command) is completed Successfully
     *
     * @var string
     */
    protected $message = 'Successfully updated.';

    /**
     * Default error
     *
     * @var string
     */
    protected $error = 'Unable to update, please try later.';

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
	public function __construct($id, $request)
	{
        $this->setRequestAndFields($request);

        $this->fields['id'] = $id;
	}

}
