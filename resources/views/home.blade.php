<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>URLRiot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="/icon.png"/>
    <link rel="icon" href="/icon.ico"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Google Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-900">

<div class="max-w-xl mx-auto mt-20 p-6 bg-white shadow rounded-lg mb-3">
    <a href="/"><img src="/logo.svg" alt="URLRiot Logo" class=" w-72 my-2 mb-3 mx-auto"></a>
    <p class="text-center mb-7 font-semibold text-gray-800 w-96 mx-auto">A simple, fast, multi-engine URL safety checker.</p>

    <form action="{{ route('check') }}" method="POST" class="mb-5">
        @csrf
        <input
            type="text"
            name="url"
            placeholder="Paste a URL..."
            class="w-full p-3 border focus:outline-none focus:outline-amber-300 focus:outline-2 rounded-full"
            value="{{ old('url', $inputUrl ?? '') }}"
            required
        >
        @error('url')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror

    <button
    id="checkBtn"
    type="submit"
    class="w-full mt-3 group relative inline-flex h-14 items-center justify-center rounded-full bg-neutral-50 py-1 pl-6 pr-14 font-medium text-black disabled:opacity-70 disabled:cursor-not-allowed"
>
    <span id="checkBtnText" class="z-10 pr-2">
        Check URL
    </span>

    <div
        id="checkBtnIcon"
        class="absolute right-1 inline-flex h-12 w-12 items-center justify-end rounded-full bg-amber-300 transition-[width] group-hover:w-[calc(100%-8px)]"
    >
        <div class="mr-3.5 flex items-center justify-center">
            <!-- arrow icon -->
            <svg
                id="arrowIcon"
                width="15"
                height="15"
                viewBox="0 0 15 15"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 text-black"
            >
                <path
                    d="M8.14645 3.14645C8.34171 2.95118 8.65829 2.95118 8.85355 3.14645L12.8536 7.14645C13.0488 7.34171 13.0488 7.65829 12.8536 7.85355L8.85355 11.8536C8.65829 12.0488 8.34171 12.0488 8.14645 11.8536C7.95118 11.6583 7.95118 11.3417 8.14645 11.1464L11.2929 8H2.5C2.22386 8 2 7.77614 2 7.5C2 7.22386 2.22386 7 2.5 7H11.2929L8.14645 3.85355C7.95118 3.65829 7.95118 3.34171 8.14645 3.14645Z"
                    fill="currentColor"
                />
            </svg>

            <!-- spinner -->
            <svg
                id="spinnerIcon"
                class="hidden h-5 w-5 animate-spin text-black"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                />
            </svg>
        </div>
    </div>
</button>

    </form>
<!-- final result -->
    @isset($results)

    @if (isset($results['rating']))
    <div class="flex items-center mb-5">
            <!-- color grading based on rating -->
    @php
        $rating = $results['rating']['rating'] ?? null;
        if ($rating > 3) {
            $bgClass = 'bg-green-100';
            $textClass = 'text-green-900';
        } elseif ($rating < 3) {
            $bgClass = 'bg-red-100';
            $textClass = 'text-red-900';
        } else {
            $bgClass = 'bg-yellow-100';
            $textClass = 'text-yellow-900';
        }
    @endphp
    <p class="text-3xl font-bold inline-flex p-3 mr-3 {{ $bgClass }} {{ $textClass }} rounded-lg items-end ">{{ $results['rating']['rating'] }}<span class=" font-medium text-lg ml-1.5">/5</span></p>
    <p class="ms-2 font-bold text-3xl {{ $textClass }}">{{ $results['rating']['label'] }}</p>
    <span class="w-1 h-1 mx-2 rounded-full bg-neutral-quaternary"></span>
</div>
@endif

        <h2 class="text-xl font-semibold mb-2">Details</h2>
<!-- results loop -->
        <div class="space-y-3">
            @foreach ($results['providers'] as $service => $result)
                <div class="p-4 border rounded">
                    <strong>{{ $service }}</strong><br>
                    <div class="mt-2">
                        @if (isset($result['safe']) && $result['safe'] === true)
                            <span class="text-green-600 font-semibold">The URL is safe.</span>
                        @elseif (isset($result['safe']) && $result['safe'] === false)
                            <span class="text-red-600 font-semibold">The URL is unsafe!</span>
                        @else
                        <span class="text-yellow-600 font-semibold">No definitive result.</span>
                        @endif
                        <br>
                        <button class="ms-auto text-sm font-medium text-fg-brand hover:underline toggle-details transition-all duration-200 ease-in-out text-gray" data-target="details-{{ $loop->index }}">See detailed results</button>
        </div>
        <!-- raw results -->
                                    <div id="details-{{ $loop->index }}" class="p-4 border rounded my-2 bg-lightgray hidden">
        <pre class="text-sm whitespace-pre-wrap overflow-scroll max-h-96">{{ json_encode($result, JSON_PRETTY_PRINT) }}</pre>
    </div>
                </div>
            @endforeach
        </div>
    @endisset

    <!-- responsability disclaimer and credits -->
    <p class="text-sm text-neutral-500 text-center mt-6">This tool is not a substitute for professional security analysis.
    Results depend entirely on third-party services and should be treated as advisory only.</p>
    <p class="text-sm text-neutral-700 text-center mt-2">✨ Made by <a href="https://github.com/cerkeira" class="underline hover:text-amber-300">cerkeira<img src="/github-mark.svg" alt="GitHub" class="w-4 h-4 inline ml-1"></a> ✨</p>
</div>

</body>
</html>
<script>
    const form = document.querySelector('form');
    const button = document.getElementById('checkBtn');
    const text = document.getElementById('checkBtnText');
    const arrow = document.getElementById('arrowIcon');
    const spinner = document.getElementById('spinnerIcon');

    // change check url button state to checking
    form.addEventListener('submit', () => {
        button.disabled = true;

        text.textContent = 'Checking...';

        arrow.classList.add('hidden');
        spinner.classList.remove('hidden');

        button.classList.remove('group');
        button.setAttribute('aria-busy', 'true');
    });

    window.addEventListener('pageshow', function (event) {
        // if page was restored from back/forward cache
        if (event.persisted) {
            if (!button) return;

            // reset button
            button.disabled = false;
            button.classList.add('group');
            text.textContent = 'Check URL';

            spinner.classList.add('hidden');
            arrow.classList.remove('hidden');
        }
    });

// hide detailed results toggle
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-details').forEach(toggle => {
        toggle.addEventListener('click', () => {

            const target = document.getElementById(toggle.dataset.target);

            if (!target) return;

            target.classList.toggle('hidden');
            toggle.textContent = target.classList.contains('hidden')
                ? 'See detailed results'
                : 'Hide detailed results';
        });
    });
});
</script>
