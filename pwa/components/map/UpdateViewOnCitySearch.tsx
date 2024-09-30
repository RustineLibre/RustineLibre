import {useMap} from 'react-leaflet';
import {City} from '@interfaces/City';
import {useEffect} from 'react';

interface UpdateViewOnCitySearchProps {
  city: City | null;
}
const UpdateViewOnCitySearch = ({city}: UpdateViewOnCitySearchProps) => {
  const map = useMap();

  useEffect(() => {
    if (city) {
      map.setView([Number(city.lat), Number(city.lon)]);
    }
  }, [city?.lat, city?.lon]);

  return null;
};

export default UpdateViewOnCitySearch;
