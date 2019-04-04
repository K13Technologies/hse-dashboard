<!doctype html>
<html lang="en">
<head>
</head>
<body>
    <div>
        Template: {{$type}}
    </div>
    <div>
        Client: {{ $object->addedBy->first_name }} {{ $object->addedBy->last_name }} - {{ $object->addedBy->company->company_name }}
    </div>
    @if (!$object instanceOf Inspection)
        <div>
            Title: {{ $object->title }}
        </div>
    @endif
    <br/>
    <div> White Knight Safety Team </div>
    <br/>
        Try <a href="https://whiteknightsafety.com">White Knight Safety Software <b>Free</b></a> for <b>30 days</b>. No credit cards required.
    <br/>
</body>
</html>

