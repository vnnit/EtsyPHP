<?php
//Coder Devnit
set_time_limit(0);
if (ob_get_level() == 0) ob_start();
@ini_set("display_errors","OFF");
/* connect to gmail */
$account_esty = isset($_POST['account_esty']) ? $_POST['account_esty'] : null;
if(!empty($account_esty)){
    $account_esty_arry = explode('|', $account_esty);
    $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';
    $username = $account_esty_arry[0];
    $password = $account_esty_arry[1];
    $date_order = isset($_POST['date_order']) ? $_POST['date_order'] : null;
    $date_order = urldecode($date_order);
    $date_order = str_replace(",", "", $date_order);
}
/* try to connect */
?>
    <html>
    <head>
        <title>Get Tracking .... </title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <!--<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>-->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js"></script>
        <!-- <link rel="stylesheet"
               href="./style/css/bootstrap-datetimepicker.css">
         <script src="./style/js/bootstrap-datetimepicker.min.js"></script>-->
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script>
            $( function() {
                // $( "#datepicker" ).datepicker();
                //$( "#datepicker" ).datepicker( "option", "dateFormat", 'd M, yy' );
                $( "#datepicker" ).datepicker({
                    dateFormat: 'd M, yy',
                    //showButtonPanel: true
                });
            } );
        </script>
    </head>
    <body>


    <div class="container" style="margin-top: 20px;">
        <h2 style="text-align: center;">Get Order! <?php if(!empty($username)){ echo 'Account: '. $username; }?></h2>
        <form method="post" id="display_pro">
            <div class="row">
                <div class='col-sm-6'>
                    <div class="form-group">
                        <input placeholder="Date Filter" id="datepicker" type='text' class="form-control" name='date_order' value='<?php if(!empty($_POST['date_order'])){echo $_POST['date_order'];}?>'/>
                    </div>
                </div>
                <?php $account = @file('./account.txt');
                if(!empty($account)) {?>

                    <div class='col-sm-6'>
                        <div class="form-group">

                            <select class="form-control" name='account_esty'>
                                <?php foreach($account as $a){
                                    $arry = explode("|", $a);?>
                                    <option value='<?php echo $a;?>' <?php
                                    if($account_esty == $a){ echo 'selected="selected"'; }
                                    ?>><?php echo $arry[0];?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <p><a href="add-email.php" target="_blank">Add More Email!</a></p>
                    </div>
                <?php }?>

            </div>


            <input type="submit" name="submitbutton" value="Submit" class="btn btn-primary">
        </form>
    </div>
    <hr>
    <div style="width: 90%; margin: auto;">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Date</th>
                <th>Account</th>
                <th>Order ID</th>
                <th>Item</th>
                <th>Option</th>
                <th>Qty</th>
                <th>SHIPPING ADDRESS</th>
                <th>Tracking</th>
                <th>LINK IMAGE</th>
                <th>Sell Price</th>
            </tr>
            <tbody>
            <?php
            if (! function_exists('imap_open') || empty($account_esty)) {
                echo "IMAP is not configured.";
                exit();
            } else {
                ?>
                <?php

                /* Connecting Gmail server with IMAP */
                $connection = imap_open($hostname, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());

                /* Search Emails having the specified keyword in the email subject */
                if(!empty($date_order)){
                    $emailData = imap_search($connection, 'BODY "Paid via Etsy Payments on " ON "'.$date_order.'"');
                    $emailData = array_reverse($emailData, true);
                }else{
                    $emailData = imap_search($connection, 'SUBJECT "Etsy Order confirmation for:" BODY "Paid via Etsy Payments on"');
                    $emailData = array_reverse($emailData, true);
                    $emailData = array_slice($emailData, 0, 10);
                }

                if (! empty($emailData)) {
                    ?>
                    <?php
	                
	                
                    foreach ($emailData as $emailIdent) {
                        $overview = imap_fetch_overview($connection, $emailIdent, 0);
                        //$message = imap_fetchbody($connection, $emailIdent, '1.1');
                        $message = imap_fetchbody($connection, $emailIdent, '2');
                        //$messageExcerpt = substr($message, 0, 150);
                        $partialMessage = trim(quoted_printable_decode($message));
                        //echo $partialMessage;
                        $date = date("d F, Y", strtotime($overview[0]->date));
                        $value = str_replace("<br />","", $partialMessage);
                        $orderID = getStr($value, "Your order number is:","</a>");
                        $orderID = strip_tags($orderID);
                        
					    $all_item = item($partialMessage);
					    
					    
					    $address = getStr($value, "<address","</address>");
                        $address = strip_tags("<address".$address, "<br>");
                        $Shop = getStr($value,"Shop:","</div>");
                        $Shop = strip_tags($Shop);
                        $total = getStr($value, "Order total","Order total");
                        $total = strip_tags($total);
                        $Item_total = getStr($total, "Item total:","Delivery");
                        $Delivery = getStr($total,"Delivery:","Sales tax");
                        if(stripos($total,"Sales") === false){
                            $Delivery = getStr($total,"Delivery:","Tax");
                        }
                        
                        $date_order = getStr($value, "Paid via Etsy Payments on","</div>");
                        $number_item = getStr($value, "Congratulations on your Etsy sale of ","</div>");
						

                        ?>
                        <tr>
                            <td><?php echo $date_order;
                            ?></td>
                            <td><?php echo $Shop;echo "<br>".$number_item;?></td>
                            <td><?php echo $orderID;?></td>
                            <td><?php foreach($all_item as $a){
	                        	echo $a['title']."<hr>";
	                        }
	                        ?></td>
                            <td><?php foreach($all_item as $a){
	                        	echo $a['color'].' '.$a['size']."<hr>";
	                        }
	                        ?></td>
                            <td><?php foreach($all_item as $a){
	                        	echo $a['qty']."<hr>";
	                        }
	                        ?></td>
                            <td><?php echo $address;?></td>
                            <td>
                            </td>
                            <td><?php foreach($all_item as $a){
	                        	echo '<a href="'.$a['img'].'" target="_blank">'.$a['img'].'</a>'."<hr>";
	                        }
	                        ?> </td>
                            <td>Item total: <?php echo $Item_total;?><br>
                                Delivery: <?php echo $Delivery;?>
                            </td>

                        </tr>
                        <?php
	                        zflush();
                    } // End foreach
                    ?>
                    <?php
                } // end if

                imap_close($connection);
            }
            ?>
            </tbody>
            </thead>
        </table>
    </div>

    </body>
    </html>
<?php
function item($partialMessage){
	$doc = new DOMDocument();
	@$doc->loadHTML($partialMessage);
	$finder = new DOMXpath($doc);
	$all_item = $finder->query('//div[@class="avatar-media-block"]');
	foreach($all_item as $a){
	    $item = DOMinnerHTML($a);
	    //echo $item;
	    $title = getStr($item, '<a style="text-decoration: none;','</a>');
		$title = strip_tags('<a style="text-decoration: none;'.$title);
		$Quantity = getStr($item, "Quantity:","</div>");
	    $Quantity = strip_tags($Quantity);
	    $Size = getStr($item,"Size:","</div>");
		if(empty($Size)){
			$Size = getStr($item,"Unisex shirt size:","</div>");
		}
	    $Size = strip_tags($Size);
	    $Color = getStr($item,"Color:","</div>");
	    $Color = strip_tags($Color);
	    $url_img = getStr($item,'https://i.etsystatic.com/', '"');
        $url_img = 'https://i.etsystatic.com/'.$url_img;
        $url_img = str_replace("75x75","fullxfull",$url_img);
	    if(!empty(trim($title))){
		    $arry_item[] = [
		            'title' => trim($title),
		            'qty' => trim($Quantity),
		            'size' => trim($Size),
		            'color' => trim($Color),
		            'img' => $url_img
		    ];
	    }
	}
	return $arry_item;
}
function getStr($string,$start,$end){
    $str = explode($start,$string);
    $str = explode($end,$str[1]);
    return $str[0];
}
function DOMinnerHTML(DOMNode $element) 
	{ 
	    $innerHTML = ""; 
	    $children  = $element->childNodes;
	
	    foreach ($children as $child) 
	    { 
	        $innerHTML .= $element->ownerDocument->saveHTML($child);
	    }
	
	    return $innerHTML; 
	}
function zflush(){
	echo str_pad('',4096)."\n";   
	ob_flush();
	flush();
}
?>
