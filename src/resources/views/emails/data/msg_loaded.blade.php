<p> The data loading request has been run by system. ( {{ $job->name }} ) </p>
Type : {{ $job->type }} <br/>
<br/>
Please find the result below:

<div>
    {!! nl2br($job->summary) !!}
</div>

for more information please check the link
{{$url}} <br/>

