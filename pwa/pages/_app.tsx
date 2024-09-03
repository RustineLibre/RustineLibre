import Layout from '@components/common/Layout';
import {AppCacheProvider} from '@mui/material-nextjs/v14-pagesRouter';
import type {AppProps} from 'next/app';
import {ReactElement, Suspense} from 'react';
import '../styles/calendar.css';
import Analytics from '@components/layout/Analytics';
import {GoogleOAuthProvider} from '@react-oauth/google';
import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';

type AppPropsWithLayout = AppProps & {
  Component: NextPageWithLayout;
};

function MyApp({Component, pageProps, ...props}: AppPropsWithLayout) {
  const googleClientId = process.env.GOOGLE_CLIENT_ID;

  const getLayout = Component.getLayout ?? ((page: ReactElement) => page);

  return (
    <AppCacheProvider {...props}>
      <GoogleOAuthProvider
        clientId={typeof googleClientId === 'string' ? googleClientId : ''}>
        <Suspense>
          <Analytics />
        </Suspense>
        <Layout dehydratedState={pageProps.dehydratedState}>
          {getLayout(<Component {...pageProps} />)}
        </Layout>
      </GoogleOAuthProvider>
    </AppCacheProvider>
  );
}

export default MyApp;
