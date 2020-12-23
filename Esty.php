<?php
// Coder Devnit
error_reporting(0);
require_once './vendor/autoload.php';
require_once('includes/config.php');
require_once('includes/auth.php');

use GmailWrapper\Messages;

$pageToken = isset($_GET['pageToken']) ? $_GET['pageToken'] : null;
$date_order = isset($_GET['date_order']) ? $_GET['date_order'] : null;
$date_order = urldecode($date_order);

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
        <h2 style="text-align: center;">Get Order!</h2>
        <form method="get" id="display_pro">
            <div class="row">
                <div class='col-sm-6'>
                    <div class="form-group">
                        <input id="datepicker" type='text' class="form-control" name='date_order' value='<?php if(!empty($_GET['date_order'])){echo $_GET['date_order'];}?>'/>
                    </div>
                </div>
                <!--<div class='col-sm-6'>
                    <div class="form-group">
                        <select class="form-control" name='account_ebay'>
                            <option value=''>Account Ebay</option>
                            <option value="Label%20created" >Acc 1</option>
                            <option value='In%20transit' >Acc 2</option>
                            <option value='Delivered' >Acc 3</option>
                        </select>
                    </div>
                </div>-->

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
            if(!empty($date_order)){
                $msgs = new Messages($authenticate);

                $requets = [
                    "q" => "Etsy Order confirmation for: paid via etsy payments on ".$date_order,
                ];
                //print_r($requets);
                $messageList = $msgs->getMessages($requets, $pageToken);
                //print_r($messageList);
                foreach ($messageList['data'] as $key => $value) {
                    $msgId = $value->getId();
                    //echo '<a href="message_details.php?messageId='.$msgId.'" target="_blank">'.$msgId.'</a><br/>';
                }
                if(!$messageList['status']) {
                    echo $messageList['message'];
                    echo "<script>alert('Không có đơn!')</script>";
                    //exit;
                }else{
                    foreach ($messageList['data'] as $key => $value) {
                        echo '<tr>';
                        $msgId = $value->getId();
                        //echo '<a href="message_details.php?messageId='.$msgId.'" target="_blank">'.$msgId.'</a><br/>';
                        $messageDetails = $msgs->getMessageDetails($msgId);
                        if(!$messageDetails['status']) {
                            echo $messageDetails['message'];
                            exit;
                        }
                        foreach ($messageDetails['data']['body']['text/html'] as $key => $value) {
                            $value = str_replace("<br />","", $value);


                            $orderID = getStr($value, "Your order number is:","</a>");
                            $orderID = strip_tags($orderID);
                            $Quantity = getStr($value, "Quantity:","</div>");
                            $Quantity = strip_tags($Quantity);
                            $Size = getStr($value,"Size:","</div>");
                            $Size = strip_tags($Size);
                            $address = getStr($value, "Delivery address","</address>");
                            $address = strip_tags($address, "<br>");
                            $Shop = getStr($value,"Shop:","</div>");
                            $Shop = strip_tags($Shop);
                            $Delivery = getStr($value, "Order total","Order total");
                            $Delivery = strip_tags($Delivery);

                            $url_img = getStr($value,'<img src="https://i.etsystatic.com/', '"');
                            $url_img = 'https://i.etsystatic.com/'.$url_img;
                            $url_img = str_replace("75x75","794xN",$url_img);
                            $title = getStr($value, "<a style='text-decoration: none;","</a>");
                            $title = strip_tags("<a style='text-decoration: none;".$title);

                            ?>
                            <td><?php echo $date_order; ?></td>
                            <td><?php echo $Shop;?></td>
                            <td><?php echo $orderID;?></td>
                            <td><?php echo $title; ?></td>
                            <td><?php echo $Size;?></td>
                            <td><?php echo $Quantity;?></td>
                            <td><?php echo $address;?></td>
                            <td>
                            </td>
                            <td><?php echo $url_img;?></td>
                            <td><?php echo $Delivery;?>
                            </td>
                            <?php

                        }
                        echo '</tr>';
                       // echo $value;
                    }
                    $nextToken = $messageList['nextToken'];
                    echo '<p><a href="getOrders.php?pageToken='.$nextToken.'">Next</a></p>';
                }
            }
            ?>
            </tbody>
            </thead>
        </table>
    </div>
    
    </body>
    </html>
