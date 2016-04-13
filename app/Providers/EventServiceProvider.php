<?php namespace ApartmentApi\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		'ApartmentApi\Events\SocietyWasRegistered' => [
			'ApartmentApi\Listeners\EmailSocietyAndUserDetails'
		],
        'ApartmentApi\Events\SocietyJoinRequest' => [
			'ApartmentApi\Listeners\EmailUserOnJoinRequest'
		],
                'ApartmentApi\Events\UserWasCreated' => [
			'ApartmentApi\Listeners\EmailUserDetails'
		],
		'ApartmentApi\Events\NewHelpdeskTicketWasLodged' => [
			'ApartmentApi\Listeners\EmailTicketDetails'
		],
                'ApartmentApi\Events\TaskWasCreated' => [
			'ApartmentApi\Listeners\EmailTaskDetails'
		],
                 'ApartmentApi\Events\TaskWasUpdated' => [
			'ApartmentApi\Listeners\EmailTaskUpdateDetails'
		],
                'ApartmentApi\Events\MeetingWasCreated' => [
			'ApartmentApi\Listeners\EmailMeetingDetails'
		],
                'ApartmentApi\Events\ResetPassword' => [
			'ApartmentApi\Listeners\RequestNewPassword'
		],
        'ApartmentApi\Events\AssociateMemberWasAdded' => [
			'ApartmentApi\Listeners\EmailAssociateMemberDetails'
		],
        'ApartmentApi\Events\BillReceipt' => [
            'ApartmentApi\Listeners\BillReceiptDetails'
        ],
        'ApartmentApi\Events\BuildingApprovedStatus' => [
            'ApartmentApi\Listeners\BuildingApprovalDetails'
        ],
		'ApartmentApi\Events\SocietyConfigUploaded' => [
			'ApartmentApi\Listeners\EmailSocietyConfigUploaded'
		],
	];

	/**
	 * Register any other events for your application.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
	 * @return void
	 */
	public function boot(DispatcherContract $events)
	{
		parent::boot($events);

		//
	}

}
