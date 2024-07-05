import React, {createContext, ReactNode, useState} from 'react';
import {Appointment} from '@interfaces/Appointment';
import {AutoDiagnostic} from '@interfaces/AutoDiagnostic';
import {MediaObject} from '@interfaces/MediaObject';

interface ProviderProps {
    children: ReactNode;
}

interface AutodiagContext {
    tunnelStep: string;
    prestation: string;
    appointment: Appointment | null;
    autoDiagnostic: AutoDiagnostic | null;
    photo: MediaObject | null;
    comment: string | null
    setTunnelStep: (value: string) => void;
    setPrestation: (value: string) => void;
    setAppointment: (value: Appointment) => void;
    setAutoDiagnostic: (value: AutoDiagnostic) => void;
    setPhoto: (value: MediaObject) => void;
    setComment: (value: string) => void;
}

const initialValue = {
    tunnelStep: 'choice',
    prestation: 'Entretien classique',
    appointment: null,
    autoDiagnostic: null,
    photo: null,
    comment: null,
    setTunnelStep: () => null,
    setPrestation: () => null,
    setAppointment: () => null,
    setAutoDiagnostic: () => null,
    setPhoto: () => null,
    setComment: () => null,
};

export const AutodiagContext = createContext<AutodiagContext>(initialValue);

export const AutodiagProvider = ({children}: ProviderProps): JSX.Element => {
    const [tunnelStep, setTunnelStep] = useState<string>('bike_selection');
    const [prestation, setPrestation] = useState<string>('Entretien classique');
    const [appointment, setAppointment] = useState<Appointment | null>(null);
    const [autoDiagnostic, setAutoDiagnostic] = useState<AutoDiagnostic | null>(
        null
    );
    const [photo, setPhoto] = useState<MediaObject | null>(null);
    const [comment, setComment] = useState<string | null>(null);

    return (
        <AutodiagContext.Provider
            value={{
                tunnelStep,
                prestation,
                appointment,
                autoDiagnostic,
                photo,
                comment,
                setTunnelStep,
                setPrestation,
                setAppointment,
                setAutoDiagnostic,
                setPhoto,
                setComment,
            }}>
            {children}
        </AutodiagContext.Provider>
    );
};
