<!doctype html>
<html lang="en">
<head>
</head>
<body>
    <div>Hello,</div>
    <br/>
    <div>Setting up your dashboard takes about 5 minutes.</div>
    <br/>
    <div>Use the following credentials to gain access to the <b>safety dashboard</b>:
    <br/><br/>
        Email:  {{ $email }} <br/>
        Password: {{ $password }}
    </div>
    <br/>
    <div> Please use the following link to log in the application:<br/>
        <a href="{{ URL::to('https://whiteknightsafety.com/webapp/login') }}" title="Log In"> Login in the application </a>
    </div>
    <br/>
    <div> Please use the following link to change your password: <br/>
        <a href="{{ URL::to('https://whiteknightsafety.com/webapp/forgot-password') }}" title="Change your password"> Change your password </a>
    </div>
    <br/>
    Best,
    <br/>
    White Knight Safety Team
    <br/>
    --
    <br/>
    Direct: 1.844.944.8356 (Canadian Mountain Time)<br/>

    Email: <a href="mailto:support@whiteknightsafety.com">support@whiteknightsafety.com</a><br/>

    Web: <a href="www.whiteknightsafety.com"> www.whiteknightsafety.com </a><br/>
</body>
</html>

