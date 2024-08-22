import Layout from '@components/common/Layout';
import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import type {AppProps} from 'next/app';
import type {DehydratedState} from 'react-query';
import Head from 'next/head';
import {Suspense} from 'react';
import '../styles/calendar.css';
import Analytics from '@components/layout/Analytics';
import {GoogleOAuthProvider} from '@react-oauth/google';

type AppPropsWithLayout<T> = AppProps<T> & {
  Component: NextPageWithLayout<T>;
};

function MyApp({
  Component,
  pageProps,
}: AppPropsWithLayout<{dehydratedState: DehydratedState}>) {
  const getLayout = Component.getLayout ?? ((page) => page);
  const googleClientId = process.env.GOOGLE_CLIENT_ID;

  return getLayout(
    <>
      <Head>
        <meta
          name="viewport"
          content="minimum-scale=1, initial-scale=1, width=device-width, shrink-to-fit=no, user-scalable=no, viewport-fit=cover"
        />
        <meta name="application-name" content="Rustine Libre" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="apple-mobile-web-app-title" content="Rustine Libre" />
        <meta name="description" content="Rustine Libre" />
        <meta name="format-detection" content="telephone=no" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="msapplication-config" content="/icons/browserconfig.xml" />
        <meta name="msapplication-TileColor" content="#2B5797" />
        <meta name="msapplication-tap-highlight" content="no" />
        <meta name="theme-color" content="#000000" />
        <meta property="og:type" content="product" />
        <meta property="og:title" content="Rustine Libre" />
        <meta
          property="og:description"
          content="Rustine Libre, la réparation de vos vélos sur Lille Métropole"
        />
        <meta property="og:url" content="https://rustinelibre.fr" />
        <meta property="og:image" content="img/og-image.webp" />
        <meta property="og:site_name" content="Rustine Libre" />
        <link
          rel="manifest"
          href="/manifest.json"
          crossOrigin="use-credentials"></link>
        <link
          rel="apple-touch-icon"
          href="/pwa_icons/apple-icon-180.png"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2048-2732.jpg"
          media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2732-2048.jpg"
          media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1668-2388.jpg"
          media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2388-1668.jpg"
          media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1536-2048.jpg"
          media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2048-1536.jpg"
          media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1668-2224.jpg"
          media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2224-1668.jpg"
          media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1620-2160.jpg"
          media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2160-1620.jpg"
          media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1290-2796.jpg"
          media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2796-1290.jpg"
          media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1179-2556.jpg"
          media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2556-1179.jpg"
          media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1284-2778.jpg"
          media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2778-1284.jpg"
          media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1170-2532.jpg"
          media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2532-1170.jpg"
          media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1125-2436.jpg"
          media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2436-1125.jpg"
          media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1242-2688.jpg"
          media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2688-1242.jpg"
          media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-828-1792.jpg"
          media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1792-828.jpg"
          media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1242-2208.jpg"
          media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-2208-1242.jpg"
          media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-750-1334.jpg"
          media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1334-750.jpg"
          media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-640-1136.jpg"
          media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"></link>
        <link
          rel="apple-touch-startup-image"
          href="/pwa_icons/apple-splash-1136-640.jpg"
          media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)"></link>
      </Head>
      <GoogleOAuthProvider
        clientId={typeof googleClientId === 'string' ? googleClientId : ''}>
        <Suspense>
          <Analytics />
        </Suspense>
        <Layout dehydratedState={pageProps.dehydratedState}>
          <Component {...pageProps} />
        </Layout>
      </GoogleOAuthProvider>
    </>
  );
}

export default MyApp;
