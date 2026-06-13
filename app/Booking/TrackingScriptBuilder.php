<?php

namespace App\Booking;

use App\Models\Hotel;
use Illuminate\Support\HtmlString;

/**
 * Builds the per-tenant tracking tags that are injected into the booking flow.
 * Each hotel configures its own GA4 / Google Ads / Meta Pixel / GTM ids, so the
 * conversion data lands in the hotel's own ad accounts ("tracking sovereignty"),
 * while the platform additionally keeps a first-party copy in booking_events for
 * server-to-server conversion replays.
 */
class TrackingScriptBuilder
{
    public function __construct(private readonly Hotel $hotel) {}

    private function config(string $key): ?string
    {
        $value = $this->hotel->tracking[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /** Tags for the document head: GTM / gtag base loaders and Meta Pixel. */
    public function headTags(): HtmlString
    {
        $out = '';
        $gtm = $this->config('gtm_id');
        $ga4 = $this->config('ga4_id');
        $ads = $this->config('google_ads_id');

        if ($gtm) {
            $id = e($gtm);
            $out .= "<!-- Google Tag Manager -->\n<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$id}');</script>\n";
        }

        $gtagId = $ga4 ?: $ads;
        if ($gtagId) {
            $loader = e($gtagId);
            $out .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$loader}\"></script>\n";
            $out .= "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());";
            if ($ga4) {
                $out .= "gtag('config','".e($ga4)."');";
            }
            if ($ads) {
                $out .= "gtag('config','".e($ads)."');";
            }
            $out .= "</script>\n";
        }

        $pixel = $this->config('meta_pixel_id');
        if ($pixel) {
            $p = e($pixel);
            $out .= "<!-- Meta Pixel -->\n<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{$p}');fbq('track','PageView');</script>\n";
        }

        return new HtmlString($out);
    }

    /**
     * Config object handed to the booking flow JS so it can fire the right
     * conversion events client-side. Server-side events are sent regardless.
     *
     * @return array<string,mixed>
     */
    public function clientConfig(): array
    {
        return [
            'ga4' => $this->config('ga4_id'),
            'googleAds' => $this->config('google_ads_id'),
            'googleAdsLabel' => $this->config('google_ads_label'),
            'metaPixel' => $this->config('meta_pixel_id'),
            'enhancedConversions' => (bool) ($this->hotel->tracking['enhanced_conversions'] ?? false),
        ];
    }
}
