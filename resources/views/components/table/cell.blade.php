@props([
    'py' => 'py-1',

])
<td {{ $attributes->merge(['class' => 'px-1 '.$py.' whitespace-no-wrap text-md leading-5 text-cool-gray-900']) }}>
    {{ $slot }}
</td>
