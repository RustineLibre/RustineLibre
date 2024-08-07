import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import React from 'react';
import Head from 'next/head';
import WebsiteLayout from '@components/layout/WebsiteLayout';

const JoinGroup: NextPageWithLayout = () => {
  return (
    <>
      <Head>
        <title>Rejoindre le collectif | Rustine Libre</title>
      </Head>
      <WebsiteLayout />
    </>
  );
};

export default JoinGroup;
