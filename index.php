<?php 
require('setup.php');
include('lib/Xero.php');
session_start();
if (isset($_GET['logoff'])) {
	session_unset();
}

$xero = new Xero; 

if($_GET['do'] == "create_contact"){ //the input format for creating a new contact see http://blog.xero.com/developer/api/contacts/ to understand more 
	$new_contact = array( 
		array( 
			"Name" => "MJ", 
			"FirstName" => "Michael", 
			"LastName" => "Jackson", 
			"Addresses" => array( 
				"Address" => array( 
					array( 
						"AddressType" => "POBOX", 
						"AddressLine1" => "PO Box 100", 
						"City" => "Someville", 
						"PostalCode" => "3890" ), 
					array( 
						"AddressType" => "STREET", 
						"AddressLine1" => "1 Some Street", 
						"City" => "Someville", 
						"PostalCode" => "3890" 
					) 
				) 
			) 
		) 
	);

    //create the contact
    $contact_result = $xero->Contacts( $new_contact );

    //echo the results back
    if ( is_object($contact_result) ) {
   	 	//use this to see the source code if the $format option is "xml"
   	 	echo htmlentities($contact_result->asXML());
    } else {
   	 	//use this to see the source code if the $format option is "json" or not specified
    	echo json_encode($contact_result);
    }
}


if($_GET['do'] == "create_invoice_and_payment"){		
    //the input format for creating a new invoice (or credit note) see http://blog.xero.com/developer/api/invoices/
    $invNumber = rand(1, 20);
    $new_invoice = array(
        array(
            "Type"=>"ACCREC",
            "Contact" => array(
                "Name" => "MJ"
            ),
            "InvoiceNumber" => "I00".$invNumber,
            "Reference" => "J0011",
            "Date" => date("Y-m-d"),
            "DueDate" => date("Y-m-d", strtotime("+30 days")),
            "Status" => "AUTHORISED",
            "LineAmountTypes" => "Exclusive",
            "LineItems"=> array(
                "LineItem" => array(
                    array(
                        "Description" => "Just another test invoice",
                        "Quantity" => "2.0000",
                        "UnitAmount" => "250.00",
                        "AccountCode" => "200"
                    )
                )
            )
        )
    );

    //the input format for creating a new payment see http://blog.xero.com/developer/api/payments/ to understand more
    $new_payment = array(
        array(
            "Invoice" => array(
                "InvoiceNumber" => "I00".$invNumber
            ),
            "Account" => array(
                "Code" => "200"
            ),
            "Date" => date("Y-m-d", strtotime("+5 days")),
            "Amount"=>"100.00",
        )
    );


    //raise an invoice
    $invoice_result = $xero->Invoices( $new_invoice );
    //put the payment to the agove invoice
    $payment_result = $xero->Payments( $new_payment );

    //echo the results back
    if ( is_object($invoice_result) ) {
   	 	//use this to see the source code if the $format option is "xml"
   	 	echo htmlentities($invoice_result->asXML());
    } else {
   	 	//use this to see the source code if the $format option is "json" or not specified
    	echo json_encode($invoice_result);
    }
    echo '<hr />';
    if ( is_object($payment_result) ) {
   	 	//use this to see the source code if the $format option is "xml"
   	 	echo htmlentities($payment_result->asXML());
    } else {
   	 	//use this to see the source code if the $format option is "json" or not specified
    	echo json_encode($payment_result);
    }

}
	
if($_GET['do'] == "pdf_an_invoice"){ // first get an invoice number to use 
	$org_invoices = $xero->Invoices; 
	$invoice_count = sizeof($org_invoices->Invoices->Invoice); $invoice_index = rand(0,$invoice_count); 
	$invoice_id = (string) $org_invoices->Invoices->Invoice[$invoice_index]->InvoiceID; 
	if(!$invoice_id) echo "You will need some invoices for this...";

	// now retrieve that and display the pdf
	$pdf_invoice = $xero->Invoices($invoice_id, '', '', '', 'pdf');
	header('Content-type: application/pdf'); header('Content-Disposition: inline; filename="the.pdf"'); 
	echo ($pdf_invoice);
}

// OTHER COOL STUFF
//get details of an account, with the name "Test Account"
//$result = $xero->Accounts(false, false, array("Name"=>"Test Account") );
//the params above correspond to the "Optional params for GET Accounts" on http://blog.xero.com/developer/api/accounts/

//to do a POST request, the first and only param must be a multidimensional array as shown above in $new_contact etc.

//get details of all accounts
//$all_accounts = $xero->Accounts;

//echo the results back
//if ( is_object($invoice_result) ) {
	 	//use this to see the source code if the $format option is "xml"
	 	//echo htmlentities($payment_result->asXML()) . "<hr />";
//} else {
	 //use this to see the source code if the $format option is "json" or not specified
	//echo json_encode($payment_result) . "<hr />";
//}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Xero Library</title>
	<link rel="stylesheet" type="text/css" href="<?=$web_root?>/css/style.css" />
</head>
<body>
	<div id="container">
		<h1>Xero API PHP</h1>
		<div id="body">
			<?php if (isset($_SESSION['access_token'])): ?>
				<p><a href="<?php echo $web_root?>/list_contacts.php">List Contacts</a><br />
					<a href="<?php echo $web_root?>/list_invoices.php">List Invoices</a></p>
				<p><em>Only create a client once as Xero might not be able to create the same one.</em><br />
					<a href="<?php echo $web_root?>/index.php?do=create_contact">Create Contact</a><br />
					<a href="<?php echo $web_root?>/index.php?do=create_invoice_and_payment">Create Invoice &amp; Payment</a><br />
					<a href="<?php echo $web_root?>/index.php?do=pdf_an_invoice">PDF an Invoice</a></p>
				<p><a href="<?php echo $web_root?>?logoff=true">Logoff Xero</a></p>
			<?php else: ?>
				<p><a href="<?php echo $web_root?>/authorise.php"><img src="<?php echo $web_root?>/connect_xero_button_blue.png" border="0"></a></p>
			<?php endif ?>
		</div>
	</div>
</body>
</html>