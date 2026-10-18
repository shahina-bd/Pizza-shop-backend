<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    
    protected $fillable = ['order_id', 'pizza_id', 'quantity', 'price', 'extras'];

    protected $casts = [
        'extras' => 'array',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function pizza()
    {
        return $this->belongsTo(Pizza::class, 'pizza_id');
    }
}