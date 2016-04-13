<?php namespace ApartmentApi\Http\Requests;

use ApartmentApi\Http\Requests\Request;

class FlatBillPaymentRequest extends Request
{
    use \Api\Traits\ApiResponseTrait;

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
            'payment_type'  => 'required',
            'cheque_number' => 'required_if:payment_type,cheque',
		];
	}

    public function response(array $error)
    {
        return response()->make($this->make400Response('Payment type or cheque number is required.'));
    }

}
