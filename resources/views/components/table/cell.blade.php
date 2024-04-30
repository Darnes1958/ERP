@props([
    'py' => 'py-1',

    'fcolor' => 'text-cool-gray-900',

])
<td {{ $attributes->merge(['class' => 'px-1 '.$py.' whitespace-no-wrap text-md leading-5 '.$fcolor]) }}>
    {{ $slot }}
</td>
