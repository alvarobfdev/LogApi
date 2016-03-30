<?php namespace App\Http\Controllers;

use App\Http\Controllers\controller;
use App\Factura;
use Carbon\Carbon;
use Chumper\Zipper\Zipper;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\View;
use Khill\Lavacharts\Formats\DateFormat;
use Khill\Lavacharts\Formats\Format;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Validator, Input, Redirect ;


class FacturaController extends Controller {

	protected $layout = "layouts.main";
	protected $data = array();	
	public $module = 'factura';
	static $per_page	= '10';
	public static $GROUPS_DESCRIPTIONS = array(
		"A" 	=> "SERVICIOS DE ALMACENAJE",
		"ME"	=> "MANIPULACION DE ENTRADAS",
		"CP"	=> "CONFECCION DE PEDIDOS"
	);

	public function __construct()
	{


		$this->beforeFilter('csrf', array('on'=>'post'));
		$this->model = new Factura();
		$this->modelview = new  \App\Models\Lineasfactura();
		$this->info = $this->model->makeInfo( $this->module);
		$this->access = $this->model->validAccess($this->info['id']);
	
		$this->data = array(
			'pageTitle'	=> 	$this->info['title'],
			'pageNote'	=>  $this->info['note'],
			'pageModule'=> 'factura',
			'return'	=> self::returnUrl()
			
		);
		$this->data['subgrid']	= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'][0] : array()); 
	}

	public function getIndex( Request $request )
	{




		if($this->access['is_view'] ==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');

		$sort = (!is_null($request->input('sort')) ? $request->input('sort') : 'fecfac');
		$order = (!is_null($request->input('order')) ? $request->input('order') : 'desc');
		// End Filter sort and order for query 
		// Filter Search for query		
		$filter = (!is_null($request->input('search')) ? $this->buildSearch() : '');
		$this->createCharts($filter);
		$this->generatePieChart($filter, 2015);
		$this->generatePieChart($filter, 2014);

		
		$page = $request->input('page', 1);
		$params = array(
			'page'		=> $page ,
			'limit'		=> (!is_null($request->input('rows')) ? filter_var($request->input('rows'),FILTER_VALIDATE_INT) : static::$per_page ) ,
			'sort'		=> $sort ,
			'order'		=> $order,
			'params'	=> $filter,
			'global'	=> (isset($this->access['is_global']) ? $this->access['is_global'] : 0 )
		);
		// Get Query 
		$results = $this->model->getRows( $params );		
		
		// Build pagination setting
		$page = $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false ? $page : 1;	
		$pagination = new Paginator($results['rows'], $results['total'], $params['limit']);	
		$pagination->setPath('factura');
		
		$this->data['rowData']		= $results['rows'];
		foreach($this->data['rowData'] as &$row) {
			if(!$row->reducida)
				$row->reducida = "Detallada";
			else $row->reducida = "Reducida";
		}
		// Build Pagination 
		$this->data['pagination']	= $pagination;
		// Build pager number and append current param GET
		$this->data['pager'] 		= $this->injectPaginate();	
		// Row grid Number 
		$this->data['i']			= ($page * $params['limit'])- $params['limit']; 
		// Grid Configuration 
		$this->data['tableGrid'] 	= $this->info['config']['grid'];
		$this->data['tableForm'] 	= $this->info['config']['forms'];
		$this->data['colspan'] 		= \SiteHelpers::viewColSpan($this->info['config']['grid']);		
		// Group users permission
		$this->data['access']		= $this->access;
		// Detail from master if any
		
		// Master detail link if any 
		$this->data['subgrid']	= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'] : array()); 
		// Render into template
		return view('factura.index',$this->data);
	}	



	function getUpdate(Request $request, $id = null)
	{
	
		if($id =='')
		{
			if($this->access['is_add'] ==0 )
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
		}	
		
		if($id !='')
		{
			if($this->access['is_edit'] ==0 )
			return Redirect::to('dashboard')->with('messagetext',\Lang::get('core.note_restric'))->with('msgstatus','error');
		}				
				
		$row = $this->model->find($id);
		if($row)
		{
			$this->data['row'] =  $row;
		} else {
			$this->data['row'] = $this->model->getColumnTable('cabfactu');
			$this->data['row']['numfac'] = $this->model->getNextNumFac(Carbon::now()->year);
			$this->data['row']['ejefac'] = Carbon::now()->year;
			$this->data['row']['fecfac'] = Carbon::now()->toDateString();
		}
		$this->data['fields'] 		=  \SiteHelpers::fieldLang($this->info['config']['forms']);
		$this->data['subgrid'] = $this->detailview($this->modelview ,  $this->data['subgrid'] ,$id );
		$this->data['id'] = $id;
		return view('factura.form',$this->data);
	}	

	public function getShow( $id = null)
	{
		if($this->access['is_detail'] ==0) 
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
					
		$row = $this->model->getRow($id);
		if($row)
		{
			$this->data['row'] =  $row;
		} else {
			$this->data['row'] = $this->model->getColumnTable('cabfactu');
		}
		$this->data['fields'] 		=  \SiteHelpers::fieldLang($this->info['config']['grid']);
		$this->data['subgrid'] = $this->detailview($this->modelview ,  $this->data['subgrid'] ,$id );
		$this->data['id'] = $id;
		$this->data['access']		= $this->access;
		return view('factura.view',$this->data);	
	}	

	function postSave( Request $request)
	{
		
		$rules = $this->validateForm();
		$validator = Validator::make($request->all(), $rules);	
		if ($validator->passes()) {
			$data = $this->validatePost('tb_factura');
				
			$id = $this->model->insertRow($data , $request->input('id'));
			$this->detailviewsave( $this->modelview , $request->all() , $this->data['subgrid'] , $id) ;
			if(!is_null($request->input('apply')))
			{
				$return = 'factura/update/'.$id.'?return='.self::returnUrl();
			} else {
				$return = 'factura?return='.self::returnUrl();
			}

			// Insert logs into database
			if($request->input('id') =='')
			{
				\SiteHelpers::auditTrail( $request , 'New Data with ID '.$id.' Has been Inserted !');
			} else {
				\SiteHelpers::auditTrail($request ,'Data with ID '.$id.' Has been Updated !');
			}

			return Redirect::to($return)->with('messagetext',\Lang::get('core.note_success'))->with('msgstatus','success');
			
		} else {

			return Redirect::to('factura/update/'.$id)->with('messagetext',\Lang::get('core.note_error'))->with('msgstatus','error')
			->withErrors($validator)->withInput();
		}	
	
	}	

	public function postDelete( Request $request)
	{
		
		if($this->access['is_remove']==0)
			return Redirect::to('dashboard')
				->with('messagetext', \Lang::get('core.note_restric'))->with('msgstatus','error');
		//delete multipe rows
		if(count($request->input('ids')) >=1 )
		{
			$this->model->destroy($request->input('ids'));
			\DB::table('linfactu')->whereIn('cabfactu_id',$request->input('ids'))->delete();
			\SiteHelpers::auditTrail( $request , "ID : ".implode(",",$request->input('ids'))."  , Has Been Removed Successfull");
			// redirect
			return Redirect::to('factura')
        		->with('messagetext', \Lang::get('core.note_success_delete'))->with('msgstatus','success'); 
	
		} else {
			return Redirect::to('factura')
        		->with('messagetext','No Item Deleted')->with('msgstatus','error');				
		}

	}

	public function postExportPdf(Request $request)
	{
		$ids = null;
		if($request->exists("checkall")) {
			$filter = (!is_null($request->input('search')) ? $this->buildSearch() : '');
			$args["params"] = $filter;
			$rows = $this->model->getRows($args);
			foreach($rows["rows"] as $row) {
				$ids[] = $row->id;
			}
		}

		if(!$ids)
			$ids = $request->input('ids');
		if (count($ids) > 0) {
			$uid = uniqid();
			$zip = new \Chumper\Zipper\Zipper;
			$zip->make(storage_path() ."/app/tmp/$uid/facturas.zip");
			foreach ($ids as $id) {
				$view = $this->getHtmlContent($id);
				$nombreFact = "factura-{$this->data['row']->serfac}-{$this->data['row']->ejefac}-{$this->data['row']->numfac}.pdf";
				$pdfContents = \PDF::loadHTML($view)->setPaper('a4')->setOption('margin-right', 0)->setOption('margin-bottom', 0)->setOption('margin-left', 0)->setOption('margin-top', 0)->output();
				$zip->addString($nombreFact, $pdfContents);
				$this->data['subgrid']	= (isset($this->info['config']['subgrid']) ? $this->info['config']['subgrid'][0] : array());
			}
			$zip->close();
			$response = \Response::make(file_get_contents(storage_path() ."/app/tmp/$uid/facturas.zip"));
			$size = \Storage::drive("local")->size("tmp/$uid/facturas.zip");
			\Storage::drive("local")->deleteDirectory("tmp/$uid");
			$response->header('Content-Disposition', 'attachment; filename="facturas.zip"');
			$response->header('Content-Length', '$size');
			return $response;
		}

	}


	public function getPdfExport(Request $request, $id) {
		$view = $this->getHtmlContent($id);
		$nombreFact = "factura-{$this->data['row']->serfac}-{$this->data['row']->ejefac}-{$this->data['row']->numfac}.pdf";
		return \PDF::loadHTML($view)->setPaper('a4')->setOption('margin-right', 0)->setOption('margin-bottom', 0)->setOption('margin-left', 0)->setOption('margin-top', 0)->download($nombreFact);
	}

	public function getPdfView(Request $request, $id) {
		$view = $this->getHtmlContent($id);
		$nombreFact = "factura-{$this->data['row']->serfac}-{$this->data['row']->ejefac}-{$this->data['row']->numfac}.pdf";
		$output =  \PDF::loadHTML($view)->setPaper('a4')->setOption('margin-right', 0)->setOption('margin-bottom', 0)->setOption('margin-left', 0)->setOption('margin-top', 0)->output();

		return Response::make($output, 200, [
				'Content-Type' => 'application/pdf',
				'Content-Disposition' => 'inline; '.$nombreFact,
		]);
	}

	/**
	 * @param $id
	 * @return mixed|string
	 */
	public function getHtmlContent($id)
	{
		$row = $this->model->getRow($id);
		if ($row) {
			$this->data['row'] = $row;
		} else {
			$this->data['row'] = $this->model->getColumnTable('cabfactu');
		}


		$this->data['fields'] = \SiteHelpers::fieldLang($this->info['config']['grid']);
		$this->data['subgrid'] = $this->detailview($this->modelview, $this->data['subgrid'], $id);

		if($this->data['row']->reducida) {
			$groups = $this->getGroupsInvoice();
			$this->data['groups'] = $groups;
		}
		$this->data['id'] = $id;
		$this->data['access'] = $this->access;
		$view = view('factura.pdf', $this->data)->render();
		$view = str_replace("http://localhost:8080/sximo51/public", public_path(), $view);
		return $view;
	}

	/**
	 * @param $GROUPS_DESCRIPTIONS
	 */
	public function getGroupsInvoice()
	{
		$groups = array();

		foreach ($this->data['subgrid']['rowData'] as $index => $rowLinea) {

			if (array_key_exists($rowLinea->aplica, $this::$GROUPS_DESCRIPTIONS)) {
				if (!isset($groups[$rowLinea->aplica]["importe"]))
					$groups[$rowLinea->aplica]["importe"] = 0;

				$groups[$rowLinea->aplica]["descripcion"] = $this::$GROUPS_DESCRIPTIONS[$rowLinea->aplica];
				$groups[$rowLinea->aplica]["importe"] += $rowLinea->import;
			}
		}

		return $groups;
	}

	public function createCharts($filter)
	{

		$stocksTable = \Lava::DataTable();
		$format = new DateFormat();

		$stocksTable->addDateColumn('Day of Month', $format->pattern("MMMM"))
				->addNumberColumn('2015')
				->addNumberColumn('2014');

		// Random Data For Example
		for ($a = 1; $a <= 12; $a++) {

			$firstDayOfMonth = Carbon::createFromDate(2015, $a, 1)->toDateString();
			$lastDay = date("t", Carbon::createFromDate(2015, $a, 1)->timestamp);
			$lastDayOfMonth = Carbon::createFromDate(2015, $a, $lastDay)->toDateString();

			$filter2015 = $filter . "AND fecfac >= '$firstDayOfMonth' AND fecfac <= '$lastDayOfMonth'";

			$rows = $this->model->getRows(["params" => $filter2015]);
			$priceMonth2015 = 0;
			foreach($rows['rows'] as $row) {
				$priceMonth2015 += $row->totfac;
			}

			$firstDayOfMonth = Carbon::createFromDate(2014, $a, 1)->toDateString();
			$lastDay = date("t", Carbon::createFromDate(2014, $a, 1)->timestamp);
			$lastDayOfMonth = Carbon::createFromDate(2014, $a, $lastDay)->toDateString();

			$filter2014 = $filter . "AND fecfac >= '$firstDayOfMonth' AND fecfac <= '$lastDayOfMonth'";

			$rows = $this->model->getRows(["params" => $filter2014]);

			$priceMonth2014 = 0;
			foreach($rows['rows'] as $row) {
				$priceMonth2014 += $row->totfac;
			}

			$rowData = array(
					"2015-$a-1", $priceMonth2015, $priceMonth2014
			);

			$stocksTable->addRow($rowData);
		}

		$chart = \Lava::LineChart('myFancyChart');
		$chart->datatable($stocksTable);
	}

	public function generatePieChart($filter, $year)
	{

		$model = new FacturaRedf();

		$filter = $filter . "AND ejefac = $year";
		$rows = $model->getRows(["params" => $filter]);

		$reasons = \Lava::DataTable();

		$reasons->addStringColumn('Clientes')
				->addNumberColumn('Percent');

		$totalPerc = 0;
		$slices = array();

		foreach($rows['rows'] as $row) {
			$totalPerc += $row->suma_cliente;

		}


		foreach($rows['rows'] as $row) {
			$perc = $row->suma_cliente/$totalPerc;
			if($perc < 0 ){
				$perc = 0;
			}
			$reasons->addRow(array($row->nomcli, $perc));
			$slices[] = \Lava::Slice(array (
					'color' => $this->stringToColorCode($row->nomcli)
			));

		}


		$piechart = \Lava::PieChart("Fact_$year")
				->setOptions(array(
						'datatable' => $reasons,
						'title' => "% Facturacion/cliente $year",
						'is3D' => true,
						'slices' => $slices

				));




	}

	public function stringToColorCode($str) {
		$code = dechex(crc32($str));
		$code = substr($code, 0, 6);
		return $code;
	}

	public function getImport() {
		return view("factura.import");
	}
}

class FacturaRedf extends Factura {

	public static function queryGroup()
	{
		return "GROUP BY codcli";
	}

	public static function querySelect()
	{
		return "SELECT sum(totfac) AS suma_cliente, codcli, nomcli FROM cabfactu";
	}


}