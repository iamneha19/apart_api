<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
</head>

<body>
    <div id="page-wrap" class="page-wrap" style="width:756px;font-family:Helvetica,Arial,sans-serif; margin:0; font-size:14px; margin-top:50px; font-weight:bold">
<table width="756px" vspace="0" cellspacing="0" cellpadding="0" align="center">
    	<tr>
        	<td class="company-name" style="border:1px solid #000000; padding-bottom:25px; text-align:center; border-radius:20px; width:100%; ">
            	<p style="text-align:center; font-size:27px;">{!! ucwords($flatBill->society->name) !!}</p>
                <p>{!! ucfirst($flatBill->society->address) !!}</p>
                <!-- <p style="text-align:center; font-size:13px; padding:0 0 0 0; margin:-20px;font-weight:normal;">Regn No. BOM/HSG/TC/42520/2000-02DT. 16-10-2002<br>Bajaj Road, Vile Parle (West) Mumbai 400056.</p> -->
            </td>
        </tr>

        <tr>
        	<td>
            	<table style="width:100%;">
                	<tr>
                    	<td style="width:50%;">
                        <br />
                        <p style="font-weight:bold;">Bill for the month of <span>{!! ucfirst($flatBill->month) !!}</span></p>
                        <p style="font-weight:bold;">Name: <span style="font-weight:bold;">{!! $flatBill->flat->flatDetails->user->fullName !!}</span></p>
                        <p style="font-weight:bold;">Flat No: {!! $flatBill->select2['text'] !!}</p>
                        <br />
                        </td>
                        <!-- <td valign="top" style="text-align:right; font-weight:normal;">
                        	<br>
                        	<p>Bill Info 1: Lorium Ipsum</p>
                            <p>Bill Info 2: Lorium Ipsum</p>
                        </td> -->
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
        	<td>
            	<table cellspacing="0" cellpadding="0" style="width:100%; border:1px solid #000000; font-weight:normal;">
                	<tr style="">
                    	<th style="padding:5px; width:75%;border-bottom:1px solid #000000; text-align:center;">Particular</th>
                        <th style="padding:5px; width:25%;border-bottom:1px solid #000000;border-left:1px solid #000000;">Amount</th>
                    </tr>
                    @foreach ($flatBill->flatBillItems as $bag)
                    <tr style="">
                    	<td style="padding:5px;padding-bottom:10px;">{!! $bag->item->name !!}</td>
                        <td style="padding:5px;border-left:1px solid #000000;padding-bottom:10px;">{!! $bag->item->charge !!}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td style="padding:5px;">Maintenance Charge</td>
                        <td style="padding:5px;border-left:1px solid #000000;padding-bottom: 100px">{!! $flatBill->charge . ' x ' . $flatBill->flat->square_feet_1 . ' Sq. ft' !!}</td>
                    </tr>
                    <tr>
                    	<td style="padding-bottom:10px; border-top:1px solid #000000;">
                        	<table style="width:100%;">
                            	<!-- <tr>
                                	<td style=" padding-left:5px; width:300px;">Principal Amount:</td>
                                    <td style="width:150px;">26,620.00</td>
                                    <td align="right" style="text-align:right; padding-right:10px; width:50px;">Total:</td>
                                </tr> -->
                                <!-- <tr>
                                	<td style=" padding-left:5px;">Accumulated Interest:</td>
                                    <td>3,133.00</td>
                                    <td align="right" style="text-align:right; padding-right:10px;">Arears:</td>
                                </tr> -->
                                <tr>
                                    <td align="right" style="text-align:right; padding-right:10px;">Service Tax:</td>
                                </tr>
                                <tr>
                                	<!-- <td colspan="2" style=" padding-left:5px; width:350px;">Rs. Thirty Two Thousand Seven Hundred Fifty seven only</td> -->
                                    <td align="right" style="text-align:right; padding-right:10px;">Grand Total:</td>
                                </tr>
                            </table>
                        </td>
                        <td valign="top" style="border-left:1px solid #000000; padding-left:5px; padding-bottom:10px;border-top:1px solid #000000;">
                            <table>
                            	<!-- <tr><td>2,540.00</td></tr>
                                <tr><td>29,751.00</td></tr> -->
                                <tr><td>{!! $flatBill->service_tax !!}%</td></tr>
                                <tr><td>{!! $flatBill->total_charge !!} Rs</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
