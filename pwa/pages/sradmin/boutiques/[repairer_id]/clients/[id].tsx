import React, {useState, useEffect, useContext} from 'react';
import Head from 'next/head';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import Box from '@mui/material/Box';
import {useRouter} from 'next/router';
import {CircularProgress} from '@mui/material';
import CustomerDetail from '@components/dashboard/customers/CustomerDetail';
import {Customer} from '@interfaces/Customer';
import {userResource} from '@resources/userResource';
import Error404 from '@pages/404';
import {DashboardRepairerContext} from '@contexts/DashboardRepairerContext';
import {NextPageWithLayout} from '@interfaces/NextPageWithLayout';

const CustomerShow: NextPageWithLayout = () => {
  const {repairer, repairerNotFound} = useContext(DashboardRepairerContext);
  const router = useRouter();
  const {id} = router.query;
  const [customer, setCustomer] = useState<Customer | null>(null);
  const [loading, setLoading] = useState<boolean>(false);

  useEffect(() => {
    const fetchCustomer = async () => {
      if (typeof id === 'string' && id.length > 0) {
        setLoading(true);
        const customerFetch: Customer = await userResource.getById(id);
        setCustomer(customerFetch);
        setLoading(false);
      }
    };
    if (id) {
      fetchCustomer();
    }
  }, [id]);

  if (repairerNotFound) {
    return <Error404 />;
  }

  return (
    <>
      <Head>
        <title>
          Client {customer?.firstName} {customer?.lastName} | Rustine Libre
        </title>
      </Head>
      <DashboardLayout />
      <Box
        component="main"
        sx={{marginLeft: '20%', marginRight: '5%', marginTop: '10px'}}>
        {loading && <CircularProgress />}
        {!loading && customer && repairer && (
          <CustomerDetail customer={customer} repairer={repairer} />
        )}
      </Box>
    </>
  );
};

export default CustomerShow;
