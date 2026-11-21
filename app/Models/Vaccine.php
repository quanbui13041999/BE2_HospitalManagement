<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/* fixed: file nay chi khai bao Vaccine, tranh redeclare cac model da co file rieng */
class Vaccine extends Model
{
    protected $table      = 'vaccines';
    protected $primaryKey = 'vaccine_id';
    public $timestamps    = false;

    protected $fillable = ['vaccine_name','description','manufacturer','doses_required','status'];
    protected $casts    = ['status' => 'boolean', 'doses_required' => 'integer'];



    public function records() { return $this->hasMany(VaccinationRecord::class, 'vaccine_id', 'vaccine_id'); }
}
