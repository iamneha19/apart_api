<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment';

    protected $fillable = ['flat_bill_id', 'payment_type', 'cheque_number'];

    public function flatBill()
    {
        return $this->belongsTo('ApartmentApi\Models\FlatBill');
    }
}
