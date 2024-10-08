import React, {ChangeEvent, useEffect, useState} from 'react';
import {
  Paper,
  Table,
  TableHead,
  TableRow,
  TableCell,
  TableBody,
  TableContainer,
  CircularProgress,
  TextField,
  IconButton,
  Button,
} from '@mui/material';
import {customerResource} from '@resources/customerResource';
import Link from 'next/link';
import RemoveRedEyeIcon from '@mui/icons-material/RemoveRedEye';
import Box from '@mui/material/Box';
import Pagination from '@mui/material/Pagination';
import Stack from '@mui/material/Stack';
import {Customer} from '@interfaces/Customer';
import SearchIcon from '@mui/icons-material/Search';
import {InputAdornment} from '@mui/material';
import {useAccount} from '@contexts/AuthContext';
import {downloadFile} from '@utils/downloadFileLink';
import {Repairer} from '@interfaces/Repairer';

interface CustomersListProps {
  repairer: Repairer;
}

export const CustomersList = ({repairer}: CustomersListProps): JSX.Element => {
  const [loadingList, setLoadingList] = useState<boolean>(false);
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [totalPages, setTotalPages] = useState<number>(0);
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [searchTerm, setSearchTerm] = useState<string>('');
  const {user} = useAccount({});

  const downloadCsv = async () =>
    await customerResource
      .exportCustomerCollectionCsv(repairer.id ?? null)
      .then((response: Response) =>
        downloadFile(response, 'customer_collection')
      );

  const fetchCustomers = async () => {
    setLoadingList(true);
    let params = {
      page: `${currentPage ?? 1}`,
      itemsPerPage: 30,
      'order[id]': 'DESC',
    };

    if ('' !== searchTerm) {
      params = {...{userSearch: searchTerm}, ...params};
      params.page = '1';
    }

    const response = await customerResource.getAllByRepairer(repairer, params);
    setCustomers(response['hydra:member']);
    setTotalPages(Math.ceil(response['hydra:totalItems'] / 30));
    setLoadingList(false);
  };

  useEffect(() => {
    fetchCustomers();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  useEffect((): void => {
    fetchCustomers();
  }, [currentPage]); // eslint-disable-line react-hooks/exhaustive-deps

  const handlePageChange = (event: ChangeEvent<unknown>, page: number) => {
    setCurrentPage(page);
  };

  const handleSearchTermChange = (
    event: React.ChangeEvent<HTMLInputElement>
  ) => {
    setSearchTerm(event.target.value);
  };

  const handleKeyPress = (event: React.KeyboardEvent<HTMLDivElement>) => {
    if (event.key === 'Enter') {
      fetchCustomers();
    }
  };

  return (
    <Box>
      <TextField
        label="Chercher..."
        value={searchTerm}
        onChange={handleSearchTermChange}
        onKeyPress={handleKeyPress}
        inputProps={{maxLength: 180}}
        InputProps={{
          endAdornment: (
            <InputAdornment position="end">
              <SearchIcon />
            </InputAdornment>
          ),
        }}
      />{' '}
      <Button onClick={downloadCsv} variant="contained" sx={{float: 'right'}}>
        Télécharger au format CSV
      </Button>
      {loadingList && (
        <Box display="flex" justifyContent="center" my={2}>
          <CircularProgress />
        </Box>
      )}
      <TableContainer elevation={4} component={Paper} sx={{marginTop: '10px'}}>
        <Table aria-label="employees">
          <TableHead
            sx={{
              '& th': {
                fontWeight: 'bold',
                color: 'primary.main',
              },
            }}>
            <TableRow>
              <TableCell align="left">Nom</TableCell>
              <TableCell align="left">Prénom</TableCell>
              <TableCell align="left">Email</TableCell>
              <TableCell align="left">Téléphone</TableCell>
              <TableCell align="left"></TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {customers.map((customer) => (
              <TableRow
                key={customer.id}
                sx={{
                  '&:last-child td, &:last-child th': {border: 0},
                }}>
                <TableCell align="left" component="th" scope="row">
                  {customer.lastName}
                </TableCell>
                <TableCell align="left">{customer.firstName}</TableCell>
                <TableCell align="left">{customer.email}</TableCell>
                <TableCell align="left">{customer.telephone}</TableCell>
                <TableCell align="right">
                  <Link
                    href={`/sradmin/boutiques/${repairer.id}/clients/${customer.id}`}
                    legacyBehavior
                    passHref>
                    <IconButton color="secondary">
                      <RemoveRedEyeIcon />
                    </IconButton>
                  </Link>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>
      {totalPages > 1 && (
        <Stack spacing={2} sx={{marginTop: '20px'}}>
          <Pagination
            count={totalPages}
            page={currentPage}
            onChange={handlePageChange}
            color="primary"
            showFirstButton
            showLastButton
            size="large"
          />
        </Stack>
      )}
    </Box>
  );
};

export default CustomersList;
