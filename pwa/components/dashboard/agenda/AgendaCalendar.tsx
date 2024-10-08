import React, {useEffect, useRef, useState} from 'react';
import {Repairer} from '@interfaces/Repairer';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin, {DateClickArg} from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';
import {Appointment} from '@interfaces/Appointment';
import {appointmentResource} from '@resources/appointmentResource';
import {EventImpl} from '@fullcalendar/core/internal';
import ModalShowAppointment from '@components/dashboard/agenda/ModalShowAppointment';
import ModalAppointmentCreate from '@components/dashboard/appointments/ModalAppointmentCreate';
import {getEndAppointment} from '@helpers/endAppointmentHelper';
import router from 'next/router';
import {DatesSetArg} from '@fullcalendar/core';
import {Box} from '@mui/material';
import useMediaQuery from '@mui/material/useMediaQuery';
import {useTheme} from '@mui/material/styles';

interface AgendaCalendarProps {
  repairer: Repairer;
}

const AgendaCalendar = ({repairer}: AgendaCalendarProps): JSX.Element => {
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const [appointment, setAppointment] = useState<Appointment | null>(null);
  const [calendarEvents, setCalendarEvents] = useState<
    {title: string; id: string}[]
  >([]);
  const [openModal, setOpenModal] = useState<boolean>(false);
  const [startDate, setStartDate] = useState<string>(
    new Date(
      typeof router.query.selectedDate === 'string'
        ? router.query.selectedDate.split('T')[0]
        : Date.now()
    )
      .toISOString()
      .split('T')[0]
  );
  const [endDate, setEndDate] = useState<string>(
    typeof router.query.selectedDate === 'string'
      ? new Date(
          new Date(router.query.selectedDate.split('T')[0]).getDate() +
            3600 * 1000 * 24
        )
          .toISOString()
          .split('T')[0]
      : new Date(Date.now() + 3600 * 1000 * 24).toISOString().split('T')[0]
  );
  const [selectedDate, setSelectedDate] = useState<string>('');
  const [initialDate, setInitialDate] = useState<Date | null>(null);
  const [openModalCreateAppointment, setOpenModalCreateAppointment] =
    useState<boolean>(false);

  const handleCloseModal = (refresh = true): void => {
    setOpenModal(false);
    setAppointment(null);
    if (refresh) {
      buildCalendarEvents(startDate, endDate);
    }
  };

  const handleCloseModalCreateAppointment = (refresh: boolean = true) => {
    setOpenModalCreateAppointment(false);
    setSelectedDate('');
    if (refresh) {
      buildCalendarEvents(startDate, endDate);
    }
  };

  useEffect(() => {
    const dateQueryUrl = router.query.selectedDate;
    if (dateQueryUrl) {
      if (typeof dateQueryUrl === 'string') {
        setInitialDate(new Date(dateQueryUrl.split('T')[0]));
      }
    } else {
      setInitialDate(new Date());
    }
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  const buildCalendarEvents = async (start: string, end: string) => {
    const appointmentsFetch = await appointmentResource.getAllByRepairer(
      repairer,
      {
        'slotTime[after]': start,
        'slotTime[before]': end,
      }
    );
    const allAppointments = appointmentsFetch['hydra:member'];

    const statusValues = ['validated', 'pending_repairer'];
    const appointments = allAppointments.filter((appointment) =>
      statusValues.includes(appointment.status)
    );

    const appointmentsEvents = appointments.map((appointment) => {
      const {customer, autoDiagnostic, slotTime, customerName} = appointment;
      const title: string = customer
        ? `${customer.firstName} ${customer.lastName}`
        : (customerName ?? 'Nom inconnu');
      const prestation = autoDiagnostic ? `(${autoDiagnostic.prestation})` : '';

      return {
        title: `${
          appointment.status === 'pending_repairer' ? '⌛' : ''
        } ${title} ${prestation}`,
        start: slotTime,
        end: getEndAppointment(slotTime, repairer.durationSlot ?? 60),
        id: appointment['@id'],
        color: appointment.status === 'validated' ? 'green' : 'red',
        display: 'block',
      };
    });

    setCalendarEvents(appointmentsEvents);
  };

  const clickAppointment = async (event: EventImpl) => {
    setOpenModal(true);
    const appointmentFetch = await appointmentResource.get(event.id, true);
    setAppointment(appointmentFetch);
  };

  const handleDateChange = (payload: DatesSetArg) => {
    if (payload.startStr === startDate && payload.endStr === endDate) {
      return;
    }

    setStartDate(payload.startStr);
    setEndDate(payload.endStr);
    buildCalendarEvents(payload.startStr, payload.endStr);
  };

  const calendarRef = useRef<FullCalendar>(null);
  const handleDateClick = (arg: DateClickArg, refresh = true) => {
    if (arg) {
      const dateFormat1 = /^\d{4}-\d{2}-\d{2}$/; // Format: YYYY-MM-DD
      const dateFormat2 = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/; // Format: YYYY-MM-DDTHH:mm:ss
      if (dateFormat1.test(arg.dateStr)) {
        setSelectedDate(`${arg.dateStr}T00:00:00`);
        setOpenModalCreateAppointment(true);
      } else if (dateFormat2.test(arg.dateStr)) {
        setSelectedDate(arg.dateStr);
        setOpenModalCreateAppointment(true);
      }
    }
  };

  return (
    <>
      {initialDate && (
        <FullCalendar
          timeZone="Europe/Paris"
          ref={calendarRef}
          height={isMobile ? 650 : undefined}
          plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
          initialView="timeGridDay"
          initialDate={initialDate}
          datesSet={handleDateChange}
          weekends={true}
          locales={[frLocale]}
          locale="fr"
          eventClick={(info) => clickAppointment(info.event)}
          eventContent={(event) => {
            return (
              <Box sx={{whiteSpace: 'initial'}}>
                {event.timeText}
                {event.event.title}
              </Box>
            );
          }}
          headerToolbar={
            isMobile
              ? {
                  center: 'prev,next',
                  left: 'title',
                  right: 'timeGridDay,dayGridWeek,dayGridMonth',
                }
              : {
                  left: 'prev,next',
                  center: 'title',
                  right: 'timeGridDay,dayGridWeek,dayGridMonth',
                }
          }
          events={calendarEvents}
          eventMouseEnter={(info) => {
            info.el.style.cursor = 'pointer';
          }}
          dateClick={handleDateClick}
        />
      )}

      {appointment && (
        <ModalShowAppointment
          appointment={appointment}
          openModal={openModal}
          handleCloseModal={handleCloseModal}
        />
      )}
      {selectedDate && (
        <ModalAppointmentCreate
          repairer={repairer}
          appointmentSelectedDate={selectedDate}
          openModal={openModalCreateAppointment}
          handleCloseModal={handleCloseModalCreateAppointment}
        />
      )}
    </>
  );
};

export default AgendaCalendar;
