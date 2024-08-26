import Layout from '@components/common/Layout';
import {AppCacheProvider} from '@mui/material-nextjs/v14-pagesRouter';
import type {AppProps} from 'next/app';
import type {DehydratedState} from 'react-query';
import {Suspense} from 'react';
import '../styles/calendar.css';
import Analytics from '@components/layout/Analytics';
import {GoogleOAuthProvider} from '@react-oauth/google';

function MyApp({
  Component,
  pageProps,
  ...props
}: AppProps<{dehydratedState: DehydratedState}>) {
  const googleClientId = process.env.GOOGLE_CLIENT_ID;

  return (
    <AppCacheProvider {...props}>
      <GoogleOAuthProvider
        clientId={typeof googleClientId === 'string' ? googleClientId : ''}>
        <Suspense>
          <Analytics />
        </Suspense>
        <Layout dehydratedState={pageProps.dehydratedState}>
          <Component {...pageProps} />
        </Layout>
      </GoogleOAuthProvider>
    </AppCacheProvider>
  );
}

export default MyApp;
