<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMaterial extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_material_id';

    protected $fillable = [
        'product_type_id',
        'material_id',
        'component_type',
        'component_product_type_id',
        'quantity',
        'created_by',
        'updated_at',
    ];

    /**
     * Check if this component is a material
     */
    public function isMaterial(): bool
    {
        return $this->component_type === 'material' || $this->component_type === null;
    }

    /**
     * Check if this component is a product
     */
    public function isProduct(): bool
    {
        return $this->component_type === 'product';
    }
}
