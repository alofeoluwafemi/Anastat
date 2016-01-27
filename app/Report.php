<?php

namespace App;

use App\Singleton;
use App\Entities\Report as ReportRepo;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;
use PHPExcel_Style_Border;

class Report extends PHPExcel
{

	use Singleton;

	public function __construct()
	{
		parent::__construct();
	}


	public function GetAllAffiliateReport()
	{

		$results = App('App\Entities\Report')->AllReport();

		$resultset = (count($results) + 1);
		// dd($results);

		$this->Parse($results,$resultset);

	}

	public function GetAffiliateReport($AffiliateId)
	{
		$results = App('App\Entities\Report')->ByAffiliate($AffiliateId);

		$resultset = (count($results) + 1);

		$filename = isset($results[0]['affiliate_name']) ? $results[0]['affiliate_name'].'-' : 'No-Result-';
		// dd($results);

		$this->Parse($results,$resultset,$filename);
	}

	/**
	 * 
 	*/
	private function Parse($results,$resultset,$filename = 'Request-Report-')
	{

		$this->getProperties()
			   ->setCreator('ANASTAT')
			   ->setTitle('Report')
			   ->setLastModifiedBy('ANASTAT')
			   ->setSubject('ANASTAT Report');


		$this->getActiveSheet()->setTitle('Report');

		/**
		 * Set Cells to Auto Resize
		 */
		$this->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$this->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$this->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$this->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$this->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);

		//Set
		$this->getDefaultStyle()->getFont()->setName('Arial');
		$this->getDefaultStyle()->getFont()->setSize(10);
		$this->getDefaultStyle()->getFont()->setBold(false);
		// $this->getDefaultStyle()->getFont()->setColor('#92B4FF');

		$headStyle = array(
	    'font'  => array(
	        'bold' => true,
	        'color' => array('rgb' => '92B4FF'),
	        'size'  => 10,
	        'name' => 'Arial'
	    ));

	    $this->getActiveSheet()->getStyle('A1:E1')->applyFromArray($headStyle);

	    $allsheet = 'A1:E'.$resultset;

	    //Set Border on sheet
		$this->getActiveSheet()
                ->getStyle($allsheet)
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

         //Header Row
         $this->setActiveSheetIndex(0)
	            ->setCellValue('A1', 'Affiliate Name')
	            ->setCellValue('B1', 'Month')
	            ->setCellValue('C1', 'Year')
	            ->setCellValue('D1', 'Client Name')
	            ->setCellValue('E1', 'No Of Requests Made');

		/**
		 * Fill cells with result
		*/
		foreach ($results as $key => $value) {

			$cell = $key + 2;

			/**
			 * Set Headers First
			 */
	        $this->setActiveSheetIndex(0)
	            ->setCellValue('A'.$cell, $value['affiliate_name'])
	            ->setCellValue('B'.$cell, $value['month'])
	            ->setCellValue('C'.$cell, $value['year'])
	            ->setCellValue('D'.$cell, $value['name'])
	            ->setCellValue('E'.$cell, $value['requests']);
	
				
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.date("d-m-Y").'.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($this, 'Excel2007');
		$objWriter->save('php://output');

	}

}