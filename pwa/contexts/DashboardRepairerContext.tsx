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
  repairerNotFound: boolean;
}

const initialValue = {
  repairer: null,
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

    const repairerForBoss = user.repairers.find(
      (repairer) => repairer.id == repairer_id
    );

    if (user && isBoss(user) && repairerForBoss) {
      setRepairer(repairerForBoss);
      return;
    }

    if (
      user &&
      isEmployee(user) &&
      user.repairerEmployee &&
      user.repairerEmployee.repairer.id == repairer_id
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
        repairerNotFound,
      }}>
      {children}
    </DashboardRepairerContext.Provider>
  );
};
