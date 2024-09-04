import {createContext, ReactNode, useState} from 'react';
import {MediaObject} from '@interfaces/MediaObject';

interface ProviderProps {
  children: ReactNode;
}

interface HomepagePictureContext {
  photo: MediaObject | null;
  setPhoto: (value: MediaObject) => void;
}

const initialValue = {
  photo: null,
  setPhoto: () => null,
};

export const HomepagePictureContext =
  createContext<HomepagePictureContext>(initialValue);

export const HomepagePictureProvider = ({
  children,
}: ProviderProps): JSX.Element => {
  const [photo, setPhoto] = useState<MediaObject | null>(null);

  return (
    <HomepagePictureContext.Provider
      value={{
        photo,
        setPhoto,
      }}>
      {children}
    </HomepagePictureContext.Provider>
  );
};
