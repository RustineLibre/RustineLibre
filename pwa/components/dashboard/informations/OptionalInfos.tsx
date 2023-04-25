import React, {useContext, useEffect} from "react";
import {Repairer} from "@interfaces/Repairer";
import InputLabel from "@mui/material/InputLabel";
import {RepairerFormContext} from "@contexts/RepairerFormContext";
import dynamic from 'next/dynamic';
const Editor = dynamic(() => import("@components/form/Editor"), {
    ssr: false
});

interface OptionnalInfosProps {
    repairer: Repairer|null;
}

export const OptionalInfos = ({repairer}: OptionnalInfosProps): JSX.Element => {
    const {optionalPage, setOptionalPage, openingHours, setOpeningHours} = useContext(RepairerFormContext);

    useEffect(() => {
        if (repairer) {
            setOptionalPage(repairer.optionalPage ? repairer.optionalPage : '');
            setOpeningHours(repairer.openingHours ? repairer.openingHours : '');
        }
    }, [repairer, setOpeningHours, setOptionalPage]);

    return (
        <>
            <InputLabel>Horaires d&apos;ouverture</InputLabel>
            <Editor content={openingHours} setContent={setOpeningHours}/>
            <InputLabel>Informations complémentaires (page visible avant la prise de rendez vous)</InputLabel>
            <Editor content={optionalPage} setContent={setOptionalPage}/>
        </>
    );
};

export default OptionalInfos;