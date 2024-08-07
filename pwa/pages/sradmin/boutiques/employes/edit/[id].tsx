import {NextPageWithLayout} from 'pages/_app';
import React, {useState, useEffect} from 'react';
import Head from 'next/head';
import DashboardLayout from '@components/dashboard/DashboardLayout';
import Box from '@mui/material/Box';
import {useRouter} from 'next/router';
import {RepairerEmployee} from '@interfaces/RepairerEmployee';
import {repairerEmployeesResource} from '@resources/repairerEmployeesResource';
import {CircularProgress} from '@mui/material';
import EmployeeForm from '@components/dashboard/employees/EmployeeForm';

const EditEmployee: NextPageWithLayout = () => {
  const router = useRouter();
  const {id} = router.query;
  const [repairerEmployee, setRepairerEmployee] =
    useState<RepairerEmployee | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);

  useEffect(() => {
    const fetchEmployee = async () => {
      if (typeof id === 'string' && id.length > 0) {
        setIsLoading(true);
        const employeeFetch: RepairerEmployee =
          await repairerEmployeesResource.getById(id);
        setRepairerEmployee(employeeFetch);
        setIsLoading(false);
      }
    };
    if (id) {
      fetchEmployee();
    }
  }, [id]);

  return (
    <>
      <Head>
        <title>Éditer un réparateur | Rustine Libre</title>
      </Head>
      <DashboardLayout />
      <Box
        component="main"
        sx={{marginLeft: '20%', marginRight: '5%', marginTop: '100px'}}>
        {isLoading ? (
          <CircularProgress />
        ) : (
          <EmployeeForm repairerEmployee={repairerEmployee} repairer={null} />
        )}
      </Box>
    </>
  );
};

export default EditEmployee;
