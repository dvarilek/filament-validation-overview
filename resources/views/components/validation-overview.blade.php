@php
    $heading = $getHeading();
    $description = $getDescription();
    $isNavigatable = false;

    $schema = $this->getSchema($getSchemaName());
    $errorMessages = $errors->getMessages();
@endphp

@if (filled($errorMessages))
    <div
        x-data="{
            navigateToSchemaElement(statePath) {
                const schemaElements = document.querySelectorAll('[x-data^=\"filamentSchemaComponent\"]');
                // todo: fix
                console.log(schemaElements)

                for (const element of schemaElements) {
                    if (Alpine.$data(element)?.$statePath === statePath) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'start' })
                        break
                    }
                }
            }
        }"
    >
        {{ $heading }}

        {{ $description }}

        <ul>
            @foreach($errorMessages as $statePath => $messages)
                @php
                    $component = $schema->getComponentByStatePath($statePath, withAbsoluteStatePath: true);

                    if (! $component) {
                        continue;
                    }
                @endphp

                <li>
                    {{ $component->getLabel() }}

                    @if (filled($messages))
                        <ul>
                            @foreach($messages as $message)
                                <div x-on:click="navigateToSchemaElement('{{ $statePath }}')">
                                    {{ $message }}
                                </div>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
