<!doctype html>
<html lang="en">
<head>
</head>
<body>
	<p>Someone bought something!</p>
    <p> 
        <br/>
        <b>Service:</b> {{ $service_code }}
        <br/><br/>
        <b>Company Name:</b> {{ $company_name }}
        <br/><br/>
        <b>Name:</b> {{ isset($name) ? $name : "none provided" }}
        <br/><br/>
        <b>Phone:</b> {{ isset($phone) ? $phone : "none provided" }}
        <br/><br/>
        <b>Email:</b> {{ isset($email) ? $email : "none provided" }}
        <br/><br/>
        <b>Comments:</b> {{ isset($comments) ? $comments : "none" }}
        <br/><br/>
    </p>  
</body>
</html>

