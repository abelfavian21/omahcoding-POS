<!DOCTYPE html>
<html lang="id" class="dark scroll-smooth">

<head>
    @include('landing.partials.head')
    @include('landing.partials.styles')
</head>

<body class="font-sans antialiased overflow-x-hidden">

    @include('landing.partials.header')

    <main>
        @include('landing.partials.hero')
        @include('landing.partials.marquee')
        @include('landing.partials.preview')
        @include('landing.partials.features')
        @include('landing.partials.partners')
        @include('landing.partials.testimonials')
        @include('landing.partials.how-it-works')
        @include('landing.partials.auth.index')
        @include('landing.partials.faq')
        @include('landing.partials.cta')
    </main>

    @include('landing.partials.footer')

    @include('landing.partials.scripts.motion')
    @include('landing.partials.scripts.auth')

    @if (config('chat.enabled'))
        @include('landing.chat-widget')
    @endif
</body>

</html>
