import React, {useState, useEffect, useContext} from 'react';
import Head from 'next/head';
import {useRouter} from 'next/router';
import {discussionResource} from '@resources/discussionResource';
import {Box, Typography} from '@mui/material';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import RepairerMessagesContent from '@components/messagerie/RepairerMessagesContent';
import RepairerDiscussionList from '@components/messagerie/RepairerDiscussionList';
import {Discussion} from '@interfaces/Discussion';
import Error404 from '@pages/404';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';
import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';

const SrAdminMessagerie: NextPageWithLayout = () => {
  const {repairer, repairerNotFound} = useContext(DashboardRepairerContext);
  const router = useRouter();
  const {id} = router.query;
  const [discussion, setDiscussion] = useState<Discussion | null>(null);
  const [loading, setLoading] = useState<boolean>(false);

  const fetchDiscussion = async (id: string) => {
    setLoading(true);
    const response: Discussion = await discussionResource.getById(id);
    setDiscussion(response);
    setLoading(false);
  };

  useEffect(() => {
    if (id) {
      fetchDiscussion(id as string);
    }
  }, [id]); // eslint-disable-line react-hooks/exhaustive-deps

  if (repairerNotFound) {
    return <Error404 />;
  }

  return (
    <>
      <Head>
        <title>Messagerie Réparateur | Rustine Libre</title>
      </Head>
      <DashboardLayout>
        <Typography variant="h3" py={2}>
          Messages
        </Typography>
        {discussion && !loading && (
          <Box display="flex" gap={4}>
            {repairer && (
              <RepairerDiscussionList
                repairer={repairer}
                discussionGiven={discussion}
                display={{xs: 'none', md: 'block'}}
              />
            )}
            <RepairerMessagesContent
              discussion={discussion}
              loading={loading}
            />
          </Box>
        )}
      </DashboardLayout>
    </>
  );
};

export default SrAdminMessagerie;
