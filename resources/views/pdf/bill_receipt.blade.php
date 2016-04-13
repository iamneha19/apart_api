
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice</title>
</head>

<body>
<div id="page-wrap" class="page-wrap" style="width:756px;font-family:Helvetica,Arial,sans-serif; margin:0; font-size:14px; margin-top:50px; font-weight:bold">

	<table width="756px" vspace="0" cellspacing="0" cellpadding="0" align="center">
    	<tr>
        	<td class="bill-head" style="text-align:center; width:100%; font-size:27px; border-bottom:1px solid #000000; padding:15px 0 15px 0">Bill Receipt</td>
        </tr>
        <tr>
        	<td class="company-name" style="border-bottom:1px solid #000000; padding-bottom:25px; ">
            	<p style="text-align:center; font-size:27px;">{{$flatBill->flat->flatDetails->society->name}}</p>
            </td>
        </tr>
        <tr><td style=" padding-top:10px;"></td></tr>
        <tr >
        	<td align="center" style=" border-top:1px solid #000000; "><p style="text-align:center;  padding-top:10px; text-decoration:underline; width:600px; margin-top:15px;font-family:Helvetica,Arial,sans-serif; font-size:15px;">RECEIPT</p></td>
        </tr>
        <tr>
        	<td align="center">
            
            	<table width="600px">
                	<tr>
                        <?php if($flatBill->payment->id<10){ ?>
                            <td style="width:300"><span style="font-weight:normal;">Receipt No. </span><span style="display:inline-block; margin-left:15px; ">00{{$flatBill->payment->id}}</span></td>
                        <?php } if(($flatBill->payment->id>10)&& ($flatBill->payment->id<100)){ ?>
                            <td style="width:300"><span style="font-weight:normal;">Receipt No. </span><span style="display:inline-block; margin-left:15px; ">0{{$flatBill->payment->id}}</span></td>
                        <?php } else { ?>
                            <td style="width:300"><span style="font-weight:normal;">Receipt No. </span><span style="display:inline-block; margin-left:15px; ">{{$flatBill->payment->id}}</span></td>
                        <?php } ?>
                        <td style="width:300px; text-align:right;" align="right"><span style="font-weight:normal;">Receipt Date </span><span style="display:inline-block; margin-left:30px;"><?php echo date('d-m-Y');?></span></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
        	<td style=" padding-top:15px;" width="600px" align="left">
            	<table style="width:100%; margin-left:85px;">
                	<tr>
                    	<td style="width:46%;"><span style="font-weight:normal;">Received with thanks from </span></td>
                        <td style="">
                            <span style=" margin-left:-85px;">{{$flatBill->flat->flatDetails->user->first_name}}-{{$flatBill->flat->flatDetails->user->last_name}}</span>
                        </td>
                    </tr>
                </table>
				<table style="width:100%;">
                	<tr>
                    	<td style="width:46%;"><span></span></td>
                        <td style="font-weight:normal;"><span style="font-weight:bold;">Flat: </span>{{$flatBill->flat->flatDetails->building->name}}-{{$flatBill->flat->flatDetails->block->block}}-{{$flatBill->flat->flat_no}}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
        	<td width="600" align="left" style=" padding-top:15px;">
            	<table style="width:100%; margin-left:85px;font-weight:normal;">
                	<tr>
                    	<td>Rs.{{convert($flatBill->charge)}}<span></span></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
        	<td style=" padding-top:15px;">
            	<table style="width:100%; margin-left:85px;font-weight:normal;">
                    <?php if($flatBill->payment->payment_type == 'cash' ){ ?>
                    <tr>
                        <td valign="top" style="width:20%;"><span>By Cash </span></td>
                    </tr>
                     <?php }else{ ?>
                        <tr>
                            <td valign="top" style="width:20%;"><span>Vide chq. </span></td>
                            <td style=""><span>102880 dt 6/5/04 ICICI Bank, Fort</span><br><span style="">towards Bill no. 125 of April 2014 in full payment</span></td>
                        </tr>
                    <?php } ?>
                </table>
            </td>
        </tr>
        <tr>
        	<td style="">
                <p style="margin-left:85px;border-bottom:1px solid #000000;"><span style="font-weight:normal;">Rs. </span><span style="">{{$flatBill->charge}}</span></p>
            </td>
            <hr>
        </tr>
        
        <hr>
        <tr>
            <td>
                <p style="margin-left:120px; margin-top:0; font-size:11px; border-top:1px solid #000000;font-weight:normal;">Subject to realisation of payment</p>
            </td>
        </tr>

        
    </table>
</div>

</body>
</html>
