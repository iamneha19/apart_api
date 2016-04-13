<?php

namespace ApartmentApi\Commands\User;

use ApartmentApi\Commands\Command;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Database\QueryException;
use ApartmentApi\User;

class IsFlatAttached extends Command implements SelfHandling
{
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
     public function handle(User $user)
     {
         $isFlatAttached = false;

         $userSocieties = $this->getUserSocieties($user);

         if (is_bool($userSocieties)) {
             return $userSocieties;
         }

         foreach ($userSocieties as $userSociety) {
             if ($userSociety->flat_id) {
                 $isFlatAttached = true;
                 break;
             }
         }

         return $isFlatAttached;
     }

     public function getUserSocieties(User $user)
     {
         $user = $user->with(['userSocieties' => function($q) {
                         $q->select('id', 'flat_id', 'user_id');
                     }, 'roles'])
                     ->select('id')
                     ->find($this->userId);

         $userSocieties = $user ? $user->userSocieties: false;

         // Check user is admin or not
         foreach ($user->roles as $role) {
             if ($role->role_name == 'Admin') {
                 $userSocieties = true;

                 break;
             }
             if (strtolower($role->role_name) == 'chairman'  || strtolower($role->role_name) == 'chairperson') {
                 $userSocieties = true;

                 break;
             }
         }
         
         return $userSocieties;
     }

}
