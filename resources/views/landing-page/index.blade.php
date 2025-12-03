@extends('landing-page.layouts.master')

@section('title', 'Home')

@section('content')

    <!-- Banner Section Start -->
    <section class="banner-section">
        <div class="container">
            @php
                $intro        = $introSection->value ?? [];
                $introImage   = $introSectionImage->value['background_image'] ?? null;
                $businessName = $business_name ?? '';
                $ctaValue     = $cta->value ?? [];
                $playStore    = $ctaValue['play_store'] ?? [];
                $appStore     = $ctaValue['app_store'] ?? [];
            @endphp

            <div
                class="banner-wrapper justify-content-between bg__img wow animate__fadeInDown"
                data-img="{{ $introImage
                    ? dynamicStorage('storage/app/public/business/landing-pages/intro-section/' . $introImage)
                    : dynamicAsset('public/landing-page/assets/img/banner/bg.png') }}"
            >
                <div class="banner-content">
                    <h1 class="title">
                        {{ !empty($intro['title'])
                            ? translate($intro['title'])
                            : translate("It’s Time to Change The Riding Experience") }}
                    </h1>

                    <p class="txt">
                        {{ !empty($intro['sub_title'])
                            ? translate($intro['sub_title'])
                            : translate("Embrace the future today and explore the amazing features that make ") .
                              ($businessName ? ' ' . $businessName : '') }}
                    </p>

                    <div class="app--btns d-flex flex-wrap">
                        <div class="dropdown">
                            <a href="#" class="cmn--btn h-50" data-bs-toggle="dropdown">
                                {{ translate('Download User App') }}
                            </a>
                            <div class="dropdown-menu dropdown-button-menu">
                                <ul>
                                    <li>
                                        <a href="{{ $playStore['user_download_link'] ?? '' }}">
                                            <img src="{{ dynamicAsset('public/landing-page/assets/img/play-fav.png') }}" alt="">
                                            <span>{{ translate('Play Store') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ $appStore['user_download_link'] ?? '' }}">
                                            <img src="{{ dynamicAsset('public/landing-page/assets/img/apple-fav.png') }}" alt="">
                                            <span>{{ translate('App Store') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        {{-- Extra buttons (driver app etc.) can be added here later --}}
                    </div>
                </div>

                {{-- Optional right-side hero image block could go here --}}
            </div>
        </div>
    </section>

    {{-- You can add more sections (features, how it works, etc.) below when you're ready --}}

@endsection
