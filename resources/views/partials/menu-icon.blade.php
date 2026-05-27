@props(['name'])

@switch($name)
    @case('home')
        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.592 0L21.75 12M4.5 9.75v9.375c0 .621.504 1.125 1.125 1.125H9.75v-4.5h4.5v4.5h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
        </svg>
        @break
    @case('layers')
        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 8.25-9-4.5-9 4.5 9 4.5 9-4.5Zm0 4.5-9 4.5-9-4.5m18 4.5-9 4.5-9-4.5" />
        </svg>
        @break
    @case('dots')
        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm6 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm6 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
        </svg>
        @break
    @case('building')
        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 21V6.75A2.25 2.25 0 0 1 6.75 4.5h3A2.25 2.25 0 0 1 12 6.75V21m0-12.75h5.25A2.25 2.25 0 0 1 19.5 10.5V21M7.5 8.25h1.5m-1.5 3h1.5m-1.5 3h1.5m6-3h1.5m-1.5 3h1.5" />
        </svg>
        @break
    @case('link')
        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 6.364 6.364l-1.768 1.768a4.5 4.5 0 0 1-6.364-6.364m-.884 4.596a4.5 4.5 0 0 1-6.364-6.364l1.768-1.768a4.5 4.5 0 0 1 6.364 6.364" />
        </svg>
        @break
    @case('file')
        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-.989-2.386l-4.5-4.5a3.375 3.375 0 0 0-2.386-.989H8.25A2.25 2.25 0 0 0 6 6v12a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 18v-3.75M12 8.25V3.75m-3 12 2.25 2.25L15 13.5" />
        </svg>
        @break
    @case('settings')
        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.658.844.145.066.287.14.425.22.325.188.719.2 1.04.002l1.11-.684a1.125 1.125 0 0 1 1.45.12l1.833 1.833c.389.389.44 1.002.12 1.45l-.684 1.11c-.198.321-.186.715.002 1.04.08.138.154.28.22.425.158.345.47.595.844.658l1.281.213c.542.09.94.56.94 1.11v2.593c0 .55-.398 1.02-.94 1.11l-1.281.213a1.125 1.125 0 0 0-.844.658c-.066.145-.14.287-.22.425-.188.325-.2.719-.002 1.04l.684 1.11c.32.448.269 1.061-.12 1.45l-1.833 1.833a1.125 1.125 0 0 1-1.45.12l-1.11-.684a1.125 1.125 0 0 0-1.04.002 6.75 6.75 0 0 1-.425.22c-.345.158-.595.47-.658.844l-.213 1.281c-.09.542-.56.94-1.11.94h-2.593c-.55 0-1.02-.398-1.11-.94l-.213-1.281a1.125 1.125 0 0 0-.658-.844 6.75 6.75 0 0 1-.425-.22 1.125 1.125 0 0 0-1.04-.002l-1.11.684a1.125 1.125 0 0 1-1.45-.12L2.864 18.69a1.125 1.125 0 0 1-.12-1.45l.684-1.11a1.125 1.125 0 0 0-.002-1.04 6.75 6.75 0 0 1-.22-.425 1.125 1.125 0 0 0-.844-.658L1.08 13.794a1.125 1.125 0 0 1-.94-1.11v-2.593c0-.55.398-1.02.94-1.11l1.281-.213c.374-.063.686-.313.844-.658.066-.145.14-.287.22-.425.188-.325.2-.719.002-1.04l-.684-1.11a1.125 1.125 0 0 1 .12-1.45L4.696 2.25a1.125 1.125 0 0 1 1.45-.12l1.11.684c.321.198.715.186 1.04-.002.138-.08.28-.154.425-.22.345-.158.595-.47.658-.844Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        @break
    @default
        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21.75 6.75-10.5 10.5-4.5-4.5-4.5 4.5" />
        </svg>
@endswitch
