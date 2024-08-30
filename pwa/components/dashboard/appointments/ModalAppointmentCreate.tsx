import React, {ChangeEvent, useEffect, useState} from 'react';
import router from 'next/router';
import dynamic from 'next/dynamic';
import dayjs, {Dayjs} from 'dayjs';
import 'dayjs/locale/fr';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';
import {customerResource} from '@resources/customerResource';
import {appointmentResource} from '@resources/appointmentResource';
import {autoDiagnosticResource} from '@resources/autoDiagResource';
import {
  Box,
  TextField,
  Autocomplete,
  Button,
  Typography,
  Alert,
  CircularProgress,
  InputLabel,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  IconButton,
  useMediaQuery,
  Select,
  MenuItem,
  FormControl,
  SelectChangeEvent,
} from '@mui/material';
import {DatePicker, TimePicker} from '@mui/x-date-pickers';
import {frFR} from '@mui/x-date-pickers/locales';
import {LocalizationProvider} from '@mui/x-date-pickers/LocalizationProvider';
import {AdapterDayjs} from '@mui/x-date-pickers/AdapterDayjs';
import AppointmentCreateAddPhoto from '@components/dashboard/appointments/AppointmentCreateAddPhoto';
import AppointmentCreateAddPrestation from '@components/dashboard/appointments/AppointmentCreateAddPrestation';
import AppointmentCreateAddBikeType from '@components/dashboard/appointments/AppointmentCreateAddBikeType';
import AppointmentCreateAddComment from './AppointmentCreateAddComment';
import {useTheme} from '@mui/material/styles';
import {padNumber} from '@helpers/dateHelper';
import {isRepairerItinerant} from '@helpers/rolesHelpers';
import {RequestBody} from '@interfaces/Resource';
import {Customer} from '@interfaces/Customer';
import {User} from '@interfaces/User';
import {Repairer} from '@interfaces/Repairer';
import {BikeType} from '@interfaces/BikeType';
import {MediaObject} from '@interfaces/MediaObject';
import {Appointment} from '@interfaces/Appointment';
import CloseIcon from '@mui/icons-material/Close';
import {errorRegex} from '@utils/errorRegex';

const PinMap = dynamic(() => import('@components/rendez-vous/PinMap'), {
  ssr: false,
});

interface AppointmentCreateProps {
  repairer: Repairer;
  appointmentSelectedDate: string | null;
  openModal: boolean;
  handleCloseModal: (refresh: boolean) => void;
  handleRedirectToAgenda?: () => void;
}

const ModalAppointmentCreate = ({
  repairer,
  appointmentSelectedDate,
  openModal,
  handleCloseModal,
}: AppointmentCreateProps): JSX.Element => {
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [customerType, setCustomerType] = useState<string>('');
  const [customerInput, setCustomerInput] = useState<string>('');
  const [customerName, setCustomerName] = useState<string>('');
  const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(
    null
  );
  const [success, setSuccess] = useState<boolean>(false);
  const [slotSelected, setSlotSelected] = useState<string>();
  const selectedDate = appointmentSelectedDate ? appointmentSelectedDate : null;
  const [prestation, setPrestation] = useState<string>('');
  const [selectedBikeType, setSelectedBikeType] = useState<BikeType | null>(
    null
  );
  const [photo, setPhoto] = useState<MediaObject | null>(null);
  const [newAppointment, setNewAppointment] = useState<Appointment | null>(
    null
  );
  const [comment, setComment] = useState<string>('');
  const [loading, setLoading] = useState<boolean>(false);
  const [loadingWithoutDetails, setLoadingWithoutDetails] =
    useState<boolean>(false);
  const [details, setDetails] = useState<boolean>(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [pickedDate, setPickedDate] = React.useState<Dayjs | null>(null);
  const [pickedTime, setPickedTime] = React.useState<Dayjs | null>(null);
  const [latitude, setLatitude] = useState<string>('');
  const [longitude, setLongitude] = useState<string>('');
  const [address, setAddress] = useState<string>('');
  const isItinerantRepairer = isRepairerItinerant(repairer);
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));

  dayjs.extend(timezone);
  dayjs.extend(utc);
  const fetchCustomers = async () => {
    const response = await customerResource.getAllByRepairer(repairer, {
      userSearch: customerInput,
    });
    setCustomers(response['hydra:member']);
  };

  useEffect(() => {
    if (customerInput.length >= 2) {
      fetchCustomers();
    }
  }, [customerInput]); // eslint-disable-line react-hooks/exhaustive-deps

  useEffect(() => {
    if (selectedDate) {
      setSlotSelected(selectedDate);
      setPickedDate(dayjs(selectedDate));
      setPickedTime(dayjs(selectedDate));
    } else {
      if (pickedTime || pickedDate) {
        let newDate = `${pickedDate?.year()}-${pickedDate?.month()}-${pickedDate?.day()}T${pickedTime?.hour()}:${pickedTime?.minute()}:00`;
        setSlotSelected(newDate);
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedDate]);

  useEffect(() => {
    if (!details && newAppointment) {
      handleSuccess();
    }
  }, [details, newAppointment]); // eslint-disable-line react-hooks/exhaustive-deps

  const handleCustomerType = (event: SelectChangeEvent): void => {
    setSelectedCustomer(null);
    setCustomerName('');
    setCustomerInput('');
    setCustomerType(event.target.value);
  };

  const handleCustomerChange = async (
    event: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ): Promise<void> => {
    setCustomerInput(event.target.value);
  };

  const handleCustomerName = (
    event: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ): void => {
    setCustomerName(event.target.value);
  };

  const handleSelectCustomer = (customer: Customer): void => {
    setSelectedCustomer(customer);
  };

  const createAppointment = async (customer: Customer | string) => {
    const requestBody: RequestBody = {
      repairer: repairer['@id'],
      slotTime: slotSelected,
      customer: typeof customer !== 'string' ? customer['@id'] : null,
      customerName: typeof customer === 'string' ? customer : null,
    };
    if (isItinerantRepairer && address && latitude && longitude) {
      requestBody['address'] = address;
      requestBody['latitude'] = latitude.toString();
      requestBody['longitude'] = longitude.toString();
    }
    const appointment = await appointmentResource.post(requestBody);
    setNewAppointment(appointment);
    setLoading(false);
  };

  const handleCreateAppointment = async () => {
    if (null === selectedCustomer && !customerName) {
      setErrorMessage(
        'Vous devez sélectionner un compte client ou indiquer son nom et son prénom.'
      );
      return;
    }
    setLoading(true);
    try {
      await createAppointment(selectedCustomer ?? customerName);
      setDetails(true);
    } catch (e: any) {
      setErrorMessage(e.message?.replace(errorRegex, '$2'));
    }
    setLoading(false);
  };

  const handleCreateAppointmentWithoutDetails = async () => {
    if (!customerName && null === selectedCustomer) {
      setErrorMessage(
        'Vous devez sélectionner un compte client ou indiquer son nom et son prénom.'
      );
      return;
    }
    setLoadingWithoutDetails(true);
    try {
      await createAppointment(selectedCustomer ?? customerName);
      handleSuccess();
    } catch (e: any) {
      setErrorMessage(e.message?.replace(errorRegex, '$2'));
    }
    setLoadingWithoutDetails(false);
  };

  const handleAddInformations = async () => {
    setLoading(true);
    if (prestation || photo) {
      const requestBody: RequestBody = {
        appointment: newAppointment!['@id'],
      };
      if (prestation) {
        requestBody['prestation'] = prestation;
      }
      if (photo) {
        requestBody['photo'] = photo['@id'];
      }
      try {
        await autoDiagnosticResource.post(requestBody);
      } catch (e: any) {
        setErrorMessage(e.message?.replace(errorRegex, '$2'));
        setLoading(false);
        return;
      }
    }
    if (selectedBikeType || comment) {
      const putRequest: RequestBody = {};
      if (selectedBikeType) {
        putRequest['bikeType'] = selectedBikeType['@id'];
      }
      if (comment) {
        putRequest['comment'] = comment;
      }
      try {
        await appointmentResource.put(newAppointment!['@id'], putRequest);
      } catch (e: any) {
        setErrorMessage(e.message?.replace(errorRegex, '$2'));
        setLoading(false);
        return;
      }
    }
    if (!errorMessage) {
      handleSuccess();
    }
    setLoading(false);
  };

  const handleDatePicker = (newValue: dayjs.Dayjs | null) => {
    if (newValue && newValue.year() && newValue.month() && newValue.date()) {
      let month = padNumber(newValue.month() + 1);
      let day = padNumber(newValue.date());
      let newDate = `${newValue.year()}-${month}-${day}`;
      setPickedDate(dayjs(newDate));
      setSlotSelected(`${newDate}T${pickedTime?.format('HH:mm')}`);
    }
    if (
      !newValue ||
      isNaN(newValue.year()) ||
      isNaN(newValue.month()) ||
      isNaN(newValue.date())
    ) {
      setPickedDate(null);
    }
  };

  const handleTimePicker = (newValue: dayjs.Dayjs | null) => {
    if (newValue && newValue.hour() !== null && newValue.minute() !== null) {
      let hour = padNumber(newValue.hour());
      let minutes = padNumber(newValue.minute());
      let newTime = `${hour}:${minutes}:00`;
      setPickedTime(dayjs(`2023-01-01T${newTime}`));
      setSlotSelected(`${pickedDate?.format('YYYY-MM-DD')}T${newTime}`);
    }
    if (!newValue || isNaN(newValue.hour()) || isNaN(newValue.minute())) {
      setPickedTime(null);
    }
  };

  const cancelPinMap = () => {
    return;
  };

  const confirmPinMap = () => {
    return;
  };

  const handleClose = () => {
    setSelectedCustomer(null);
    setCustomers([]);
    setCustomerInput('');
    setNewAppointment(null);
    setSelectedBikeType(null);
    setPrestation('');
    setPhoto(null);
    setComment('');
    setPickedTime(null);
    setPickedDate(null);
    setSlotSelected(undefined);
    setAddress('');
    setLatitude('');
    setLongitude('');
    setErrorMessage(null);
    setLoading(false);
    setDetails(false);
    setSuccess(false);
    handleCloseModal(true);
  };

  const handleSuccess = () => {
    setErrorMessage(null);
    setSuccess(true);
    router.push(
      `/sradmin/boutiques/${repairer.id}/agenda?selectedDate=${slotSelected}`
    );
    setTimeout(async () => {
      handleClose();
    }, 3000);
  };

  return (
    <Dialog
      open={openModal}
      onClose={handleClose}
      fullScreen={isMobile}
      fullWidth={true}
      maxWidth="sm"
      aria-labelledby="Créer un rendez-vous"
      aria-describedby="popup_appointment_create">
      <DialogTitle
        sx={{
          m: 0,
          p: 2,
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
        }}>
        <Typography variant="h3" color="primary">
          Création de rendez-vous
        </Typography>
        <IconButton aria-label="close" color="primary" onClick={handleClose}>
          <CloseIcon fontSize="large" />
        </IconButton>
      </DialogTitle>
      <DialogContent dividers>
        {!address && (
          <Box>
            <LocalizationProvider
              localeText={
                frFR.components.MuiLocalizationProvider.defaultProps.localeText
              }
              adapterLocale="fr"
              dateAdapter={AdapterDayjs}>
              <InputLabel sx={{mb: 1}}>
                {selectedDate ? 'Modifier le créneau' : 'Choisir votre créneau'}
              </InputLabel>
              <Box
                sx={{
                  display: 'flex',
                  gap: 2,
                }}>
                <DatePicker
                  label="Jour *"
                  value={pickedDate}
                  disablePast
                  sx={{width: '50%'}}
                  defaultValue={dayjs(selectedDate)}
                  format="DD-MM-YYYY"
                  onChange={(newValue) => handleDatePicker(newValue)}
                />
                <TimePicker
                  label="Heure *"
                  value={pickedTime}
                  sx={{width: '50%'}}
                  defaultValue={dayjs.tz(selectedDate, 'Europe/Paris')}
                  format="HH:mm"
                  onChange={(newValue) => handleTimePicker(newValue)}
                />
              </Box>
            </LocalizationProvider>
            <FormControl fullWidth required sx={{mt: 2, mb: 1}}>
              <InputLabel id="repairer-type-label">Pour qui ?</InputLabel>
              <Select
                id="repairer-type"
                labelId="repairer-type-label"
                required
                label="Type de réparateur"
                onChange={handleCustomerType}
                value={customerType}
                style={{width: '100%'}}>
                <MenuItem disabled value="">
                  Pour qui ?
                </MenuItem>
                <MenuItem key="customerWithAccount" value="customerWithAccount">
                  Client avec un compte
                </MenuItem>
                <MenuItem
                  key="customerWithoutAccount"
                  value="customerWithoutAccount">
                  Client sans compte
                </MenuItem>
              </Select>
            </FormControl>
            {customerType === 'customerWithAccount' && (
              <Autocomplete
                disabled={'' !== customerName}
                freeSolo
                value={customerInput}
                options={customers}
                getOptionLabel={(customer) =>
                  typeof customer === 'string'
                    ? customer
                    : `${customer.firstName} ${customer.lastName} (${customer.email})`
                }
                onChange={(event, value) => handleSelectCustomer(value as User)}
                renderInput={(params) => (
                  <TextField
                    label="Compte client"
                    {...params}
                    value={customerInput}
                    onChange={(e) => handleCustomerChange(e)}
                  />
                )}
              />
            )}
            {customerType === 'customerWithoutAccount' && (
              <TextField
                disabled={null !== selectedCustomer}
                sx={{width: '100%'}}
                label="Nom / prénom du client"
                value={customerName}
                onChange={(e) => handleCustomerName(e)}
              />
            )}
          </Box>
        )}
        {(selectedCustomer || customerName) && pickedDate && pickedTime && (
          <>
            {isItinerantRepairer && (
              <PinMap
                repairer={repairer}
                latitude={latitude}
                longitude={longitude}
                cancelPinMap={cancelPinMap}
                confirmPinMap={confirmPinMap}
                setLatitude={setLatitude}
                setLongitude={setLongitude}
                address={address}
                setAddress={setAddress}
              />
            )}
          </>
        )}
        {details && (
          <>
            <Box
              display="flex"
              flexDirection={isMobile ? 'column' : 'row'}
              gap={isMobile ? 0 : 2}>
              <AppointmentCreateAddPrestation
                prestation={prestation}
                setPrestation={setPrestation}
              />
              <AppointmentCreateAddBikeType
                selectedBikeType={selectedBikeType}
                setSelectedBikeType={setSelectedBikeType}
              />
            </Box>
            <AppointmentCreateAddPhoto photo={photo} setPhoto={setPhoto} />
            <AppointmentCreateAddComment
              comment={comment}
              setComment={setComment}
            />
          </>
        )}
      </DialogContent>
      {(selectedCustomer || customerName) && (
        <>
          <DialogActions>
            {details ? (
              <Button
                onClick={handleAddInformations}
                variant="contained"
                startIcon={
                  loading && (
                    <CircularProgress size={18} sx={{color: 'white'}} />
                  )
                }>
                Valider rendez-vous
              </Button>
            ) : (
              <Box
                display="flex"
                flexDirection={isMobile ? 'column' : 'row'}
                p={2}
                gap={isMobile ? 0 : 2}>
                <Button
                  onClick={() => handleCreateAppointmentWithoutDetails()}
                  disabled={isItinerantRepairer! && !address}
                  size="medium"
                  variant="outlined"
                  startIcon={
                    loadingWithoutDetails && (
                      <CircularProgress size={18} sx={{color: 'primary'}} />
                    )
                  }>
                  Créer sans détails
                </Button>
                <Button
                  onClick={() => handleCreateAppointment()}
                  disabled={isItinerantRepairer! && !address}
                  size="medium"
                  variant="contained"
                  startIcon={
                    loading && (
                      <CircularProgress size={18} sx={{color: 'white'}} />
                    )
                  }>
                  Créer avec détails
                </Button>
              </Box>
            )}
          </DialogActions>
          <Box>
            {errorMessage && (
              <Alert severity="error" sx={{p: 2}}>
                {errorMessage}
              </Alert>
            )}
            {success && (
              <Alert severity="success" sx={{p: 2}}>
                Rendez-vous créé avec succès
              </Alert>
            )}
          </Box>
        </>
      )}
    </Dialog>
  );
};

export default ModalAppointmentCreate;
