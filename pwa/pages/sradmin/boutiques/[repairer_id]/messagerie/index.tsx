import React, {useContext} from 'react';
import Head from 'next/head';
import {Typography} from '@mui/material';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import RepairerDiscussionList from '@components/messagerie/RepairerDiscussionList';
import Error404 from '@pages/404';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';

const DashboardMessages = () => {
  const {repairer, repairerNotFound} = useContext(DashboardRepairerContext);

  if (repairerNotFound) {
    return <Error404 />;
  }

  return (
    <>
      <Head>
        <title>Messagerie Réparateur | Rustine Libre</title>
      </Head>
      <DashboardLayout>
        <Typography variant="h3" py={2} pl={2}>
          Messages
        </Typography>
        {repairer && (
          <RepairerDiscussionList repairer={repairer} discussionGiven={null} />
        )}
      </DashboardLayout>
    </>
  );
};

export default DashboardMessages;
