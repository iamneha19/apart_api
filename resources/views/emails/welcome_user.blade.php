<p>
Hi <?php echo $user['name']; ?>,
</p>
<p>
Thank you for choosing <?php echo env('PROJECT_NAME') ?>
</p>
<p>
    Your account has been successfully created in society <?php echo $user['society_name'] ?>
</p>
<p>
Login with email: <?php echo $user['email'] ?>
</p>
<p>Password: <?php echo $user['password'] ?></p>
