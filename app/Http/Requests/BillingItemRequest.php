<?php

namespace ApartmentApi\Http\Requests;

use ApartmentApi\Http\Requests\Request;
use Api\Traits\ApiResponseTrait;

class BillingItemRequest extends Request
{
    use ApiResponseTrait;

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
            'society_id'    => 'required',
            'item_name'     => 'required',
            'fixed_billing_item' => 'required',
            'charge'        => 'required',
            'month'         => 'required_if:fixed-billing-item,no',
		];
	}

    public function messages()
    {
        return [
            'month.required_if' => 'Please select which month you want to add item.',
        ];
    }

    public function response(array $error)
    {
        return response()->make($this->make400Response('Validation Failed.', $error));
    }

}
