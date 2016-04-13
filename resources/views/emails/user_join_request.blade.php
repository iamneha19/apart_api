<p>
Hi {{$user['name'] }} 
</p>
<p>
Thank you for choosing <?php echo env('PROJECT_NAME') ?>
</p>
<p>
    Your account has been successfully created. Will be approved by {{ $user['society'] }} admin.
</p>
<p>
Login with email: {{ $user['email'] }}
</p>
<p>password: {{ $user['password'] }}</p>
<br/><br/> -Regards <br /> Team <?php echo env('PROJECT_NAME') ?>