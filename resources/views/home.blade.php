<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>URL Safety Checker</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

<div class="max-w-xl mx-auto mt-20 p-6 bg-white shadow rounded">
    <h1 class="text-2xl font-bold mb-4">URLRiot</h1>

    <form action="{{ route('check') }}" method="POST" class="mb-6">
        @csrf
        <input
            type="text"
            name="url"
            placeholder="Paste a URL..."
            class="w-full p-3 border rounded"
            value="{{ old('url', $inputUrl ?? '') }}"
            required
        >
        @error('url')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror

        <button
            class="mt-4 w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700"
        >
            Check URL
        </button>
    </form>

    @isset($results)

    @if (isset($results['rating']))
    <div class="flex items-center mb-5">
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
    <p class="text-3xl font-bold inline-flex p-3 mr-3 {{ $bgClass }} {{ $textClass }} rounded-md items-end ">{{ $results['rating']['rating'] }}<span class=" font-medium text-lg ml-1.5">/5</span></p>
    <p class="ms-2 font-bold text-3xl {{ $textClass }}">{{ $results['rating']['label'] }}</p>
    <span class="w-1 h-1 mx-2 rounded-full bg-neutral-quaternary"></span>
</div>
@endif

        <h2 class="text-xl font-semibold mb-2">Details</h2>

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
                                    <div id="details-{{ $loop->index }}" class="p-4 border rounded my-2 bg-lightgray hidden">
        <pre class="text-sm whitespace-pre-wrap overflow-x-scroll">{{ json_encode($result, JSON_PRETTY_PRINT) }}</pre>
    </div>
                </div>
            @endforeach
        </div>
    @endisset
</div>

</body>
</html>
<script>
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
