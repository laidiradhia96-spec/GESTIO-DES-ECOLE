@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-2 py-1 border-b-2 border-[#C89B3C] text-xs font-semibold leading-5 text-[#0B2A55] focus:outline-none focus:border-[#C89B3C] transition-all duration-300 ease-in-out hover:-translate-y-0.5 whitespace-nowrap'
            : 'inline-flex items-center px-2 py-1 border-b-2 border-transparent text-xs font-medium leading-5 text-gray-600 hover:text-[#0B2A55] hover:border-[#C89B3C] focus:outline-none focus:text-[#0B2A55] focus:border-[#C89B3C] transition-all duration-300 ease-in-out hover:-translate-y-0.5 whitespace-nowrap';

@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
