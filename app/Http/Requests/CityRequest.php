<?php namespace ApartmentApi\Http\Requests;

use ApartmentApi\Http\Requests\Request;
use Api\Presentor;
use Illuminate\Http\Response;

class CityRequest extends Request
{
    protected $presentor;

    protected $redirect = false;

    public function __construct(Presentor $presentor)
    {
        $this->presentor = $presentor;
    }

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
            'state_id' => 'required|integer',
			'name'     => 'required'
		];
	}

    /**
     * When validation failed then throw this response
     *
     * @return [type] [description]
     */
    public function response(array $errors)
    {
        return new Response($this
                    ->presentor
                    ->make400Response('Name and state id is required and state id should be integer.')
        );
    }

}
