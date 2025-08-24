@php
    $heading = $getHeading();
    $description = $getDescription();
    $isSimple = $isSimple();
@endphp

<div>
    {{ $heading }}

    {{ $description }}
</div>

