<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class factura extends Model  {

	protected $connection = 'mysql';

	protected $table = 'cabfactu';
	protected $primaryKey = 'id';
	const LINES_PER_PAGE = 47;


	public function __construct() {
		parent::__construct();
		
	}



	public function getNextNumFac($ejercicio) {
		$numFac = \DB::table($this->table)->where("ejefac", $ejercicio)->max("numfac");
		return $numFac+1;
	}
	

}
