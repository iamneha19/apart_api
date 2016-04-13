<p>
Dear <b><?php echo $status['to_name'];?></b>,
</p>
<p>
We have reviewed <b><?php echo $status['society_name'];?></b>.
</p>
<p>
    Please see below  status and notes which i have added for <b><?php echo $status['society_name'];?></b>.
</p>
<p>
    Status :  <?php if($status['status']=='YES'){?> <b>Approved</b> <?php } else{ ?> <b>Disapproved </b> <?php }?>
</p>
<p>
    <b>Notes:</b>
</p>
<p>
    <?php echo $status['description']; ?>
</p>
<br>
<p>
    Thanks and Regards,
    <br>
    <?php echo $status['from_name'];?>
</p>

