<div>
      Dear <b><?php echo $pwd['username']; ?> ,</b>
     <br/><br/> This is with reference to your change password request.
     <br/><br/> To log on the site,please use the following credentials:
     <br/> <br/><b> Username:</b> <?php echo $pwd['email'];?>
     <br/> <b>Password:</b> <?php echo $pwd['password']; ?>
     <br/><br/> -Regards <br /> Team <?php echo env('PROJECT_NAME') ?>
</div>
