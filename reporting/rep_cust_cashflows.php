<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_ITEMSVALREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Stock Check Sheet
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/includes/db/manufacturing_db.inc");

//----------------------------------------------------------------------------------------------------

print_cust_cashflows();

//function getTransactions($category, $location, $rep_date)
function getTransactions($rep_date)
{

/*
define('ST_SALESINVOICE', 10);
define('ST_CUSTCREDIT', 11);
define('ST_CUSTPAYMENT', 12);
define('ST_CUSTDELIVERY', 13);
*/

	$sql = "SELECT sum((ov_amount+ov_gst)/rate) as totalinvoice FROM `1_debtor_trans` WHERE type=10 
Union
	SELECT sum((ov_amount+ov_gst)/rate) as totalcredit FROM `1_debtor_trans` WHERE  type=11
Union
	SELECT sum((ov_amount+ov_gst)/rate) as totalpayment FROM `1_debtor_trans` WHERE  type=12
Union
	SELECT sum((ov_amount+ov_gst)/rate) as totaldelivery FROM `1_debtor_trans` WHERE  type=13";

    return db_query($sql,"No transactions were returned");
}

//----------------------------------------------------------------------------------------------------

function print_cust_cashflows()
{
    global $comp_path, $path_to_root, $pic_height;

	$rep_date = $_POST['PARAM_0'];
    	$comments = $_POST['PARAM_1'];
	$destination = $_POST['PARAM_2'];

    	//$category = $_POST['PARAM_1'];
    	//$location = $_POST['PARAM_2'];
    	//$comments = $_POST['PARAM_3'];
	//$destination = $_POST['PARAM_4'];

	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");
/****
	if ($category == ALL_NUMERIC)
		$category = 0;
	if ($category == 0)
		$cat = _('All');
	else
		$cat = get_category_name($category);

	if ($location == ALL_TEXT)
		$location = 'all';
	if ($location == 'all')
		$loc = _('All');
	else
		$loc = get_location_name($location);
***/
		$cat = _('All');
		$loc = _('All');
		
	$cols = array(0, 100,200, 300, 400, 500);
	$headers = array( "", _('Invoice'), _('Credits'), _('Payments'), _('Delivered') );
	$aligns = array('left', 'left' , 'left' , 'left', 'left');


    $params =   array( 	0 => $comments,
    				    1 => array('text' => _('Category'), 'from' => $cat, 'to' => ''),
    				    2 => array('text' => _('Location'), 'from' => $loc, 'to' => ''),
						2 => array('text' => _('Date'), 'from' => $rep_date, 'to' => '')
    				  );

	$user_comp = "";

    $rep = new FrontReport(_('Customer Cashflows'), "DatedStockSheet", user_pagesize());

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();

	$res = getTransactions(date2sql($rep_date));
	//$res = getTransactions($category, $location,date2sql($rep_date));
	$catt = '';
	$count = 1;
	while ($trans=db_fetch($res))
	{
/*
		if ($location == 'all')
			$loc_code = "";
		else
			$loc_code = $location;
*/

/*
		if ($catt != $trans['cat_description'])
		{
			if ($catt != '')
			{
				$rep->Line($rep->row - 2);
				$rep->NewLine(2, 3);
			}
			$rep->TextCol(0, 1, $trans['category_id']);
			$rep->TextCol(1, 2, $trans['cat_description']);
			$catt = $trans['cat_description'];
			$rep->NewLine();
		}
*/
		//$rep->NewLine();
		//$dec = get_qty_dec($trans['stock_id']);
		$dec = 2;
		if(isset( $trans['totalinvoice'] ) )
		{
			$rep->TextCol(0, 1, "Total Invoiced");
		//	$rep->AmountCol(1, 2, $trans['totalinvoice'], $dec);
			$rep->AmountCol($count, $count+1, $trans['totalinvoice'], $dec);
		}
		if( isset( $trans['totalcredit'] ) )
		{
			$rep->TextCol(0, 1, "Total Credits");
			$rep->AmountCol(1,2 , $trans['totalcredit'], $dec);
			$rep->AmountCol($count, $count+1, $trans['totalcredit'], $dec);
		}
		if( isset( $trans['totalpayment'] ) )
		{
			$rep->TextCol(0, 1, "Total Payments");
			$rep->AmountCol(3,4, $trans['totalpayment'], $dec);
			$rep->AmountCol($count, $count+1, $trans['totalpayment'], $dec);
		}
		if( isset( $trans['totaldelivery'] ) )
		{
			$rep->TextCol(0, 1, "Total Deliveries");
			$rep->AmountCol(4,5, $trans['totaldelivery'], $dec);
			$rep->AmountCol($count, $count+1, $trans['totaldelivery'], $dec);
		}
		//$rep->NewLine();
		$count++;
		
	}
	$rep->Line($rep->row - 4);
	$rep->NewLine();
    $rep->End();
}

?>
