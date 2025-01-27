<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <div class="container">

            <div class="form-group">
              <label for="exampleInputEmail1">Email address</label>
              <input id="email" type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
            </div>
            <div class="form-group">
              <label for="exampleInputPassword1">Password</label>
              <input id="password" type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
            </div>
            <button type="submit" class="btn btn-primary" id="submitbutton">Submit</button>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function(){
       $('#submitbutton').on('click',function(){
           const email = $('#email').val();
           const password = $('#password').val();
           $.ajax({
            url : 'login',
            type : 'post',
            contentType: 'application/json',
            data : JSON.stringify({
                email : email,
                password : password,
            }),
            success: function(response){
                console.log(response);
            },
            error: function(xhr) {
                const errorResponse = xhr.responseJSON;
                const errorMessage = errorResponse ? errorResponse.message : 'An error occurred. Please try again.';

                $('#error-message').text(errorMessage).show();
            }

           });
       });
    });
</script>
</body>
</html>
