<?php namespace Api\Commands;

abstract class DeleteCommand extends Command
{
    /**
     * Message to be display if job(command) is completed Successfully
     *
     * @var string
     */
    protected $message = 'Successfully deleted.';

    /**
     * Default error
     *
     * @var string
     */
    protected $error = 'Unable to delete, please try later.';

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
	public function __construct($id)
	{
        $this->fields['id'] = $id;
	}

}
