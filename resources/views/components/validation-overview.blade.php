@php
    $heading = $getHeading();
    $description = $getDescription();
    $isSimple = $isSimple();

    $isVisible = $isVisible();
@endphp

@if ($isVisible)
    <div>
        {{ $heading }}

        {{ $description }}
    </div>
@endif

