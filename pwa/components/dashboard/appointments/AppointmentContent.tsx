import React, {useState} from "react";
import Box from '@mui/material/Box';
import Button from "@mui/material/Button";
import {CircularProgress} from "@mui/material";
import {appointmentResource} from "@resources/appointmentResource";
import {Appointment} from "@interfaces/Appointment";
import {formatDate, isPast} from "@helpers/dateHelper";
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListItemText from '@mui/material/ListItemText';
import Grid from '@mui/material/Grid';
import {apiImageUrl} from "@helpers/apiImagesHelper";
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import Link from "next/link";
import Typography from "@mui/material/Typography";
import AlternateEmailIcon from "@mui/icons-material/AlternateEmail";
import CheckCircleOutlineIcon from "@mui/icons-material/CheckCircleOutline";
import BuildIcon from "@mui/icons-material/Build";
import {getAppointmentStatus} from "@helpers/appointmentStatus";
import {openingHoursResource} from "@resources/openingHours";
import useMediaQuery from "@hooks/useMediaQuery";
import InputLabel from '@mui/material/InputLabel';
import MenuItem from '@mui/material/MenuItem';
import FormControl from '@mui/material/FormControl';
import Select, { SelectChangeEvent } from '@mui/material/Select';

type AppointmentContentProps = {
    appointment: Appointment;
    handleCloseModal: (refresh: boolean|undefined) => void
};

const AppointmentContent = ({appointment, handleCloseModal}: AppointmentContentProps): JSX.Element => {

    const [loadingNewSlot, setLoadingNewSlot] = useState<boolean>(false);
    const [loadingDelete, setLoadingDelete] = useState<boolean>(false);
    const [proposeOtherSlot, setProposeOtherSlot] = useState<boolean>(false);
    const [slotsAvailable, setSlotsAvailable] = useState<any>(null);
    const isMobile = useMediaQuery('(max-width: 640px)');
    const [dates, setDates] = useState<string[]>([]);
    const [times, setTimes] = useState<string[]>([]);
    const [selectedDate, setSelectedDate] = useState<string|undefined>(undefined);
    const [selectedTime, setSelectedTime] = useState<string|undefined>('');


    const cancelAppointment = async () => {
        if (!appointment) {
            return;
        }

        setLoadingDelete(true);
        await appointmentResource.updateAppointmentStatus(appointment.id, {
            'transition': 'cancellation'
        });
        setLoadingDelete(false);
        handleCloseModal(true);
    }

    const handleClickProposeOtherSlot = async () => {
        setProposeOtherSlot(true);
        const slots = await openingHoursResource.getRepairerSlotsAvailable(appointment.repairer.id);
        setSlotsAvailable(slots);
        const dates = Object.keys(slots);
        setDates(dates);
    }

    const handleDateChange = (event: SelectChangeEvent) => {
        const newDateSelected = event.target.value as string;
        setSelectedDate(newDateSelected);
        const timesAvailable = slotsAvailable[newDateSelected]
        setTimes(timesAvailable)
    };

    const handleTimeChange = (event: SelectChangeEvent) => {
        setSelectedTime(event.target.value as string);
    };

    const sendNewSlot = async() => {
        if (!selectedDate || !selectedTime) {
            return;
        }

        setLoadingNewSlot(true);
        await appointmentResource.updateAppointmentStatus(appointment.id, {
            transition: 'propose_another_slot',
            slotTime: `${selectedDate} ${selectedTime}`
        });
        setLoadingNewSlot(false);
        handleCloseModal(true);
    };

    return (
        <Box>
            <Typography id="appointment_title" fontSize={20} fontWeight={600}>
                Rendez-vous : {`${appointment.customer.firstName} ${appointment.customer.lastName}`}
            </Typography>
            <List>
                <ListItem>
                    <ListItemIcon>
                        <CalendarMonthIcon />
                    </ListItemIcon>
                    <ListItemText
                        primary={formatDate(appointment.slotTime)}
                    />
                </ListItem>
                <ListItem>
                    <ListItemIcon>
                        <AlternateEmailIcon />
                    </ListItemIcon>
                    <ListItemText
                        primary={appointment.customer.email}
                    />
                </ListItem>
                <ListItem>
                    <ListItemIcon>
                        <CheckCircleOutlineIcon />
                    </ListItemIcon>
                    <ListItemText
                        primary={getAppointmentStatus(appointment.status)}
                    />
                </ListItem>
                {appointment.autoDiagnostic &&
                    <ListItem>
                        <ListItemIcon>
                            <BuildIcon />
                        </ListItemIcon>
                        <ListItemText
                            primary={appointment.autoDiagnostic.prestation}
                        />
                    </ListItem>
                }
                {appointment.autoDiagnostic && appointment.autoDiagnostic.photo &&
                    <img style={{marginTop: '20px', marginLeft: isMobile ? '10%': '20%'}} width={isMobile ? '200' : '300'} src={apiImageUrl(appointment.autoDiagnostic.photo.contentUrl)} alt="Photo autodiag" />
                }
            </List>

            <Grid container spacing={2}>
                <Grid item xs={6}>
                    {appointment.bike ?
                        <Link href={`/sradmin/clients/velos/${appointment.bike.id}`}>
                            <Button variant="outlined">
                                Voir le carnet du vélo
                            </Button>
                        </Link> : <Button disabled variant="outlined">
                            Voir le carnet du vélo
                        </Button>}
                </Grid>
                <Grid item xs={6}>
                    <Button variant="outlined">
                        Envoyer un message
                    </Button>
                </Grid>

                <Grid item xs={6}>
                    {(isPast(appointment.slotTime) || appointment.status === 'cancel' || appointment.status === 'refused') ?
                        <Button disabled variant="outlined">
                            Modifier le rendez-vous
                        </Button> :
                        <Button variant="outlined" sx={{color: 'green'}} onClick={handleClickProposeOtherSlot}>
                            Modifier le rendez-vous
                        </Button>
                    }
                </Grid>
                <Grid item xs={6}>
                    {(isPast(appointment.slotTime) || appointment.status === 'cancel' || appointment.status === 'refused') ?
                        <Button variant="outlined" disabled>
                            Annuler le rendez-vous
                        </Button>:
                        <Button variant="outlined" sx={{color: 'red'}} onClick={cancelAppointment}>
                            {!loadingDelete ? 'Annuler le rendez-vous' : <CircularProgress />}
                        </Button>
                    }
                </Grid>

                {
                    proposeOtherSlot &&
                    <Grid container spacing={2} sx={{mt: 3}}>
                        <Grid item xs={6}>
                            <FormControl fullWidth>
                                <InputLabel id="label_select_jour">Jour</InputLabel>
                                <Select
                                    labelId="select_jour"
                                    id="select_jour"
                                    value={selectedDate}
                                    label="Jour"
                                    onChange={handleDateChange}
                                >
                                    {dates.map((date) => (
                                        <MenuItem key={date} value={date}>{date}</MenuItem>
                                    ))}
                                </Select>
                            </FormControl>
                        </Grid>
                        <Grid item xs={6}>
                            {selectedDate &&
                                <FormControl fullWidth>
                                    <InputLabel id="label_select_heure">Heure</InputLabel>
                                    <Select
                                        labelId="select_time"
                                        id="select_time"
                                        value={selectedTime}
                                        label="Heure"
                                        onChange={handleTimeChange}
                                    >
                                        {times.map((time) => (
                                            <MenuItem key={time} value={time}>{time}</MenuItem>
                                        ))}
                                    </Select>
                                </FormControl>
                            }
                        </Grid>
                        <Grid item xs={12}>
                            <Button variant="outlined" onClick={sendNewSlot}>
                                {loadingNewSlot ? <CircularProgress /> : 'Envoyer la nouvelle proposition'}
                            </Button>
                        </Grid>
                    </Grid>
                }
            </Grid>
        </Box>
    )
}

export default AppointmentContent;