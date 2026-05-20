@php
    $flash = [];

    if (session('status')) {
        $flash[] = ['type' => 'success', 'message' => session('status')];
    }

    if (session('error')) {
        $flash[] = ['type' => 'error', 'message' => session('error')];
    }

    if ($errors->any()) {
        $flash[] = [
            'type' => 'error',
            'message' => $errors->all()[0],
            'delay' => 8,
        ];
    }
@endphp

@if (count($flash) > 0)
    <script>
        window.atlyFlash = @json($flash);
    </script>
@endif
