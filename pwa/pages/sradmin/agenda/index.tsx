import React, {useEffect, useState} from 'react';
import Head from 'next/head';
import {useAccount} from '@contexts/AuthContext';
import {Box} from '@mui/material';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import {Repairer} from '@interfaces/Repairer';
import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';
import AgendaCalendar from '@components/dashboard/agenda/AgendaCalendar';

const Agenda: NextPageWithLayout = () => {
  const [repairer, setRepairer] = useState<Repairer | null>(null);
  const {user} = useAccount({});

  useEffect(() => {
    if (user && user.repairer) {
      setRepairer(user.repairer);
      return;
    }
    if (user && user.repairerEmployee) {
      setRepairer(user.repairerEmployee.repairer);
    }
  }, [user]); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <>
      <Head>
        <title>Agenda | Rustine Libre</title>
      </Head>
      <DashboardLayout>
        <Box component="main" maxWidth="1200">
          {repairer && <AgendaCalendar repairer={repairer} />}
        </Box>
      </DashboardLayout>
    </>
  );
};

export default Agenda;
