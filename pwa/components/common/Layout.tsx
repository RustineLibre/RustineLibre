import {ReactNode, useState} from 'react';
import {
  DehydratedState,
  Hydrate,
  QueryClient,
  QueryClientProvider,
} from 'react-query';
import theme from '../../styles/theme';
import CssBaseline from '@mui/material/CssBaseline';
import {A2HS} from '@components/banner/A2HS';
import {A2HSIOS} from '@components/banner/A2HSIOS';
import {ThemeProvider} from '@mui/material/styles';
import dynamic from 'next/dynamic';
import {AuthProvider} from '@contexts/AuthContext';
import {SearchRepairerProvider} from '@contexts/SearchRepairerContext';
import {UserFormProvider} from '@contexts/UserFormContext';
import {DashboardRepairerProvider} from '@contexts/DashboardRepairerContext';

const Notifications = dynamic(
  () => import('@components/notifications/Notifications'),
  {
    ssr: false,
  }
);

const Layout = ({
  children,
  dehydratedState,
}: {
  children: ReactNode;
  dehydratedState: DehydratedState;
}) => {
  const [queryClient] = useState(() => new QueryClient());

  return (
    <QueryClientProvider client={queryClient}>
      <Hydrate state={dehydratedState}>
        <AuthProvider>
          <ThemeProvider theme={theme}>
            <CssBaseline />
            <A2HS />
            <A2HSIOS />
            <Notifications />
            <SearchRepairerProvider>
              <DashboardRepairerProvider>
                <UserFormProvider>{children}</UserFormProvider>
              </DashboardRepairerProvider>
            </SearchRepairerProvider>
          </ThemeProvider>
        </AuthProvider>
      </Hydrate>
    </QueryClientProvider>
  );
};

export default Layout;
