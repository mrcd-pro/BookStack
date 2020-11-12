<!DOCTYPE html>
<html class="@yield('body-class')">
<head>
    <title>{{ isset($pageTitle) ? $pageTitle . ' | ' : '' }}{{ setting('app-name') }}</title>

    <!-- Meta -->
    <meta name="viewport" content="width=device-width">
    <meta name="token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ baseUrl('/') }}">
    <meta charset="utf-8">

    <!-- Styles and Fonts -->
    <link rel="stylesheet" href="{{ versioned_asset('dist/styles.css') }}">
    <link rel="stylesheet" media="print" href="{{ versioned_asset('dist/print-styles.css') }}">

    <!-- Scripts -->
    <script src="{{ baseUrl('/translations') }}"></script>

    @yield('head')

    @include('partials/custom-styles')

    @include('partials.custom-head')
</head>
<body class="@yield('body-class')" ng-app="bookStack">

    @include('partials/notifications')

    <header id="header">
        <div class="container fluid">
            <div class="row">
                <div class="col-sm-6 col-md-6">
                    <a href="{{ baseUrl('/') }}" class="logo">
                        @if(setting('app-logo', '') !== 'none')
                            <img class="logo-image" src="{{ setting('app-logo', '') === '' ? baseUrl('/logo.png') : baseUrl(setting('app-logo', '')) }}" alt="Logo">
                        @endif
                        @if (setting('app-name-header'))
                            <span class="logo-text">{{ setting('app-name') }}</span>
                        @endif
                    </a>
                    <div class="links text-center">
                        <a href="{{ url('https://app.mrcd.pro/login') }}">@icon('login') Войти в Систему Mertago</a>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="float right">
                        <div class="header-search">
                            <form action="{{ baseUrl('/search') }}" method="GET" class="search-box">
                                <button id="header-search-box-button" type="submit">@icon('search') </button>
                                <input id="header-search-box-input" type="text" name="term" tabindex="2" placeholder="{{ trans('common.search') }}" value="{{ isset($searchTerm) ? $searchTerm : '' }}">
                            </form>
                        </div>
                        <div class="links text-center">
                            @if(userCanOnAny('view', \BookStack\Entities\Bookshelf::class) || userCan('bookshelf-view-own'))
                                <a href="{{ baseUrl('/shelves') }}">@icon('bookshelf'){{ trans('entities.shelves') }}</a>
                            @endif
                            <a href="{{ baseUrl('/books') }}">@icon('book'){{ trans('entities.books') }}</a>
                            @if(signedInUser() && userCan('settings-manage'))
                                <a href="{{ baseUrl('/settings') }}">@icon('settings'){{ trans('settings.settings') }}</a>
                            @endif
                            @if(signedInUser() && userCan('users-manage') && !userCan('settings-manage'))
                                <a href="{{ baseUrl('/settings/users') }}">@icon('users'){{ trans('settings.users') }}</a>
                            @endif
                            @if(!signedInUser())
                                @if(setting('registration-enabled', false))
                                    <a href="{{ baseUrl("/register") }}">@icon('new-user') {{ trans('auth.sign_up') }}</a>
                                @endif
                                <a href="{{ baseUrl('/login') }}">@icon('login') {{ trans('auth.log_in') }}</a>
                            @endif
                        </div>
                        @if(signedInUser())
                            @include('partials._header-dropdown', ['currentUser' => user()])
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="content" class="block">
        @yield('content')
    </section>

    <div back-to-top>
        <div class="inner">
            @icon('chevron-up') <span>{{ trans('common.back_to_top') }}</span>
        </div>
    </div>
@yield('bottom')
<script src="{{ versioned_asset('dist/app.js') }}"></script>
@yield('scripts')
</body>
</html>
