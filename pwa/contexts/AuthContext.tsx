import {
  useState,
  useEffect,
  createContext,
  useContext,
  PropsWithChildren,
} from 'react';
import Router from 'next/router';
import {userResource} from '../resources/userResource';
import {authenticationResource} from '../resources/authenticationResource';
import { User } from 'interfaces/User';
import {
  getToken,
  removeRefreshToken,
  removeToken,
  setRefreshToken,
  setToken,
} from '../helpers';

type AuthenticationValues = {
  email: string;
  password: string;
};

interface AuthContextType {
  user: User | null;
  login: (data: AuthenticationValues) => Promise<User | null>;
  logout: () => void;
  isLoading?: boolean;
  fetchUser: () => void;
}

export const AuthContext = createContext<AuthContextType | null>(null);

// Hook for child components to get the auth object and re-render when it changes.
export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error(`useAuth must be used within a AuthProvider`);
  }
  return context;
};

// Provider component that wraps your app and makes auth object available to any child component that calls useAuth().
export const AuthProvider = ({children}: PropsWithChildren): JSX.Element => {
  const auth = useProviderAuth();

  return <AuthContext.Provider value={auth}>{children}</AuthContext.Provider>;
};

// Provider hook that creates auth object and handles state
const useProviderAuth = () => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setLoading] = useState(true);

  const fetchUser = async (): Promise<User | null> => {
    const user = await userResource.getCurrent();
    setUser(user);

    return user || null;
  };

  useEffect(() => {
    async function loadUserFromSession() {
      try {
        if (!!getToken()) {
          await fetchUser();
        }
      } catch (e) {
        logout();
      }
      setLoading(false);
    }

    loadUserFromSession();
  }, []);

  const login = async (data: {email: string; password: string}) => {
    try {
      const {token, refresh_token} = await authenticationResource.authenticate(
        data
      );
      setToken(token);
      // setRefreshToken(refresh_token); // @todo implement

      return await fetchUser();
    } catch (e) {
      return null;
    }
  };

  const logout = async () => {
    removeToken();
    removeRefreshToken();
    setUser(null);
  };

  return {
    user,
    login,
    logout,
    isAuthenticated: !!user,
    isLoading,
    fetchUser,
  };
};