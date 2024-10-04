import React, {createContext, ReactNode, useEffect, useState} from 'react';
import {Repairer} from '@interfaces/Repairer';
import {useRouter} from 'next/router';
import {useAccount} from '@contexts/AuthContext';
import {isBoss, isEmployee} from '@helpers/rolesHelpers';

interface ProviderProps {
  children: ReactNode;
}

interface DashboardRepairerContext {
  repairer: Repairer | null;
  setRepairer: (repairer: Repairer) => void;
  repairerNotFound: boolean;
}

const initialValue = {
  repairer: null,
  setRepairer: (repairer: Repairer | null) => {},
  repairerNotFound: false,
};

export const DashboardRepairerContext =
  createContext<DashboardRepairerContext>(initialValue);

export const DashboardRepairerProvider = ({
  children,
}: ProviderProps): JSX.Element => {
  const router = useRouter();
  const {repairer_id} = router.query ?? null;
  const {user} = useAccount({});
  const [repairer, setRepairer] = useState<Repairer | null>(null);
  const [repairerNotFound, setRepairerNotFound] = useState<boolean>(false);

  useEffect(() => {
    if (!user) {
      return;
    }

    setRepairerNotFound(false);
    if (!repairer_id) {
      setRepairer(null);
      return;
    }

    const repairerForBoss = user.repairers.find((repairer) =>
      Array.isArray(repairer_id)
        ? repairer_id.includes(repairer.id.toString())
        : repairer.id.toString() === repairer_id
    );

    if (user && isBoss(user) && repairerForBoss) {
      setRepairer(repairerForBoss);
      return;
    }

    if (
      user &&
      isEmployee(user) &&
      user.repairerEmployee &&
      (Array.isArray(repairer_id)
        ? repairer_id.includes(user.repairerEmployee.repairer.id.toString())
        : user.repairerEmployee.repairer.id.toString() === repairer_id)
    ) {
      setRepairer(user.repairerEmployee.repairer);
      return;
    }

    setRepairerNotFound(true);
  }, [user, repairer_id]);

  return (
    <DashboardRepairerContext.Provider
      value={{
        repairer,
        setRepairer,
        repairerNotFound,
      }}>
      {children}
    </DashboardRepairerContext.Provider>
  );
};
